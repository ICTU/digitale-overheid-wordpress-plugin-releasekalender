<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wbvb.nl/
 * @since      1.0.0
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/public
 * @author     Marcel Bootsman <marcel@nostromo.nl>
 */
class rijksreleasekalender_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $rijksreleasekalender    The ID of this plugin.
	 */
	private $rijksreleasekalender;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The page template slug for the main page template
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    page template slug
	 */
	private $releasekalender_template_hoofdpagina;

	/**
	 * The page template slug for the dossier page template
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    page template slug
	 */
	private $releasekalender_template_dossier;

	/**
	 * The separator to recognize the voorziening in the URL 
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    page template slug
	 */
	private $releasekalender_queryvar_voorziening;
	private $releasekalender_queryvar_product;

	/**
	 * A test array for displaying some strings for testing purposes 
	 *
	 * @since    1.0.2
	 * @access   private
	 * @var      string    $version    page template slug
	 */
	private $TEMP_releasekalender_testarray;
	private $TEMP_listarray;


	/**
	 * A string to preserve the original page title 
	 *
	 * @since    1.0.2
	 * @access   private
	 * @var      string    $version    page title
	 */
	private $TEMP_pagename_for_voorziening;
	private $TEMP_pagename_for_product;

	/**
	 * Variable containing the voorziening key, if available
	 *
	 * @since    1.0.2
	 * @access   private
	 * @var      string    $version   key for voorziening
	 */
	private $releasekalender_voorziening;

	/**
	 * Variable containing the product key, if available
	 *
	 * @since    1.0.2
	 * @access   private
	 * @var      string    $version   key for voorziening
	 */
	private $releasekalender_product;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $rijksreleasekalender       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $rijksreleasekalender, $version ) {

		$this->rijksreleasekalender = $rijksreleasekalender;
		$this->version = $version;
		
		$this->releasekalender_template_dossier   		= 'releasekalender-dossier-template.php';
		$this->releasekalender_template_hoofdpagina   = 'releasekalender-main-page-template.php';
		
		$this->releasekalender_queryvar_voorziening   = 'voorziening';
		$this->releasekalender_queryvar_product   		= 'product';

		
		$this->releasekalender_voorziening						= '';

		add_filter( 'init',				array( $this, 'rijksreleasekalender_add_rewrite_rules' ) );
		add_filter( 'query_vars',	array( $this, 'rijksreleasekalender_add_query_vars' ) );

		// add the page templates
		add_filter( 'theme_page_templates', array( $this, 'rijksreleasekalender_add_page_templates' ) );
		
		// activate the page filters
		add_action( 'template_redirect',    array( $this, 'rijksreleasekalender_use_page_template' )  );
		
		$this->TEMP_releasekalender_testarray = array(
			'Gegevens' => array( 
				'parent' => true,
				'bouwstenen' => array(	   
					'burgerservicenummer' => array(
						'display_name' => 'Burgerservicenummer' ),
					'digikoppeling' => array(
						'display_name' => 'Digikoppeling' ),
					'digilevering' => array(
						'display_name' => 'Digilevering' ),
					'digimelding' => array(
						'display_name' => 'Digimelding' ),
					'stelselcatalogus' => array(
						'display_name' => 'Stelselcatalogus' )
				),
			),
			'Basisregistratie' => array(
				'parent' => false,
				'bouwstenen' => array(
				  'basisregistratie-grootschalige-topografie-bgt' => array(
						'display_name' => 'Grootschalige Topografie (<abbr title="Basisregistratie grootschalige Topografie">BGT</abbr>)' ),
					'basisregistratie-handelsregister-hr' => array(
						'display_name' => 'Handelsregister (HR)' ),
					'basisregistratie-inkomen-bri' => array(
						'display_name' => 'Inkomen (<abbr title="Basisregistratie Inkomen">BRI</abbr>)' ),
					'basisregistratie-kadaster-brk' => array(
						'display_name' => 'Kadaster (<abbr title="Basisregistratie Kadaster">BRK</abbr>)' ),
					'basisregistratie-lonen-arbeidsverhoudingen-en-uitkeringen-blau' => array(
						'display_name' => 'Lonen, Arbeidsverhoudingen en Uitkeringen (<abbr title="Basisregistratie Lonen Arbeidsverhoudingen en Uitkeringen">BLAU</abbr>)' ),
					'basisregistratie-ondergrond-bro' => array(
						'display_name' => 'Ondergrond (<abbr title="Basisregistratie Ondergrond">BRO</abbr>)' ),
					'basisregistratie-personen-brp-gba-rni' => array(
						'display_name' => 'Personen (<abbr title="Basisregistratie Personen">BRP</abbr>=<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>+<abbr title="Registratie Niet Ingezetenen">RNI</abbr>)' ),
					'basisregistratie-topografie-brt' => array(
						'display_name' => 'Topografie (<abbr title="Basisregistratie Topografie">BRT</abbr>)' ),
					'basisregistratie-voertuigen-brv' => array(
						'display_name' => 'Voertuigen (<abbr title="Basisregistratie Voertuigen">BRV</abbr>)' ),
					'basisregistratie-waarde-onroerende-zaken-woz' => array(
						'display_name' => 'Waarde onroerende zaken (<abbr title="Waarde Onroerende Zaken">WOZ</abbr>)' ),
					'basisregistraties-adressen-en-gebouwen-bag' => array(
						'display_name' => 'Adressen en Gebouwen (<abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr>)' )
				),
			),
			'Dienstverlening' => array(
				'parent' => true,
				'bouwstenen' => array(
					'antwoord-voor-bedrijven' => array(
						'display_name' => 'Antwoord voor bedrijven' ),
					'berichtenbox-voor-bedrijven' => array(
						'display_name' => 'Berichtenbox voor bedrijven' ),
					'e-factureren' => array(
						'display_name' => 'e-Factureren' ),
					'mijnoverheid' => array(
						'display_name' => 'MijnOverheid' ),
					'ondernemersplein' => array(
						'display_name' => 'Ondernemersplein' ),
					'ondernemingsdossier' => array(
						'display_name' => 'Ondernemingsdossier' ),
					'overheid-nl' => array(
						'display_name' => 'Overheid.nl' ),
					'samenwerkende-catalogi' => array(
						'display_name' => 'Samenwerkende catalogi' ),
					'standard-business-reporting-sbr' => array(
						'display_name' => 'Standard Business Reporting (<abbr title="Standard Business Reporting">SBR</abbr>)' )
				),
			),
			'Identificatie &amp; authenticatie' => array(
				'parent' => true,
				'bouwstenen' => array(  
					'digid' => array(
						'display_name' => '<abbr title="Digitale Identiteit">DigiD</abbr>' ),
					'eherkenning' => array(
						'display_name' => '<abbr title="Elektronische Herkenning">eHerkenning</abbr>' )
				),
			),
			'Interconnectiviteit' => array(
				'parent' => true,
				'bouwstenen' => array(
				 	'diginetwerk' => array(
				 		'display_name' => 'Diginetwerk' ),
					'digipoort' => array(
						'display_name' => 'Digipoort' ),
					'pkioverheid' => array(
						'display_name' => '<abbr title="Public Key Infrastructure voor de overheid">PKIoverheid</abbr>' )
				),
			),
		);  


    $this->TEMP_listarray = [];



		foreach ( $this->TEMP_releasekalender_testarray as $key => $value ) {

			$newarray = $value['bouwstenen'];							

			if ( is_array( $newarray ) ) {

				foreach ( $newarray as $key1 => $value1 ) {
  				$this->TEMP_listarray[$key1] = $value1['display_name'];
				}
			}
		}
	

    if( function_exists('acf_add_local_field_group') ):
    
    acf_add_local_field_group(array (
    	'key' => 'group_58500d27b83da',
    	'title' => 'Kies bijbehorende voorzieningen (releasekalender)',
    	'fields' => array (
    		array (
    			'layout' => 'vertical',
    			'choices' => $this->TEMP_listarray,
    			'default_value' => '',
    			'other_choice' => 0,
    			'save_other_choice' => 0,
    			'allow_null' => 1,
    			'return_format' => 'value',
    			'key' => 'field_58500d3f113fe',
    			'label' => 'Voorzieningen',
    			'name' => 'releasekalender_voorziening',
    			'type' => 'radio',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    		),
    	),
    	'location' => array (
    		array (
    			array (
    				'param' => 'page_template',
    				'operator' => '==',
    				'value' => 'releasekalender-dossier-template.php',
    			),
    		),
    	),
    	'menu_order' => 0,
    	'position' => 'acf_after_title',
    	'style' => 'default',
    	'label_placement' => 'top',
    	'instruction_placement' => 'label',
    	'hide_on_screen' => '',
    	'active' => 1,
    	'description' => '',
    ));
    
    endif;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in rijksreleasekalender_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The rijksreleasekalender_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/rijksreleasekalender-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in rijksreleasekalender_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The rijksreleasekalender_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


	}

    /**
     * Hides the custom post template for pages on WordPress 4.6 and older
     *
     * @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
     * @return array Expanded array of page templates.
     */
    function rijksreleasekalender_add_page_templates( $post_templates ) {

      $post_templates[$this->releasekalender_template_dossier]  		= __( 'Releasekalender - Dossierpagina ', 'rijksreleasekalender' );    
      $post_templates[$this->releasekalender_template_hoofdpagina]	= __( 'Releasekalender - Hoofdpagina', 'rijksreleasekalender' );    
      return $post_templates;
      
    }
  
  	/**
  	 * Modify page content if using a specific page template.
  	 */
  	public function rijksreleasekalender_use_page_template() {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

			wp_enqueue_script( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'js/min/releasekalender-min.js?v1', array( 'jquery' ), $this->version, false );

      $this->rijksreleasekalender_get_original_page_title();
  		
  		if ( ( $this->releasekalender_template_dossier == $page_template ) || ( $this->releasekalender_template_hoofdpagina == $page_template ) ) {

    		// filter the main template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_hoofdpagina_content_filter' ) );
  			
  			// change the page title
        add_filter( 'genesis_post_title_output', array( $this, 'rijksreleasekalender_template_hoofdpagina_title_filter' ), 15, 2 );

				// check the breadcrumb
				add_filter( 'genesis_single_crumb', array( $this, 'rijksreleasekalender_breadcrumb_modify' ), 10, 2 );
				add_filter( 'genesis_page_crumb', array( $this, 'rijksreleasekalender_breadcrumb_modify' ), 10, 2 );
				add_filter( 'genesis_archive_crumb', array( $this, 'rijksreleasekalender_breadcrumb_modify' ), 10, 2 ); 				

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-main-page-template.css', array(), $this->version, 'all' );

  		}
  		
  	}


//========================================================================  
public function rijksreleasekalender_get_original_page_title( ) {

	global $post;

	$nieuwetitle = get_the_title( get_the_id() );
  

	if ( get_query_var( $this->releasekalender_queryvar_voorziening ) ||  get_query_var( $this->releasekalender_queryvar_product ) ) {

    $this->TEMP_pagename_for_voorziening  = get_query_var( $this->releasekalender_queryvar_voorziening );
    $this->TEMP_pagename_for_product      = get_query_var( $this->releasekalender_queryvar_product );


		$thekeyvoorziening  = get_query_var( $this->releasekalender_queryvar_voorziening );
		$thekeyproduct      = get_query_var( $this->releasekalender_queryvar_product );

		foreach ( $this->TEMP_releasekalender_testarray as $key => $value ) {

			$newarray = $value['bouwstenen'];							

			if ( is_array( $newarray ) ) {
				if ( isset( $newarray[$thekeyvoorziening] ) ) {
					$this->TEMP_pagename_for_voorziening = strip_tags($newarray[$thekeyvoorziening]['display_name']);
				}
				if ( isset( $newarray[$thekeyproduct] ) ) {
					$this->TEMP_pagename_for_product = strip_tags($newarray[$thekeyvoorziening]['display_name']);
				}
			}
		}
	}

}

//========================================================================  
public function rijksreleasekalender_breadcrumb_modify( $crumb, $args ) {

	global $post;

	$page_template  = get_post_meta( get_the_ID(), '_wp_page_template', true );
  $thelink        = get_permalink( get_the_id() ) . $this->releasekalender_queryvar_voorziening . '/' . get_query_var( $this->releasekalender_queryvar_voorziening ) . '/';
	$nieuwetitle    = get_the_title( get_the_id() );

	if ( ( $this->releasekalender_template_dossier == $page_template ) || ( $this->releasekalender_template_hoofdpagina == $page_template ) ) {

	  $span_before_start  = '<span class="breadcrumb-link-wrap" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';  
	  $span_between_start = '<span itemprop="name">';  
	  $span_before_end    = '</span>';  

    if ( $this->releasekalender_template_dossier == $page_template ) {

  		if ( get_query_var( $this->releasekalender_queryvar_product ) ) {
  			$replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
  	    $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
  			$crumb .= $args['sep'] . $this->TEMP_pagename_for_product;
      }

    }	
    else {
  	
  		if ( get_query_var( $this->releasekalender_queryvar_product ) && get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
  
  	
  			$replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
  	    $crumb = str_replace( $nieuwetitle, $replacer, $crumb);

  			$crumb .= $args['sep'] . '<a href="' . $thelink . '">' . $this->TEMP_pagename_for_voorziening .'</a>';
  			$crumb .= $args['sep'] . $this->TEMP_pagename_for_product;
  			
  		}
  		elseif ( get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
  
  			$replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
  	    $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
  
  			$crumb .= $args['sep'] . $this->TEMP_pagename_for_voorziening;
  			
  		}
    }
	}
	
	return $crumb;

}
	  	
//========================================================================  
public function rijksreleasekalender_template_hoofdpagina_title_filter( $thetitle, $id ) {

	global $post, $query;

  $nieuwetitle		= '';

	if ( ! is_admin() ) {
		if ( get_query_var( $this->releasekalender_queryvar_product ) ) {
			$nieuwetitle		= $this->TEMP_pagename_for_product;
		}
		elseif ( get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
			$nieuwetitle		= $this->TEMP_pagename_for_voorziening;
		}
	}

	if ( $nieuwetitle ) {
  	$thetitle =  '<h1 class="entry-title" itemprop="headline">' . $nieuwetitle . '</h1>';
	}

	return $thetitle;
	
}
	  	
//========================================================================  
  
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_template_hoofdpagina_content_filter( $content ) {


  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

      // TODO: toon de juist informatie voor deze voorziening

		  $thispage 		= '';
	    $thelink    	= get_permalink( get_the_ID() );
	    
      
      if( get_field('releasekalender_voorziening') ) {
  			$this->releasekalender_voorziening = get_field('releasekalender_voorziening');
      }
      else {
  			$this->releasekalender_voorziening = get_query_var( $this->releasekalender_queryvar_voorziening );
      }

			$content 			= '';
			$strnu 				= date(get_option('date_format'));
	    $folder 			= dirname(__FILE__) . '/partials/do-downloads/';
			$currentURL		= get_permalink( get_the_id( ) ) . $this->releasekalender_queryvar_voorziening . '/';

			if ( $this->releasekalender_voorziening ) {

        //* Force full-width-content layout
        add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
        

				if ( get_query_var( $this->releasekalender_queryvar_product ) ) {
				  $filelocation = $folder . $this->releasekalender_voorziening . '-' . get_query_var( $this->releasekalender_queryvar_product ) . '.php';
				}
				else {
				  $filelocation = $folder . $this->releasekalender_voorziening . '.php';
				}

				if ( file_exists($filelocation) ) {
			    $filecontents = file_get_contents( $filelocation, FILE_USE_INCLUDE_PATH);
			    
					$filecontents	= str_replace( '<div class="nu"></div>', '<div class="nu"><p>' . $strnu . '</p></div>', $filecontents);
					$filecontents	= str_replace( '__RELEASEKALENDERBOUWSTEEN__', $currentURL, $filecontents);
					$filecontents	= str_replace( '__PRODUCTSEPARATOR__', '/' . $this->releasekalender_queryvar_product . '/', $filecontents);
					
					$content			= '<div id="releasekalenderoutput">' . $filecontents . '</div>';
				}
				else {
				}
					
			}
			else {

				$content2 			= $this->releasekalender_voorziening;

				foreach ( $this->TEMP_releasekalender_testarray as $key => $value ) {

					if ( $value['parent'] ) {
						$content2 .= '<h2>' . $key . '</h2>';
					}
					else {
						$content2 .= '<h3>' . $key . '</h3>';
					}

					$newarray = $value['bouwstenen'];							


					if ( is_array( $newarray ) ) {
						$content2 .= '<ul>';

						foreach ( $newarray as $key1 => $value1 ) {

							$content2 .= '<li><a href="' . $thelink . $this->releasekalender_queryvar_voorziening . '/' . $key1 . '/">' . $value1['display_name'] . '</a></li>';
						}
						
						$content2 .= '</ul>';
					}
				}
				$content = $content2;
				
			}
			
  		return $content;
  	}

  
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_template_dossier_filter( $content ) {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

      // TODO: toon de juist informatie voor deze voorziening

  		$content = '<h2>' . __( 'Releasekalender info voor een dossier pagina', 'rijksreleasekalender' ) . '</h2><p>' . __( 'Meer volgt.', 'rijksreleasekalender' ) . '<br>' . $page_template . '</p>' . $content;
  		return $content;
  	}

		//========================================================================================================
		
		function rijksreleasekalender_add_rewrite_rules() {

		  // rewrite rules for posts in dossier context
		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)(/' . $this->releasekalender_queryvar_product . '/)(.+?)/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]&' . $this->releasekalender_queryvar_product . '=$matches[5]', 'top');
		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]', 'top');

		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)(/' . $this->releasekalender_queryvar_product . '/)(.+?)?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]&' . $this->releasekalender_queryvar_product . '=$matches[5]', 'top');
		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]', 'top');
		
		}

		//========================================================================================================
		
		function rijksreleasekalender_add_query_vars($vars) {
			$vars[] = $this->releasekalender_queryvar_voorziening;
			$vars[] = $this->releasekalender_queryvar_product;

			
			
			return $vars;
		}
		
		//========================================================================================================

}
