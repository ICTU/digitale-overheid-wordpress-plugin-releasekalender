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
	private $releasekalender_queryvar_kalender;
	private $releasekalender_queryvar_plainhtml;

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
	private $TEMP_pagename_for_kalender;

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
		$this->version              = $version;
		
		$this->releasekalender_template_dossier   		= 'releasekalender-dossier-template.php';
		$this->releasekalender_template_hoofdpagina   = 'releasekalender-main-page-template.php';
		
		$this->releasekalender_queryvar_voorziening   = 'voorziening';
		$this->releasekalender_queryvar_product   		= 'product';
		$this->releasekalender_queryvar_kalender   		= 'kalender';
		$this->releasekalender_queryvar_plainhtml   	= 'plainhtml';
		

		$this->releasekalender_voorziening						= '';

		add_filter( 'init',				array( $this, 'rijksreleasekalender_add_rewrite_rules' ) );
		add_filter( 'query_vars',	array( $this, 'rijksreleasekalender_add_query_vars' ) );

		// add the page templates
		add_filter( 'theme_page_templates', array( $this, 'rijksreleasekalender_add_page_templates' ) );
		
		// activate the page filters
		add_action( 'template_redirect',    array( $this, 'rijksreleasekalender_use_page_template' )  );

    // action for writing extra info in the alt-sidebar
		add_action( 'genesis_before_sidebar_widget_area',    array( $this, 'rijksreleasekalender_sidebar_context_widgets' )  );
    
    // action for writing extra info in the alt-sidebar
		add_action( 'genesis_entry_content',    array( $this, 'rijksreleasekalender_add_plain_html_kalender' ), 11  );
    

		
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

      // action for writing extra info in the alt-sidebar
      add_action( 'rhswp_primary_sidebar_first_action', 'rhswp_sidebar_context_widgets' );

			wp_enqueue_script( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'js/min/releasekalender-min.js?v1', array( 'jquery' ), $this->version, false );

      $this->rijksreleasekalender_get_original_page_title();
  		
  		if ( ( $this->releasekalender_template_dossier == $page_template ) || ( $this->releasekalender_template_hoofdpagina == $page_template ) ) {


        if( get_field('releasekalender_voorziening') ) {
    			$this->releasekalender_voorziening = get_field('releasekalender_voorziening');
        }
        else {
    			$this->releasekalender_voorziening = get_query_var( $this->releasekalender_queryvar_voorziening );
        }


        if ( ( $this->releasekalender_voorziening )  ||  get_query_var( $this->releasekalender_queryvar_kalender ) ) {
        
            //* Force full-width-content layout
            add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
        
        }        


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

  $this->TEMP_pagename_for_kalender     = 'Kalender';

  if ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {
  }
  elseif ( get_query_var( $this->releasekalender_queryvar_voorziening ) ||  get_query_var( $this->releasekalender_queryvar_product ) ) {

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
  		elseif ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {
  
  			$replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
  	    $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
  
  			$crumb .= $args['sep'] . $this->TEMP_pagename_for_kalender;
  			
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
		if ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {
			$nieuwetitle		= $this->TEMP_pagename_for_kalender;
		}
		elseif ( get_query_var( $this->releasekalender_queryvar_product ) ) {
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

    		if ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {

          $url = get_permalink( get_the_ID() );

  				$content = '<div  id="releasekalenderoutput"><div class="rk-kalender">
    <div id="skipjaar" class="hide"> <a href="#j2007">2007</a> | <a href="#j2008">2008</a> | <a href="#j2009">2009</a> | <a href="#j2010">2010</a> | <a href="#j2011">2011</a> | <a href="#j2012">2012</a> | <a href="#j2013">2013</a> | <a href="#j2014">2014</a> | <a href="#j2015">2015</a> | <a href="#j2016">2016</a> | <a href="#j2017">2017</a> | <a href="#j2018">2018</a></div>
    <div class="kalender">
      <div class="months">
        <div class="row">
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2007">2007</h2>
          <div class="unit">
            <h3>Maart 2007</h3>
            <h4>5 maart</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Regelgeving (Kadasterwet)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-regelgeving-kadasterwet#wet-basisregistraties-kadaster-en-topografie">Wet Basisregistraties Kadaster en Topografie</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Mei 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Juni 2007</h3>
            <h4>9 juni</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-regelgeving#wet-gemeentelijke-basisadministratie-persoonsgegevens">Wet gemeentelijke basisadministratie persoonsgegevens</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Augustus 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2007</h3>
            <h4>8 september</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-regelgeving#besluit-gemeentelijke-basisadministratie-persoonsgegevens">Besluit gemeentelijke basisadministratie persoonsgegevens</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>December 2007</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2008">2008</h2>
          <div class="unit">
            <h3>Januari 2008</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-bronhouders-koppelvlak-0-9"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bronhouders koppelvlak 0.9</a></li>
            </ul>
            <p>Gemeentelijke <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr>-applicatie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/gemeentelijke-bag-applicatie#gemeentelijke-bag-applicatie">Gemeentelijke <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr>-applicatie ?</a></li>
            </ul>
            <p>Mutatieabonnement</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/mutatieabonnement#nhr-mutatieabonnement"><abbr title="Nieuw Handelsregister">NHR</abbr> Mutatieabonnement</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2008</h3>
            <p><em>Geen releases</em></p>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2008</h3>
            <h4>5 maart</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-regelgeving#wet-basisregistraties-kadaster-en-topografie">Wet basisregistraties kadaster en topografie</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2008</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Mei 2008</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Juni 2008</h3>
            <p><em>Geen releases</em></p>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2008</h3>
            <h4>1 juli</h4>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Terugmeldvoorziening</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-terugmeldvoorziening#kentekenregister">Kentekenregister</a></li>
            </ul>
            <h4>21 juli</h4>
            <p><abbr title="Burgerservicenummer">BSN</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/burgerservicenummer/' . $this->releasekalender_queryvar_product . '/bsn-regelgeving#wet-algemene-bepalingen-burgerservicenummer">Wet algemene bepalingen Burgerservicenummer</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2008</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2008</h3>
            <h4>19 september</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-bronhouders-koppelvlak-1-0"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bronhouders koppelvlak 1.0</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2008</h3>
            <h4>7 oktober</h4>
            <p>Digikoppeling standaard Identificatie &amp; Authenticatie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-identificatie-authenticatie#digikoppeling-1-0-2-0-i-a-1-0">Digikoppeling 1.0 - 2.0: I&amp;A 1.0</a></li>
            </ul>
            <h4>30 oktober</h4>
            <p><abbr title="Burgerservicenummer">BSN</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/burgerservicenummer/' . $this->releasekalender_queryvar_product . '/bsn-regelgeving#besluit-burgerservicenummer">Besluit Burgerservicenummer</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2008</h3>
            <h4>1 november</h4>
            <p>Digikoppeling standaard <abbr title="ebXML Messaging Service">ebMS</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-ebms#digikoppeling-1-0-kvs-ebms-1-1">Digikoppeling 1.0: KVS <abbr title="ebXML Messaging Service">ebMS</abbr> 1.1</a></li>
            </ul>
            <p>Digikoppeling standaard WUS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-wus#digikoppeling-1-0-kvs-wus-1-1">Digikoppeling 1.0: KVS WUS 1.1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2008</h3>
            <h4>12 december</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-bronhouders-koppelvlak-1-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> bronhouders koppelvlak 1.1</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2009">2009</h2>
          <div class="unit">
            <h3>Januari 2009</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Catalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-catalogus#bag-catalogus-2009"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Catalogus 2009</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Processenhandboek</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-processenhandboek#bag-processenhandboek-2009"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Processenhandboek 2009</a></li>
            </ul>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Gegevensverzameling AKR</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-akr#akr-bestand-automatisering-kadastrale-registratie">AKR bestand (Automatisering Kadastrale Registratie)</a></li>
            </ul>
            <p>Digikoppeling standaard Architectuur</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-architectuur#digikoppeling-1-0-2-0-architectuur-1-0">Digikoppeling 1.0 - 2.0: Architectuur 1.0</a></li>
            </ul>
            <p>Mijn Kadaster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/mijn-kadaster#kol-kadaster-on-line">KOL (Kadaster-on-line)</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/mijn-kadaster#kadata-internet">Kadata internet</a></li>
            </ul>
            <p><abbr title="TerugMeldVoorziening">TMV</abbr> <abbr title="Basisregistratie Kadaster">BRK</abbr> (terugmeldvoorziening)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/tmv-brk-terugmeldvoorziening#tmv-brk-1-0"><abbr title="TerugMeldVoorziening">TMV</abbr> <abbr title="Basisregistratie Kadaster">BRK</abbr> 1.0</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2009</h3>
            <p><em>Geen releases</em></p>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2009</h3>
            <h4>9 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-regelgeving#besluit-basisregistraties-adressen-en-gebouwen">Besluit basisregistraties adressen en gebouwen</a></li>
            </ul>
            <h4>10 maart</h4>
            <p>Digikoppeling standaard <abbr title="ebXML Messaging Service">ebMS</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-ebms#digikoppeling-2-0-3-0-kvs-ebms-2-0">Digikoppeling 2.0 - 3.0: KVS <abbr title="ebXML Messaging Service">ebMS</abbr> 2.0</a></li>
            </ul>
            <h4>19 maart</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Catalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-catalogus#brk-catalogus-imkad-1-0"><abbr title="Basisregistratie Kadaster">BRK</abbr> Catalogus IMKAD 1.0</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2009</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Mei 2009</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Juni 2009</h3>
            <h4>1 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-koppelvlak-bronhouders-1-2-0"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> koppelvlak bronhouders 1.2.0</a></li>
            </ul>
            <h4>19 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-bronhouders-koppelvlak-1-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> bronhouders koppelvlak 1.2</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2009</h3>
            <h4>1 juli</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-bevragen#bag-verstrekkingen"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Verstrekkingen</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-verstrekkingen"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Verstrekkingen</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Web</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-web#bag-verstrekkingen"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Verstrekkingen</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2009</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2009</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Oktober 2009</h3>
            <h4>1 oktober</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Objectenhandboek</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-objectenhandboek#bag-objectenhandboek-2009"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Objectenhandboek 2009</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2009</h3>
            <h4>1 november</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Applicatie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-applicatie#gba-applicatie-lo-3-7"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Applicatie <abbr title="Logisch Ontwerp">LO</abbr> 3.7</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-logisch-ontwerp#gba-lo-3-7"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 3.7</a></li>
            </ul>
            <h4>20 november</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="TerugMeldVoorziening">TMV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-tmv#bag-terugmeldvoorziening"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Terugmeldvoorziening</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2009</h3>
            <h4>31 december</h4>
            <p>Digikoppeling Gateway</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-gateway#gateway-2-012">Gateway 2.012</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2010">2010</h2>
          <div class="unit">
            <h3>Januari 2010</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> gegevensverzameling Geo</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-geo#brk-gegevensbestand-geo"><abbr title="Basisregistratie Kadaster">BRK</abbr> gegevensbestand Geo</a></li>
            </ul>
            <p>KIK-inzage (Ketenintegratie Inschrijving Kadaster)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kik-inzage-ketenintegratie-inschrijving-kadaster#kik-inzage">KIK-inzage</a></li>
            </ul>
            <p>Massale Output</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/massale-output#massale-output-akr">Massale Output AKR</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2010</h3>
            <p><em>Geen releases</em></p>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2010</h3>
            <h4>10 maart</h4>
            <p>Digikoppeling standaard WUS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-wus#digikoppeling-2-0-kvs-wus-2-0">Digikoppeling 2.0: KVS WUS 2.0</a></li>
            </ul>
            <h4>31 maart</h4>
            <p>Digimelding <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-lv#digimelding-lv-1-2">Digimelding <abbr title="Landelijke Voorziening">LV</abbr> 1.2</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2010</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Mei 2010</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Juni 2010</h3>
            <h4>1 juni</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-5-1-selectieverstrekkingen"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 5.1 - Selectieverstrekkingen</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2010</h3>
            <h4>1 juli</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Gegevensverzameling AKR</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-akr#akr-bestand-incl-gba-gegevens">AKR bestand (incl. <abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> gegevens)</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2010</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2010</h3>
            <h4>1 september</h4>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-1-1">Stelselcatalogus 1.1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2010</h3>
            <h4>10 oktober</h4>
            <p>PIVA Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-regelgeving#wet-basisadministraties-persoonsgegevens-bes">Wet Basisadministraties Persoonsgegevens BES</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-regelgeving#besluit-basisadministraties-persoonsgegevens-bes">Besluit Basisadministraties Persoonsgegevens BES</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2010</h3>
            <h4>1 november</h4>
            <p>Digikoppeling Compliancevoorziening</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-compliancevoorziening#compliancevoorziening-2-1">Compliancevoorziening 2.1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2010</h3>
            <h4>31 december</h4>
            <p><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-inkomen-bri/' . $this->releasekalender_queryvar_product . '/bri-lv#bri-lv-1-1"><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr> 1.1</a></li>
            </ul>
            <p>Digikoppeling Serviceregister</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-serviceregister#serviceregister-2-1">Serviceregister 2.1</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2011">2011</h2>
          <div class="unit">
            <h3>Januari 2011</h3>
            <h4>1 januari</h4>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-gegevensverzameling#gegevensverzameling-br-woz-start-br-woz">Gegevensverzameling BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> - Start BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
            </ul>
            <p>Gemeentelijke <abbr title="Waarde Onroerende Zaken">WOZ</abbr>-applicaties</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/gemeentelijke-woz-applicaties#gemeentelijke-woz-applicaties-jan-2011">Gemeentelijke <abbr title="Waarde Onroerende Zaken">WOZ</abbr>-applicaties - jan 2011</a></li>
            </ul>
            <p>IMKAD</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/imkad#imkad-1-0">IMKAD 1.0</a></li>
            </ul>
            <p><abbr title="Management Overleg">MO</abbr>-DKK</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/mo-dkk#mo-dkk"><abbr title="Management Overleg">MO</abbr>-DKK</a></li>
            </ul>
            <p><abbr title="Waarde Onroerende Zaken">WOZ</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/woz-regelgeving#wet-waardering-onroerende-zaken">Wet waardering onroerende zaken</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2011</h3>
            <h4>1 februari</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Gegevensmodel</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-top10nl-gegevensmodel#brt-top10nl-objectencatalogus-2-0"><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Objectencatalogus 2.0</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2011</h3>
            <h4>16 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-bronhouders-koppelvlak-1-2-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> bronhouders koppelvlak 1.2.1</a></li>
            </ul>
            <h4>31 maart</h4>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Catalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-catalogus#catalogus-basisregistratie-woz-1-5">Catalogus Basisregistratie <abbr title="Waarde Onroerende Zaken">WOZ</abbr> 1.5</a></li>
            </ul>
            <p>Diginetwerk</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/diginetwerk/' . $this->releasekalender_queryvar_product . '/diginetwerk#diginetwerk-1-0">Diginetwerk 1.0</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2011</h3>
            <h4>1 april</h4>
            <p><abbr title="Nieuw Handelsregister">NHR</abbr> Gegevenscatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/nhr-gegevenscatalogus#br-nhr-gegevenscatalogus-2-0-april-2011">BR <abbr title="Nieuw Handelsregister">NHR</abbr> Gegevenscatalogus 2.0 (april 2011)</a></li>
            </ul>
            <h4>29 april</h4>
            <p><abbr title="Basisregistratie Ondergrond">BRO</abbr> algemeen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-algemeen#mkba">MKBA</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2011</h3>
            <h4>4 mei</h4>
            <p><abbr title="Nieuw Handelsregister">NHR</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/nhr-regelgeving#nhr-handelsregisterbesluit-2008"><abbr title="Nieuw Handelsregister">NHR</abbr> Handelsregisterbesluit 2008</a></li>
            </ul>
            <h4>17 mei</h4>
            <p><abbr title="Landelijke Voorziening">LV</abbr> aankiesbaarheid 14+netnummer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/14-netnummer/' . $this->releasekalender_queryvar_product . '/lv-aankiesbaarheid-14-netnummer#lv-14-6-0"><abbr title="Landelijke Voorziening">LV</abbr> 14+ 6.0</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2011</h3>
            <h4>1 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-viewer#bag-viewer"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> wet- en regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-wet-en-regelgeving#wegenverkeerswet-1994">Wegenverkeerswet 1994</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-wet-en-regelgeving#kentekenreglement">Kentekenreglement</a></li>
            </ul>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-1-2">Stelselcatalogus 1.2</a></li>
            </ul>
            <h4>17 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-2011-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2011/1</a></li>
            </ul>
            <h4>23 juni</h4>
            <p>Webrichtlijnen norm</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/webrichtlijnen/' . $this->releasekalender_queryvar_product . '/webrichtlijnen-norm#webrichtlijnen-versie-2-norm">Webrichtlijnen versie 2 norm</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2011</h3>
            <h4>1 juli</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-gegevensverzameling#bag-gegevensverzameling-2011"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Gegevensverzameling 2011</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-regelgeving#wet-basisregistraties-adressen-en-gebouwen-2011">Wet basisregistraties adressen en gebouwen 2011</a></li>
            </ul>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Gegevensverzameling AKR</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-akr#akr-bestand-inclusief-koppeltabel-bag">AKR bestand (inclusief koppeltabel <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr>)</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Abonnementen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-abonnementen#kentekenregister">Kentekenregister</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-gegevensverzameling#kentekenregister">Kentekenregister</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-gegevensverzameling#gba-gegevensverzameling-2011"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Gegevensverzameling 2011</a></li>
            </ul>
            <p><abbr title="Nieuw Handelsregister">NHR</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/nhr-regelgeving#handelsregisterwet-2007">Handelsregisterwet 2007</a></li>
            </ul>
            <h4>18 juli</h4>
            <p>Uittreksel (vernieuwd)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/uittreksel-vernieuwd#uittreksel-vernieuwd">Uittreksel (vernieuwd)</a></li>
            </ul>
            <h4>22 juli</h4>
            <p><abbr title="Basisregistratie Inkomen">BRI</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-inkomen-bri/' . $this->releasekalender_queryvar_product . '/bri-regelgeving#bri-algemene-wet-inzake-rijksbelastingen"><abbr title="Basisregistratie Inkomen">BRI</abbr> Algemene wet inzake rijksbelastingen</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2011</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2011</h3>
            <h4>19 september</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-lv"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release <abbr title="Landelijke Voorziening">LV</abbr></a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2011</h3>
            <h4>27 oktober</h4>
            <p><abbr title="Registratie Niet Ingezetenen">RNI</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/rni-logisch-ontwerp#rni-lo-2-7"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 2.7</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2011</h3>
            <h4>1 november</h4>
            <p>Mijn Ondernemingsdossier</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemingsdossier/' . $this->releasekalender_queryvar_product . '/mijn-ondernemingsdossier#ondernemingsdossier-eerste-release">Ondernemingsdossier eerste release</a></li>
            </ul>
            <h4>21 november</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Compact</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-compact#bag-release-2011-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2011/2</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2011</h3>
            <h4>31 december</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Keten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-keten#bgt-lv-prototype"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr>-<abbr title="Landelijke Voorziening">LV</abbr> prototype</a></li>
            </ul>
            <p>Digimelding <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-lv#digimelding-lv-1-3">Digimelding <abbr title="Landelijke Voorziening">LV</abbr> 1.3</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2012">2012</h2>
          <div class="unit">
            <h3>Januari 2012</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-regelgeving#bgt-ontwerp-wet-bgt"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Ontwerp wet <abbr title="Basisregistratie grootschalige Topografie">BGT</abbr></a></li>
            </ul>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-gegevensverzameling#gegevensverzameling-br-woz-relatie-met-bag-eerste-fase">Gegevensverzameling BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> - Relatie met <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> eerste fase</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Interactieve/online gegevensdiensten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-interactieve-online-gegevensdiensten#kentekenregister">Kentekenregister</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Selecties</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-selecties#kentekenregister">Kentekenregister</a></li>
            </ul>
            <p>Informatie Publicatie Model Samenwerkende Catalogi</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/samenwerkende-catalogi/' . $this->releasekalender_queryvar_product . '/informatie-publicatie-model-samenwerkende-catalogi#ipm-samenwerkende-catalogi-4-0">IPM Samenwerkende Catalogi 4.0</a></li>
            </ul>
            <h4>23 januari</h4>
            <p>Platform e-overheid voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/platform-e-overheid-voor-bedrijven#ondernemersplatform-q1">Ondernemersplatform Q1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2012</h3>
            <h4>4 februari</h4>
            <p>HR gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/hr-gegevensverzameling#nhr-gegevensverzameling-incl-bag"><abbr title="Nieuw Handelsregister">NHR</abbr> Gegevensverzameling incl. <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr></a></li>
            </ul>
            <p>Opvragen <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr>-ID op basis van vestigingsnummer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/opvragen-bag-id-op-basis-van-vestigingsnummer#opvragen-bag-id-op-basis-van-vestigingsnummer">Opvragen <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr>-ID op basis van vestigingsnummer</a></li>
            </ul>
            <h4>29 februari</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r2-2012">TOP10NL R2-2012</a></li>
            </ul>
            <p>TOP250raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250raster#top250raster-r2-2012">TOP250raster R2-2012</a></li>
            </ul>
            <p>TOP250vector</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250vector#top250vector-r2-2012">TOP250vector R2-2012</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r2-2012">TOP25raster R2-2012</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r2-2012">TOP50raster R2-2012</a></li>
            </ul>
            <p>TOP50vector</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50vector#top50vector-r2-2012">TOP50vector R2-2012</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2012</h3>
            <h4>15 maart</h4>
            <p>MijnOverheid Berichtenbox</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-berichtenbox#mijnoverheid-berichtenbox-maart-2012">MijnOverheid Berichtenbox maart 2012</a></li>
            </ul>
            <p>MijnOverheid Lopende zaken</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-lopende-zaken#mijnoverheid-lopende-zaken-maart-2012">MijnOverheid Lopende Zaken maart 2012</a></li>
            </ul>
            <p>MijnOverheid Persoonlijke gegevens</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-persoonlijke-gegevens#mijnoverheid-persoonlijke-gegevens-maart-2012">MijnOverheid Persoonlijke Gegevens maart 2012</a></li>
            </ul>
            <p>MijnOverheid portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-portaal#mijnoverheid-portaal-maart-2012">MijnOverheid Portaal maart 2012</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2012</h3>
            <h4>1 april</h4>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Catalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-catalogus#catalogus-basisregistratie-woz-versie-1-6">Catalogus Basisregistratie <abbr title="Waarde Onroerende Zaken">WOZ</abbr> versie 1.6</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-6-1-full-service-adhoc"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 6.1 - Full Service Adhoc</a></li>
            </ul>
            <p>Geocodeerservice</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/geocodeerservice#pdok-geocodeerservice-bag"><abbr title="Publieke Dienstverlening op de Kaart">PDOK</abbr> geocodeerservice <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr></a></li>
            </ul>
            <h4>24 april</h4>
            <p>Aansluiten Overheid - HR Dataservice (applicatie-applicatie)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/aansluiten-overheid-hr-dataservice-applicatie-applicatie#aao-release-0-1">AAO Release 0.1</a></li>
            </ul>
            <h4>30 april</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r4-2012">TOP10NL R4-2012</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r4-2012">TOP25raster R4-2012</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r4-2012">TOP50raster R4-2012</a></li>
            </ul>
            <p>TOP50vector</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50vector#top50vector-r4-2012">TOP50vector R4-2012</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2012</h3>
            <h4>1 mei</h4>
            <p>Digikoppeling Compliancevoorziening</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-compliancevoorziening#compliancevoorziening-2-2">Compliancevoorziening 2.2</a></li>
            </ul>
            <p>Digikoppeling Serviceregister</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-serviceregister#serviceregister-2-2">Serviceregister 2.2</a></li>
            </ul>
            <h4>15 mei</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-6-3-full-service-spontaan"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 6.3 - Full Service Spontaan</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-6-2-full-service-conditioneel"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 6.2 - Full Service Conditioneel</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2012</h3>
            <h4>14 juni</h4>
            <p><abbr title="Landelijke Voorziening">LV</abbr> aankiesbaarheid 14+netnummer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/14-netnummer/' . $this->releasekalender_queryvar_product . '/lv-aankiesbaarheid-14-netnummer#lv-14-7-0"><abbr title="Landelijke Voorziening">LV</abbr> 14+ 7.0</a></li>
            </ul>
            <h4>22 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Compact</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-compact#bag-release-2012-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/1</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-2012-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/1</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-2012-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/1</a></li>
            </ul>
            <h4>30 juni</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r6-2012">TOP10NL R6-2012</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r6-2012">TOP25raster R6-2012</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r6-2012">TOP50raster R6-2012</a></li>
            </ul>
            <p>TOP50vector</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50vector#top50vector-r6-2012">TOP50vector R6-2012</a></li>
            </ul>
            <p>TOPgrenzen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/topgrenzen#topgrenzen-r6-2012">TOPgrenzen R6-2012</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2012</h3>
            <h4>1 juli</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#q2-2012">Q2 2012</a></li>
            </ul>
            <p>IMKAD</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/imkad#imkad-2-1-1">IMKAD 2.1.1.</a></li>
            </ul>
            <p>Inspire Adressen (WFS/WMS)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/inspire-adressen-wfs-wms#inspire-adressen-tijdelijke-voorziening">Inspire Adressen, tijdelijke voorziening</a></li>
            </ul>
            <h4>2 juli</h4>
            <p><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-inkomen-bri/' . $this->releasekalender_queryvar_product . '/bri-lv#bri-lv-1-2"><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr> 1.2</a></li>
            </ul>
            <h4>9 juli</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#eherkenning-release-1-5-medio-2012"><abbr title="Elektronische Herkenning">eHerkenning</abbr> Release 1.5 (medio 2012)</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2012</h3>
            <h4>30 augustus</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-6-4-0-beheerfunctionaliteit"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 6.4.0 - Beheerfunctionaliteit</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>September 2012</h3>
            <h4>3 september</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Terugmeldvoorziening (<abbr title="TerugMeldVoorziening">TMV</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-terugmeldvoorziening-tmv#tmv-release-6-4-lo-3-8"><abbr title="TerugMeldVoorziening">TMV</abbr> Release 6.4 - <abbr title="Logisch Ontwerp">LO</abbr> 3.8</a></li>
            </ul>
            <h4>30 september</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r9-2012">TOP10NL R9-2012</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r9-2012">TOP25raster R9-2012</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r9-2012">TOP50raster R9-2012</a></li>
            </ul>
            <p>TOP50vector</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50vector#top50vector-r9-2012">TOP50vector R9-2012</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2012</h3>
            <h4>1 oktober</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Applicatie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-applicatie#gba-applicatie-lo-3-8"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Applicatie <abbr title="Logisch Ontwerp">LO</abbr> 3.8</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-logisch-ontwerp#gba-lo-3-8"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 3.8</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-6-5-lo-3-8"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 6.5 - <abbr title="Logisch Ontwerp">LO</abbr> 3.8</a></li>
            </ul>
            <h4>4 oktober</h4>
            <p>Digikoppeling standaard Grote Berichten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-grote-berichten#digikoppeling-2-0-3-0-kvs-grote-berichten-2-0">Digikoppeling 2.0 - 3.0: KVS Grote Berichten 2.0</a></li>
            </ul>
            <h4>29 oktober</h4>
            <p><abbr title="Basisregistratie Ondergrond">BRO</abbr> algemeen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-algemeen#impact-analyse-en-informatiekundige-uitvoeringstoets">Impact Analyse en Informatiekundige uitvoeringstoets</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2012</h3>
            <h4>18 november</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#q4-2012">Q4 2012</a></li>
            </ul>
            <h4>22 november</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Processenhandboek</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-processenhandboek#bag-processenhandboek-2012"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Processenhandboek 2012</a></li>
            </ul>
            <h4>30 november</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r11-2012">TOP10NL R11-2012</a></li>
            </ul>
            <p>TOP250namen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250namen#top250namen-r11-2012">TOP250namen R11-2012</a></li>
            </ul>
            <p>TOP25namen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25namen#top25namen-r11-2012">TOP25namen R11-2012</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r11-2012">TOP25raster R11-2012</a></li>
            </ul>
            <p>TOP50namen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50namen#top50namen-r11-2012">TOP50namen R11-2012</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r11-2012">TOP50raster R11-2012</a></li>
            </ul>
            <p>TOP50vector</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50vector#top50vector-r11-2012">TOP50vector R11-2012</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2012</h3>
            <h4>1 december</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> download service via <abbr title="Publieke Dienstverlening op de Kaart">PDOK</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-download-service-via-pdok#download-service-brt">Download Service <abbr title="Basisregistratie Topografie">BRT</abbr></a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-6-3-4-full-service-spontaan"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 6.3.4 - Full Service Spontaan</a></li>
            </ul>
            <h4>7 december</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Compact</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-compact#bag-release-2012-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/2</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-2012-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/2</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-2012-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/2</a></li>
            </ul>
            <h4>31 december</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> webservice via <abbr title="Publieke Dienstverlening op de Kaart">PDOK</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top250raster">TOP250raster</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top10nl">TOP10NL</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top25raster">TOP25raster</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top50raster">TOP50raster</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#brt-achtergrondkaart"><abbr title="Basisregistratie Topografie">BRT</abbr> Achtergrondkaart</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2013">2013</h2>
          <div class="unit">
            <h3>Januari 2013</h3>
            <h4>1 januari</h4>
            <p>Aansluiten Overheid - HR Dataservice (applicatie-applicatie)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/aansluiten-overheid-hr-dataservice-applicatie-applicatie#aansluiten-overheid-hr-dataservice-applicatie-applicatie-stuf-versie">Aansluiten Overheid - HR Dataservice (applicatie-applicatie) <abbr title="Standaard Uitwisselings Formaat">StUF</abbr> versie</a></li>
            </ul>
            <h4>8 januari</h4>
            <p><abbr title="Nieuw Handelsregister">NHR</abbr> Gegevenscatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/nhr-gegevenscatalogus#gegevenscatalogus-handelsregister-versie-2-3">Gegevenscatalogus Handelsregister (versie 2.3)</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2013</h3>
            <h4>1 februari</h4>
            <p>Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-1-0-abonnementenvoorziening">Digilevering 1.0 Abonnementenvoorziening</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-2-0">Digilevering 2.0</a></li>
            </ul>
            <h4>22 februari</h4>
            <p><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-inkomen-bri/' . $this->releasekalender_queryvar_product . '/bri-lv#bri-lv-1-3"><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr> 1.3</a></li>
            </ul>
            <h4>28 februari</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r2-2013">TOP10NL R2-2013</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r2-2013">TOP25raster R2-2013</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r2-2013">TOP50raster R2-2013</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2013</h3>
            <h4>1 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WFS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-wfs#publicatie-tijdelijke-bag-wfs">Publicatie Tijdelijke <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WFS</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WMS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-wms#publicatie-tijdelijke-bag-wms">Publicatie Tijdelijke <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WMS</a></li>
            </ul>
            <p>Evaluatiemethode en technieken webrichtlijnen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/webrichtlijnen/' . $this->releasekalender_queryvar_product . '/evaluatiemethode-en-technieken-webrichtlijnen#evaluatiemethode-en-technieken-webrichtlijnen-versie-2">Evaluatiemethode en technieken webrichtlijnen versie 2</a></li>
            </ul>
            <p>Platform e-overheid voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/platform-e-overheid-voor-bedrijven#ondernemersplatform">Ondernemersplatform</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2013</h3>
            <h4>1 april</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#eherkenning-release-1-7-1-april-2013"><abbr title="Elektronische Herkenning">eHerkenning</abbr> Release 1.7 (1 april 2013)</a></li>
            </ul>
            <h4>11 april</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Gegevensmodel</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-top10nl-gegevensmodel#brt-top10nl-objectencatalogus-2-1"><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Objectencatalogus 2.1</a></li>
            </ul>
            <h4>15 april</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-versie-4-2-digid-buitenland"><abbr title="Digitale Identiteit">DigiD</abbr> Versie 4.2 &quot;<abbr title="Digitale Identiteit">DigiD</abbr> Buitenland&quot;</a></li>
            </ul>
            <h4>30 april</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r4-2013">TOP10NL R4-2013</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r4-2013">TOP25raster R4-2013</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r4-2013">TOP50raster R4-2013</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2013</h3>
            <h4>13 mei</h4>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-2-0">Stelselcatalogus 2.0</a></li>
            </ul>
            <h4>15 mei</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-1"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2013</h3>
            <h4>1 juni</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#juni-2014">juni 2014</a></li>
            </ul>
            <p>Landelijke voorziening <abbr title="Waarde Onroerende Zaken">WOZ</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/landelijke-voorziening-woz#lv-woz-1-0"><abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr> 1.0</a></li>
            </ul>
            <h4>30 juni</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r6-2013">TOP10NL R6-2013</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r6-2013">TOP25raster R6-2013</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r6-2013">TOP50raster R6-2013</a></li>
            </ul>
            <p>TOPgrenzen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/topgrenzen#topgrenzen-r6-2013">TOPgrenzen R6-2013</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2013</h3>
            <h4>1 juli</h4>
            <p>Antwoord voor Bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/antwoord-voor-bedrijven#koppeling-digitaal-ondernemersplein-1-1">Koppeling Digitaal Ondernemersplein 1.1</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="TerugMeldVoorziening">TMV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-tmv#koppeling-digimelding-1-2">Koppeling Digimelding 1.2</a></li>
            </ul>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Standaard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-standaard#bgt-gegevenscatalogus-1-1-1"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr>-gegevenscatalogus 1.1.1</a></li>
            </ul>
            <p>Digimelding Portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-portaal#dm-portaal-1-2">DM Portaal 1.2</a></li>
            </ul>
            <h4>21 juli</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#downloaden-in-een-zip">downloaden in een ZIP</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2013</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2013</h3>
            <h4>1 september</h4>
            <p>TOP250NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250nl#top250nl-r9-2013">TOP250NL R9-2013</a></li>
            </ul>
            <h4>2 september</h4>
            <p>Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-2-1">Digilevering 2.1</a></li>
            </ul>
            <h4>15 september</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#september-2013">september 2013</a></li>
            </ul>
            <p>Digikoppeling ontkoppelpunt</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-ontkoppelpunt#digikoppeling-3-0-vertaalspecificatie-0-9">Digikoppeling 3.0: Vertaalspecificatie 0.9</a></li>
            </ul>
            <h4>21 september</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-2012-2-sp-5"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2012/2 SP 5</a></li>
            </ul>
            <h4>30 september</h4>
            <p>Digikoppeling standaard Architectuur</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-architectuur#digikoppeling-3-0-architectuur-0-9">Digikoppeling 3.0: Architectuur 0.9</a></li>
            </ul>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r9-2013">TOP10NL R9-2013</a></li>
            </ul>
            <p>TOP250raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250raster#top250raster-r9-2013">TOP250raster R9-2013</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r9-2013">TOP25raster R9-2013</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r9-2013">TOP50raster R9-2013</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2013</h3>
            <h4>1 oktober</h4>
            <p>KIK-inzage (Ketenintegratie Inschrijving Kadaster)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kik-inzage-ketenintegratie-inschrijving-kadaster#kik-inzage-4-5">KIK-inzage 4.5</a></li>
            </ul>
            <h4>17 oktober</h4>
            <p>Antwoord voor Bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/antwoord-voor-bedrijven#website-r-13-10">Website: R.13-10</a></li>
            </ul>
            <h4>18 oktober</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Compact</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-compact#bag-release-2013-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2013/1</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-2013-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2013/1</a></li>
            </ul>
            <h4>27 oktober</h4>
            <p>Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-2-2">Digilevering 2.2</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2013</h3>
            <h4>1 november</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Levering.</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-levering#brk-levering-gebied-gemeente"><abbr title="Basisregistratie Kadaster">BRK</abbr> Levering (gebied gemeente)</a></li>
            </ul>
            <h4>8 november</h4>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-2-1">Stelselcatalogus 2.1</a></li>
            </ul>
            <h4>14 november</h4>
            <p>Simplerinvoicing - Specificaties</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/e-factureren/' . $this->releasekalender_queryvar_product . '/simplerinvoicing-specificaties#specificaties-1-0">Specificaties 1.0</a></li>
            </ul>
            <p>Simplerinvoicing – Discovery engine</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/e-factureren/' . $this->releasekalender_queryvar_product . '/simplerinvoicing-discovery-engine#discovery-engine">Discovery engine</a></li>
            </ul>
            <h4>15 november</h4>
            <p>Antwoord voor Bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/antwoord-voor-bedrijven#antwoord-voor-bedrijven-mobiele-site">Antwoord voor bedrijven:mobiele site</a></li>
            </ul>
            <p>Digikoppeling standaard WUS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-wus#digikoppeling-3-0-kvs-wus-3-0">Digikoppeling 3.0: KVS WUS 3.0</a></li>
            </ul>
            <h4>30 november</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r11-2013">TOP10NL R11-2013</a></li>
            </ul>
            <p>TOP25namen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25namen#top25namen-r11-2013">TOP25namen R11-2013</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r11-2013">TOP25raster R11-2013</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r11-2013">TOP50raster R11-2013</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2013</h3>
            <h4>1 december</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#beveiligingsrelease">Beveiligingsrelease</a></li>
            </ul>
            <h4>5 december</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Processenhandboek</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-processenhandboek#bag-processenhandboek-2013"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Processenhandboek 2013</a></li>
            </ul>
            <h4>8 december</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#december-2013">december 2013</a></li>
            </ul>
            <h4>13 december</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-2013-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2013/2</a></li>
            </ul>
            <h4>31 december</h4>
            <p>Digipoort koppelvlakken SMTP, POP3, FTP en X400</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digipoort/' . $this->releasekalender_queryvar_product . '/digipoort-koppelvlakken-smtp-pop3-ftp-en-x400#uitfasering-koppelvlak-x400-bedrijven">Uitfasering koppelvlak X400 bedrijven</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2014">2014</h2>
          <div class="unit">
            <h3>Januari 2014</h3>
            <h4>1 januari</h4>
            <p>Beheervoorziening <abbr title="Burgerservicenummer">BSN</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/burgerservicenummer/' . $this->releasekalender_queryvar_product . '/beheervoorziening-bsn#bsn-release-5-9-tot-6-1-ihkv-gba-lo-3-8"><abbr title="Burgerservicenummer">BSN</abbr> Release 5.9 tot 6.1 (ihkv <abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 3.8)</a></li>
            </ul>
            <p><abbr title="Basisregistratie Personen">BRP</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-regelgeving#wet-basisregistratie-personen">Wet Basisregistratie Personen</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-gegevensverzameling#kentekenregister-uitbreiding-registratie-kilometer-tellerstanden">Kentekenregister - uitbreiding registratie (kilometer)tellerstanden</a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Interactieve/online gegevensdiensten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-interactieve-online-gegevensdiensten#uitbreiding-kilometer-tellerstanden">Uitbreiding (kilometer)tellerstanden</a></li>
            </ul>
            <p>Digikoppeling ontkoppelpunt</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-ontkoppelpunt#digikoppeling-3-0-vertaalspecificatie-1-0">Digikoppeling 3.0: Vertaalspecificatie 1.0</a></li>
            </ul>
            <p>Digikoppeling standaard Architectuur</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-architectuur#digikoppeling-3-0-architectuur-1-0">Digikoppeling 3.0: Architectuur 1.0</a></li>
            </ul>
            <p>Kadaster Woordenboek</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kadaster-woordenboek#kadaster-woordenboek-1-0">Kadaster woordenboek 1.0</a></li>
            </ul>
            <p>KIK-inzage (Ketenintegratie Inschrijving Kadaster)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kik-inzage-ketenintegratie-inschrijving-kadaster#kik-inzage-4-6">KIK-inzage 4.6</a></li>
            </ul>
            <p>Registratie Niet-Ingezetenen (<abbr title="Registratie Niet Ingezetenen">RNI</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/registratie-niet-ingezetenen-rni#rni-applicatie-1-0"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> Applicatie 1.0</a></li>
            </ul>
            <p><abbr title="Registratie Niet Ingezetenen">RNI</abbr> Deelnemers - Webservice</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/rni-deelnemers-webservice#rni-deelnemers-webservice-1-0"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> Deelnemers - Webservice 1.0</a></li>
            </ul>
            <p><abbr title="Registratie Niet Ingezetenen">RNI</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/rni-gegevensverzameling#rni-gegevensverzameling-2012"><abbr title="Registratie Niet Ingezetenen">RNI</abbr>-Gegevensverzameling 2012</a></li>
            </ul>
            <p><abbr title="Waarde Onroerende Zaken">WOZ</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/woz-regelgeving#wet-waardering-onroerende-zaken-formele-basis-lv-woz">Wet waardering onroerende zaken Formele basis <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
            </ul>
            <h4>20 januari</h4>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#ondernemersplein-1-0">Ondernemersplein 1.0</a></li>
            </ul>
            <h4>25 januari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-2013-2"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2013/2</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2014</h3>
            <h4>21 februari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-2014-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2014/1</a></li>
            </ul>
            <h4>28 februari</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r2-2014">TOP10NL R2-2014</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r2-2014">TOP25raster R2-2014</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r2-2014">TOP50raster R2-2014</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2014</h3>
            <h4>1 maart</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Levering.</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-levering#brk-levering-persoonsgebonden"><abbr title="Basisregistratie Kadaster">BRK</abbr> Levering (persoonsgebonden)</a></li>
            </ul>
            <h4>3 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Beheerauditrapportage</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-beheerauditrapportage#bag-release-3-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 3-2014</a></li>
            </ul>
            <h4>17 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Beheerauditrapportage</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-beheerauditrapportage#bag-release-3-1-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 3.1-2014</a></li>
            </ul>
            <h4>21 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Beheerauditrapportage</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-beheerauditrapportage#bag-release-3-3-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 3.3-2014</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Compact</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-compact#bag-release-3-2-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 3.2-2014</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#bag-release-3-2-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 3.2-2014</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Web</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-web#bag-release-3-2-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 3.2-2014</a></li>
            </ul>
            <h4>26 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Beheerauditrapportage</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-beheerauditrapportage#bag-release-2014-1"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2014/1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2014</h3>
            <h4>1 april</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Keten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-keten#bgt-ketenrelease-1"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Ketenrelease 1</a></li>
            </ul>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Standaard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-standaard#objectenhandboek">Objectenhandboek</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-standaard#visualisatie-handreiking-1-2">Visualisatie handreiking 1.2</a></li>
            </ul>
            <p>Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-3-0">Digilevering 3.0</a></li>
            </ul>
            <p>HR Dataservice Berichten via Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/hr-dataservice-berichten-via-digilevering#hr-dataservice-berichten-via-digilevering">HR Dataservice Berichten via Digilevering</a></li>
            </ul>
            <h4>21 april</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-viewer#bag-viewer-hogere-actualiteit"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer hogere actualiteit</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WFS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-wfs#verhogen-update-frequentie">Verhogen update frequentie</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WMS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-wms#verhogen-update-frequentie">Verhogen update frequentie</a></li>
            </ul>
            <h4>25 april</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-4-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> release 4-2014</a></li>
            </ul>
            <h4>27 april</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Gegevensmodel</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-top10nl-gegevensmodel#brt-top10nl-objectencatalogus-2-2"><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Objectencatalogus 2.2</a></li>
            </ul>
            <h4>30 april</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r4-2014">TOP10NL R4-2014</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r4-2014">TOP25raster R4-2014</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r04-2014">TOP50raster R04-2014</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2014</h3>
            <h4>19 mei</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-versie-4-3-sterk-wachtwoord"><abbr title="Digitale Identiteit">DigiD</abbr> Versie 4.3 &quot;Sterk Wachtwoord&quot;</a></li>
            </ul>
            <p>PIVA Verstrekkingen (PIVA-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-verstrekkingen-piva-v#piva-v-1-1">PIVA-V 1.1</a></li>
            </ul>
            <h4>21 mei</h4>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-2-2">Stelselcatalogus 2.2</a></li>
            </ul>
            <h4>23 mei</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#mei-2014">mei 2014</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2014</h3>
            <h4>2 juni</h4>
            <p>Mijn Ondernemingsdossier</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemingsdossier/' . $this->releasekalender_queryvar_product . '/mijn-ondernemingsdossier#uitbreiding-functionaliteit">Uitbreiding functionaliteit</a></li>
            </ul>
            <h4>20 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-bevragen#bag-releasenote-6-2014"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> releasenote 6-2014</a></li>
            </ul>
            <h4>30 juni</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r6-2014">TOP10NL R6-2014</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r6-2014">TOP25raster R6-2014</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r6-2014">TOP50raster R6-2014</a></li>
            </ul>
            <p>TOPgrenzen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/topgrenzen#topgrenzen-r6-2014">TOPgrenzen R6-2014</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2014</h3>
            <h4>1 juli</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#juli-2014">juli 2014</a></li>
            </ul>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Keten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-keten#bgt-ketenrelease-1a"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Ketenrelease 1A</a></li>
            </ul>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Standaard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-standaard#berichtenstandaard-stuf-geo-imgeo-1-1-1">Berichtenstandaard <abbr title="Standaard Uitwisselings Formaat">StUF</abbr>-Geo IMgeo 1.1.1</a></li>
            </ul>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Gegevensverzameling AKR</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-akr#akr-bestand-inclusief-koppeling-met-nhr-gegevens">AKR bestand (inclusief koppeling met <abbr title="Nieuw Handelsregister">NHR</abbr> gegevens)</a></li>
            </ul>
            <p><abbr title="Basisregistratie Personen">BRP</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-logisch-ontwerp#brp-lo-1-0"><abbr title="Basisregistratie Personen">BRP</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 1.0</a></li>
            </ul>
            <p>Gemeentelijke <abbr title="Waarde Onroerende Zaken">WOZ</abbr>-applicaties</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/gemeentelijke-woz-applicaties#gemeentelijke-woz-applicaties-voorziet-in-aansluiten-op-lv-woz">Gemeentelijke <abbr title="Waarde Onroerende Zaken">WOZ</abbr>-applicaties voorziet in aansluiten op <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
            </ul>
            <p>Landelijke voorziening <abbr title="Waarde Onroerende Zaken">WOZ</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/landelijke-voorziening-woz#webapplicatie-individuele-bevraging-lv-woz">Webapplicatie individuele bevraging <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/landelijke-voorziening-woz#webservices-individuele-synchrone-bevraging">Webservices individuele synchrone bevraging</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/landelijke-voorziening-woz#webservice-massale-bevraging-lv-woz">Webservice massale bevraging <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
            </ul>
            <p>Mijn Ondernemingsdossier</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemingsdossier/' . $this->releasekalender_queryvar_product . '/mijn-ondernemingsdossier#berichtenbox">Berichtenbox</a></li>
            </ul>
            <p>Platform e-overheid voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/platform-e-overheid-voor-bedrijven#ondernemersplatform-sso-eherkenning">Ondernemersplatform SSO <abbr title="Elektronische Herkenning">eHerkenning</abbr></a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/platform-e-overheid-voor-bedrijven#platform-e-overheid-voor-bedrijven">Platform e-overheid voor bedrijven</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2014</h3>
            <h4>1 augustus</h4>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#uitbreiding-inhoud">Uitbreiding inhoud</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>September 2014</h3>
            <h4>1 september</h4>
            <p>Digikoppeling Serviceregister</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-serviceregister#serviceregister-2-3">Serviceregister 2.3</a></li>
            </ul>
            <h4>14 september</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#aanmaak-nieuwe-berichtenboxen-vanuit-het-ondernemingsdossier">Aanmaak nieuwe berichtenboxen vanuit het ondernemingsdossier</a></li>
            </ul>
            <h4>15 september</h4>
            <p>Antwoord voor Bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/antwoord-voor-bedrijven#antwoord-voor-bedrijven-is-niet-meer-als-zelfstandige-website-te-benaderen">Antwoord voor bedrijven is niet meer als zelfstandige website te benaderen.</a></li>
            </ul>
            <h4>30 september</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r9-2014">TOP10NL R9-2014</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r9-2014">TOP25raster R9-2014</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r09-2014">TOP50raster R09-2014</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2014</h3>
            <h4>1 oktober</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#eherkenning"><abbr title="Elektronische Herkenning">eHerkenning</abbr></a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#aansluitvoorziening-via-digikoppeling">Aansluitvoorziening via Digikoppeling</a></li>
            </ul>
            <p>Digimelding 2.0</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-2-0#digimelding-blt-hr-1-0">Digimelding BLT HR 1.0</a></li>
            </ul>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#ondernemersagenda">Ondernemersagenda</a></li>
            </ul>
            <h4>6 oktober</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-4-usability"><abbr title="Digitale Identiteit">DigiD</abbr> 4.4 &quot;Usability&quot;</a></li>
            </ul>
            <h4>9 oktober</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-3-1-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.3.1 &quot;Beheer en Beveiliging&quot;</a></li>
            </ul>
            <h4>14 oktober</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-3-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.3 &quot;Beheer en Beveiliging&quot;</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2014</h3>
            <h4>1 november</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WMS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-wms#verhogen-update-frequentie-naar-dagelijks-in-november-2014">verhogen update frequentie naar dagelijks in november 2014</a></li>
            </ul>
            <h4>4 november</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-4-1-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.4.1 &quot;Beheer en Beveiliging&quot;</a></li>
            </ul>
            <h4>14 november</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/bag-digilevering#initiele-versie">Initiele versie</a></li>
            </ul>
            <h4>30 november</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r11-2014">TOP10NL R11-2014</a></li>
            </ul>
            <p>TOP25namen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25namen#top25namen-r11-2014">TOP25namen R11-2014</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r11-2014">TOP25raster R11-2014</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r11-2014">TOP50raster R11-2014</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2014</h3>
            <h4>1 december</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Levering.</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-levering#brk-levering-geografisch-gebied"><abbr title="Basisregistratie Kadaster">BRK</abbr> Levering (geografisch gebied)</a></li>
            </ul>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-2-3">Stelselcatalogus 2.3</a></li>
            </ul>
            <h4>7 december</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#sessie-informatie">sessie-informatie</a></li>
            </ul>
            <h4>8 december</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-4-2-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.4.2 &quot;Beheer en Beveiliging&quot;</a></li>
            </ul>
            <h4>15 december</h4>
            <p>Digimelding 2.0</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-2-0#digimelding-blt-brp-1-0">Digimelding BLT <abbr title="Basisregistratie Personen">BRP</abbr> 1.0</a></li>
            </ul>
            <h4>31 december</h4>
            <p>Evaluatiemethode en technieken webrichtlijnen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/webrichtlijnen/' . $this->releasekalender_queryvar_product . '/evaluatiemethode-en-technieken-webrichtlijnen#evaluatiemethode-en-technieken-webrichtlijnen-versie-1">Evaluatiemethode en technieken webrichtlijnen versie 1</a></li>
            </ul>
            <p>Informatie Publicatie Model Samenwerkende Catalogi</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/samenwerkende-catalogi/' . $this->releasekalender_queryvar_product . '/informatie-publicatie-model-samenwerkende-catalogi#ipm-samenwerkende-catalogi-2-1">IPM Samenwerkende Catalogi 2.1</a></li>
            </ul>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#eherkenning"><abbr title="Elektronische Herkenning">eHerkenning</abbr></a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#personalisatie">Personalisatie</a></li>
            </ul>
            <p>Simplerinvoicing - Specificaties</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/e-factureren/' . $this->releasekalender_queryvar_product . '/simplerinvoicing-specificaties#specificaties-1-1">Specificaties 1.1</a></li>
            </ul>
            <p>Webrichtlijnen norm</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/webrichtlijnen/' . $this->releasekalender_queryvar_product . '/webrichtlijnen-norm#webrichtlijnen-versie-1-norm">Webrichtlijnen versie 1 norm</a></li>
            </ul>
            <p>Webrichtlijnen quickscan</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/webrichtlijnen/' . $this->releasekalender_queryvar_product . '/webrichtlijnen-quickscan#webrichtlijnen-versie-1-quickscan">Webrichtlijnen versie 1 quickscan</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2015">2015</h2>
          <div class="unit">
            <h3>Januari 2015</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Standaard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-standaard#berichtenstandaard-stuf-geo-imgeo-1-2">Berichtenstandaard <abbr title="Standaard Uitwisselings Formaat">StUF</abbr>-Geo IMgeo 1.2</a></li>
            </ul>
            <p>Digikoppeling ontkoppelpunt</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-ontkoppelpunt#digikoppeling-3-0-centrale-vertaaldienst-1-0-pilot">Digikoppeling 3.0: Centrale vertaaldienst 1.0 (pilot)</a></li>
            </ul>
            <p>Digimelding 2.0</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-2-0#digimelding-annotatie-specificatie-digimelding-as">Digimelding Annotatie Specificatie (Digimelding AS)</a></li>
            </ul>
            <h4>20 januari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-viewer#vernieuwde-bag-viewer">Vernieuwde <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</a></li>
            </ul>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-4-3-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.4.3 &quot;Beheer en Beveiliging&quot;</a></li>
            </ul>
            <h4>30 januari</h4>
            <p>Mijn Ondernemingsdossier</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemingsdossier/' . $this->releasekalender_queryvar_product . '/mijn-ondernemingsdossier#koppeling-tenderned">koppeling Tenderned</a></li>
            </ul>
            <h4>31 januari</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-logisch-ontwerp#gba-lo-3-9"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 3.9</a></li>
            </ul>
            <p>PIVA Logisch Ontwerp BES</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-logisch-ontwerp-bes#bes-lo-1-2">BES <abbr title="Logisch Ontwerp">LO</abbr> 1.2</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2015</h3>
            <h4>1 februari</h4>
            <p>KIK-inzage (Ketenintegratie Inschrijving Kadaster)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kik-inzage-ketenintegratie-inschrijving-kadaster#kik-inzage-4-7">KIK-inzage 4.7</a></li>
            </ul>
            <h4>9 februari</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-5-balie"><abbr title="Digitale Identiteit">DigiD</abbr> 4.5 &quot;Balie&quot;</a></li>
            </ul>
            <h4>20 februari</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r2-2015">TOP10NL R2-2015</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r2-2015">TOP25raster R2-2015</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r2-2015">TOP50raster R2-2015</a></li>
            </ul>
            <h4>25 februari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-bevragen#bag-bevragen-koppelvlak-1-4-0"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen Koppelvlak 1.4.0</a></li>
            </ul>
            <h4>27 februari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#bag-lv-koppelvlak-1-3-0"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr> Koppelvlak 1.3.0</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2015</h3>
            <h4>1 maart</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Keten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-keten#bgt-ketenrelease-2"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Ketenrelease 2</a></li>
            </ul>
            <h4>25 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-viewer#diverse-verbeteringen-oa-uitgebreid-zoeken-toegevoegd">Diverse verbeteringen, oa uitgebreid zoeken toegevoegd</a></li>
            </ul>
            <h4>26 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#bag-release-2015-01"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Release 2015-01</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2015</h3>
            <h4>1 april</h4>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Gegevensverzameling AKR</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-akr#brk-gegevens-verzameling-incl-rni-gegevens"><abbr title="Basisregistratie Kadaster">BRK</abbr> gegevens verzameling (incl <abbr title="Registratie Niet Ingezetenen">RNI</abbr> gegevens)</a></li>
            </ul>
            <p>Digikoppeling ontkoppelpunt</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-ontkoppelpunt#digikoppeling-3-0-decentraal-ontkoppelpunt-1-0">Digikoppeling 3.0: Decentraal ontkoppelpunt 1.0</a></li>
            </ul>
            <h4>24 april</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r4-2015">TOP10NL R4-2015</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r4-2015">TOP25raster R4-2015</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r4-2015">TOP50NL R4-2015</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r4-2015">TOP50raster R4-2015</a></li>
            </ul>
            <h4>26 april</h4>
            <p>TOPgrenzen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/topgrenzen#topgrenzen-r4-2015">TOPgrenzen R4-2015</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2015</h3>
            <h4>1 mei</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#afsprakenstelsel-1-9">Afsprakenstelsel 1.9</a></li>
            </ul>
            <h4>18 mei</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="TerugMeldVoorziening">TMV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-tmv#koppeling-digimelding-2-0">Koppeling Digimelding 2.0</a></li>
            </ul>
            <h4>20 mei</h4>
            <p>Digimelding 2.0</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-2-0#digimelding-blt-bag-1-0">Digimelding BLT <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> 1.0</a></li>
            </ul>
            <h4>30 mei</h4>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#start-up-delta">Start Up Delta</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2015</h3>
            <h4>1 juni</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> WMTS</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-wmts#realisatie-wmts">Realisatie WMTS</a></li>
            </ul>
            <p><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-inkomen-bri/' . $this->releasekalender_queryvar_product . '/bri-lv#bri-lv-1-5"><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr> 1.5</a></li>
            </ul>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> Catalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-catalogus#brk-catalogus-imkad-2-1-1"><abbr title="Basisregistratie Kadaster">BRK</abbr> Catalogus IMKAD 2.1.1</a></li>
            </ul>
            <h4>15 juni</h4>
            <p>Digikoppeling Compliancevoorziening</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-compliancevoorziening#compliancevoorziening-2-3">Compliancevoorziening 2.3</a></li>
            </ul>
            <h4>24 juni</h4>
            <p>MijnOverheid Berichtenbox</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-berichtenbox#mijnoverheid-berichtenbox-release-2-3-april-2015">MijnOverheid Berichtenbox release 2.3 april 2015</a></li>
            </ul>
            <p>MijnOverheid Lopende zaken</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-lopende-zaken#mijnoverheid-lopende-zaken-release-2-3-april-2015">MijnOverheid Lopende Zaken release 2.3 april 2015</a></li>
            </ul>
            <p>MijnOverheid Persoonlijke gegevens</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-persoonlijke-gegevens#mijnoverheid-persoonlijke-gegevens-release-2-3-april-2015">MijnOverheid Persoonlijke Gegevens release 2.3 april 2015</a></li>
            </ul>
            <p>MijnOverheid portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-portaal#mijnoverheid-release-2-3-april-2015">MijnOverheid release 2.3 april 2015</a></li>
            </ul>
            <h4>26 juni</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r6-2015">TOP10NL R6-2015</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r6-2015">TOP25raster R6-2015</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r6-2015">TOP50NL R6-2015</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r6-2015">TOP50raster R6-2015</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2015</h3>
            <h4>1 juli</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Keten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-keten#bgt-ketenrelease-2a"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Ketenrelease 2A</a></li>
            </ul>
            <p>Inspire Adressen (WFS/WMS)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/inspire-adressen-wfs-wms#inspire-adressen-geharmoniseerd">Inspire adressen geharmoniseerd</a></li>
            </ul>
            <h4>13 juli</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-7-pilot-alternatief-voor-sms"><abbr title="Digitale Identiteit">DigiD</abbr> 4.7 &quot;Pilot alternatief voor sms&quot;</a></li>
            </ul>
            <h4>30 juli</h4>
            <p>Normenstelsel</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/pkioverheid/' . $this->releasekalender_queryvar_product . '/normenstelsel#pve-versie-4-1">PvE versie 4.1</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2015</h3>
            <h4>1 augustus</h4>
            <p>KIK-inzage (Ketenintegratie Inschrijving Kadaster)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kik-inzage-ketenintegratie-inschrijving-kadaster#kik-inzage-4-8">KIK - inzage 4.8</a></li>
            </ul>
            <h4>20 augustus</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-bevragen#beta-versie-verbeterde-bag-bevragen-services">Beta versie verbeterde <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen services</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Viewer</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-viewer#diverse-verbeteringen">Diverse verbeteringen</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>September 2015</h3>
            <h4>15 september</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-5-usability"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.5 &quot;Usability&quot;</a></li>
            </ul>
            <h4>16 september</h4>
            <p>Nederlandse Taxonomie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/standard-business-reporting-sbr/' . $this->releasekalender_queryvar_product . '/nederlandse-taxonomie#nederlandse-taxonomie-9-2-2015">Nederlandse Taxonomie 9.2 2015</a></li>
            </ul>
            <h4>21 september</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-8-wachtwoordherstel"><abbr title="Digitale Identiteit">DigiD</abbr> 4.8 &quot;Wachtwoordherstel&quot;</a></li>
            </ul>
            <h4>22 september</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r9-2015">TOP10NL R9-2015</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r9-2015">TOP25raster R9-2015</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r9-2015">TOP50raster R9-2015</a></li>
            </ul>
            <h4>24 september</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-7-8"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 7.8</a></li>
            </ul>
            <h4>25 september</h4>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r9-2015">TOP50NL R9-2015</a></li>
            </ul>
            <h4>30 september</h4>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#financieringswijzer">Financieringswijzer</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2015</h3>
            <h4>1 oktober</h4>
            <p><abbr title="Basisregistratie Ondergrond">BRO</abbr> standaard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-standaard#deelcatalogus-geotechnische-sonderingen">Deelcatalogus Geotechnische Sonderingen</a></li>
            </ul>
            <h4>4 oktober</h4>
            <p>Registratie Niet-Ingezetenen (<abbr title="Registratie Niet Ingezetenen">RNI</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/registratie-niet-ingezetenen-rni#rni"><abbr title="Registratie Niet Ingezetenen">RNI</abbr></a></li>
            </ul>
            <h4>19 oktober</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-6-beveiliging-en-migratie"><abbr title="Digitale Identiteit">DigiD</abbr> 4.6 &quot;Beveiliging en migratie&quot;</a></li>
            </ul>
            <h4>28 oktober</h4>
            <p>MijnOverheid Berichtenbox</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-berichtenbox#release-2-4">Release 2.4</a></li>
            </ul>
            <p>MijnOverheid Lopende zaken</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-lopende-zaken#release-2-4">Release 2.4</a></li>
            </ul>
            <p>MijnOverheid Persoonlijke gegevens</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-persoonlijke-gegevens#release-2-4">Release 2.4</a></li>
            </ul>
            <p>MijnOverheid portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-portaal#release-2-4">Release 2.4</a></li>
            </ul>
            <h4>31 oktober</h4>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#zoekmachine">Zoekmachine</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2015</h3>
            <h4>17 november</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-6-saneren-koppelvlakken"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.6 &quot;Saneren koppelvlakken&quot;</a></li>
            </ul>
            <h4>24 november</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r11-2015">TOP10NL R11-2015</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r11-2015">TOP25raster R11-2015</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r11-2015">TOP50NL R11-2015</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r11-2015">TOP50raster R11-2015</a></li>
            </ul>
            <h4>27 november</h4>
            <p>Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-3-1">Digilevering 3.1</a></li>
            </ul>
            <h4>30 november</h4>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#fact-based-design">Fact Based Design</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#personallisatie">Personallisatie</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>December 2015</h3>
            <h4>10 december</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Kwaliteitsdashboard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-kwaliteitsdashboard#lancering-kwaliteitsdashboard">Lancering kwaliteitsdashboard</a></li>
            </ul>
            <h4>14 december</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-9-voorbereiding-digid-middel-hoog"><abbr title="Digitale Identiteit">DigiD</abbr> 4.9 &quot;Voorbereiding <abbr title="Digitale Identiteit">DigiD</abbr> middel hoog&quot;</a></li>
            </ul>
            <h4>16 december</h4>
            <p>Nederlandse Taxonomie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/standard-business-reporting-sbr/' . $this->releasekalender_queryvar_product . '/nederlandse-taxonomie#nederlandse-taxonomie-10-0">Nederlandse Taxonomie 10.0</a></li>
            </ul>
            <h4>18 december</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#afsprakenstelsel-1-9-in-productie">Afsprakenstelsel 1.9 in productie</a></li>
            </ul>
            <h4>31 december</h4>
            <p>Normenstelsel</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/pkioverheid/' . $this->releasekalender_queryvar_product . '/normenstelsel#pve-versie-4-2">PvE versie 4.2</a></li>
            </ul>
            <p>Ondernemersplein</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/ondernemersplein/' . $this->releasekalender_queryvar_product . '/ondernemersplein#engelstalige-website">Engelstalige website</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2016">2016</h2>
          <div class="unit">
            <h3>Januari 2016</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-regelgeving#wet-bgt">Wet <abbr title="Basisregistratie grootschalige Topografie">BGT</abbr></a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-regelgeving#ministeriele-regeling-koppelvlak-bronhouders-lv">Ministeriele regeling koppelvlak bronhouders - <abbr title="Landelijke Voorziening">LV</abbr></a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-regelgeving#ministeriele-regeling-gegevenscatalogus">Ministeriele regeling gegevenscatalogus</a></li>
            </ul>
            <p><abbr title="Basisregistratie Kadaster">BRK</abbr> gegevensverzameling Geo</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/brk-gegevensverzameling-geo#kadastrale-kaart">Kadastrale Kaart</a></li>
            </ul>
            <p>INSPIRE Gebouwen (geharmoniseerd)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/inspire-gebouwen-geharmoniseerd#inspire-gebouwen-geharmoniseerd">INSPIRE Gebouwen (geharmoniseerd)</a></li>
            </ul>
            <h4>19 januari</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-7-verbeteren-performance"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.7 &quot;Verbeteren performance&quot;</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2016</h3>
            <h4>1 februari</h4>
            <p>Registratie Niet-Ingezetenen (<abbr title="Registratie Niet Ingezetenen">RNI</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/registratie-niet-ingezetenen-rni#rni-3-20-3-21"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> 3.20/3.21</a></li>
            </ul>
            <h4>9 februari</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-7-9-7-10"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V Release 7.9/7.10</a></li>
            </ul>
            <h4>16 februari</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-7-1-drempelvrij-waarmerk"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.7.1 &quot;Drempelvrij waarmerk&quot;</a></li>
            </ul>
            <h4>17 februari</h4>
            <p>Nederlandse Taxonomie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/standard-business-reporting-sbr/' . $this->releasekalender_queryvar_product . '/nederlandse-taxonomie#nederlandse-taxonomie-10-3">Nederlandse Taxonomie 10.3</a></li>
            </ul>
            <h4>22 februari</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-10-voorbereiding-digid-op-de-digid-app"><abbr title="Digitale Identiteit">DigiD</abbr> 4.10 &quot;Voorbereiding <abbr title="Digitale Identiteit">DigiD</abbr> op de <abbr title="Digitale Identiteit">DigiD</abbr> app&quot;</a></li>
            </ul>
            <h4>25 februari</h4>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r2-2016">TOP10NL R2-2016</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r2-2016">TOP25raster R2-2016</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r2-2016">TOP50NL R2-2016</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r2-2016">TOP50raster R2-2016</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2016</h3>
            <h4>1 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Bevragen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-bevragen#bag-bevragen-2-0-beschikbaar-gesteld"><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> bevragen 2.0 beschikbaar gesteld</a></li>
            </ul>
            <h4>4 maart</h4>
            <p>Registratie Niet-Ingezetenen (<abbr title="Registratie Niet Ingezetenen">RNI</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/registratie-niet-ingezetenen-rni#rni-3-22"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> 3.22</a></li>
            </ul>
            <h4>10 maart</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-extract#nieuw-bestelscherm-bag-extract">Nieuw bestelscherm <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Extract</a></li>
            </ul>
            <h4>31 maart</h4>
            <p>Digipoort koppelvlakken SMTP, POP3, FTP en X400</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digipoort/' . $this->releasekalender_queryvar_product . '/digipoort-koppelvlakken-smtp-pop3-ftp-en-x400#uitfasering-koppelvlak-x400-overheid">Uitfasering koppelvlak X400 overheid</a></li>
            </ul>
            <p>Overheid.nl</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/overheid-nl/' . $this->releasekalender_queryvar_product . '/overheid-nl#toeleiding-naar-life-events">Toeleiding naar life-events</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/overheid-nl/' . $this->releasekalender_queryvar_product . '/overheid-nl#aanpassingen-zbo-register">Aanpassingen ZBO-register</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2016</h3>
            <h4>1 april</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Kwaliteitsdashboard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-kwaliteitsdashboard#uitbreiding-functionaliteit-kwaliteitsdashboard">Uitbreiding functionaliteit kwaliteitsdashboard</a></li>
            </ul>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Standaard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-standaard#visualisatie-handreiking-1-3">Visualisatie handreiking 1.3</a></li>
            </ul>
            <h4>19 april</h4>
            <p><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-inkomen-bri/' . $this->releasekalender_queryvar_product . '/bri-lv#bri-lv-1-4"><abbr title="Basisregistratie Inkomen">BRI</abbr> <abbr title="Landelijke Voorziening">LV</abbr> 1.4</a></li>
            </ul>
            <h4>20 april</h4>
            <p>Nederlandse Taxonomie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/standard-business-reporting-sbr/' . $this->releasekalender_queryvar_product . '/nederlandse-taxonomie#nederlandse-taxonomie-10-1">Nederlandse Taxonomie 10.1</a></li>
            </ul>
            <h4>30 april</h4>
            <p>MijnOverheid Berichtenbox</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-berichtenbox#release-2-5">Release 2.5</a></li>
            </ul>
            <p>MijnOverheid Lopende zaken</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-lopende-zaken#release-2-5">Release 2.5</a></li>
            </ul>
            <p>MijnOverheid Persoonlijke gegevens</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-persoonlijke-gegevens#release-2-5">Release 2.5</a></li>
            </ul>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r4-2016">TOP10NL R4-2016</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r4-2016">TOP25raster R4-2016</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r4-2016">TOP50NL R4-2016</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r4-2016">TOP50raster R4-2016</a></li>
            </ul>
            <p>TOPgrenzen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/topgrenzen#topgrenzen-r4-2016">TOPgrenzen R4-2016</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2016</h3>
            <h4>1 mei</h4>
            <p><abbr title="Nieuw Handelsregister">NHR</abbr> Gebeurtenissencatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-handelsregister-hr/' . $this->releasekalender_queryvar_product . '/nhr-gebeurtenissencatalogus#nhr-gebeurtenissencatalogus-versie-3-0"><abbr title="Nieuw Handelsregister">NHR</abbr> Gebeurtenissencatalogus (versie 3.0)</a></li>
            </ul>
            <h4>23 mei</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Gegevensmodel</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-top10nl-gegevensmodel#brt-top10nl-objectencatalogus-2-2-1"><abbr title="Basisregistratie Topografie">BRT</abbr> TOP10NL Objectencatalogus 2.2.1</a></li>
            </ul>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-4-11-vast-nummer-toestaan-en-beperkte-uitrol-digid-app"><abbr title="Digitale Identiteit">DigiD</abbr> 4.11 &quot;Vast nummer toestaan en beperkte uitrol <abbr title="Digitale Identiteit">DigiD</abbr> app&quot;</a></li>
            </ul>
            <h4>25 mei</h4>
            <p>MijnOverheid portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-portaal#release-2-5">Release 2.5</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2016</h3>
            <h4>1 juni</h4>
            <p>Landelijke voorziening <abbr title="Waarde Onroerende Zaken">WOZ</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/landelijke-voorziening-woz#abonnementservice-lv-woz">Abonnementservice <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
            </ul>
            <h4>21 juni</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-7-2-nabestaanden-machtiging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.7.2 &quot;Nabestaanden Machtiging&quot;</a></li>
            </ul>
            <h4>30 juni</h4>
            <p><abbr title="Basisregistratie Lonen Arbeidsverhoudingen en Uitkeringen">BLAU</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-lonen-arbeidsverhoudingen-en-uitkeringen-blau/' . $this->releasekalender_queryvar_product . '/blau-gegevensverzameling#blau-initiele-gegevensverzameling"><abbr title="Basisregistratie Lonen Arbeidsverhoudingen en Uitkeringen">BLAU</abbr> initiële gegevensverzameling</a></li>
            </ul>
            <p><abbr title="Basisregistratie Lonen Arbeidsverhoudingen en Uitkeringen">BLAU</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-lonen-arbeidsverhoudingen-en-uitkeringen-blau/' . $this->releasekalender_queryvar_product . '/blau-regelgeving#blau-initiele-wetgeving"><abbr title="Basisregistratie Lonen Arbeidsverhoudingen en Uitkeringen">BLAU</abbr> initiële wetgeving</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2016</h3>
            <h4>1 juli</h4>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Gegevensverzameling (bronhouders en <abbr title="Sociale Verzekeringsbank">SVB</abbr>-<abbr title="Basisregistratie grootschalige Topografie">BGT</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-gegevensverzameling-bronhouders-en-svb-bgt#bgt-gegevensverzameling-initiele-vulling"><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Gegevensverzameling initiële vulling</a></li>
            </ul>
            <p><abbr title="Basisregistratie grootschalige Topografie">BGT</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-grootschalige-topografie-bgt/' . $this->releasekalender_queryvar_product . '/bgt-regelgeving#ministeriele-regeling-toezicht-handhaving-en-kwaliteitsborging">Ministeriele regeling toezicht, handhaving en kwaliteitsborging</a></li>
            </ul>
            <p>Digikoppeling standaard Identificatie &amp; Authenticatie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-standaard-identificatie-authenticatie#digikoppeling-3-0-i-a-2-0">Digikoppeling 3.0: I&amp;A 2.0</a></li>
            </ul>
            <p>KIK-inzage (Ketenintegratie Inschrijving Kadaster)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/kik-inzage-ketenintegratie-inschrijving-kadaster#kik-inzage-5-0">KIK - inzage 5.0</a></li>
            </ul>
            <h4>6 juli</h4>
            <p>TOP100raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top100raster#top100raster-r6-2016">TOP100raster R6-2016</a></li>
            </ul>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r6-2016">TOP10NL R6-2016</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r6-2016">TOP25raster R6-2016</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r6-2016">TOP50NL R6-2016</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r6-2016">TOP50raster R6-2016</a></li>
            </ul>
            <h4>14 juli</h4>
            <p>Digilevering</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digilevering/' . $this->releasekalender_queryvar_product . '/digilevering#digilevering-3-2">Digilevering 3.2</a></li>
            </ul>
            <h4>24 juli</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> webservice via <abbr title="Publieke Dienstverlening op de Kaart">PDOK</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top100raster">TOP100raster</a></li>
            </ul>
            <h4>27 juli</h4>
            <p>MijnOverheid portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-portaal#r2-5-1">R2.5.1.</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2016</h3>
            <h4>1 augustus</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-8-0"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V release 8.0</a></li>
            </ul>
            <p>PIVA Verstrekkingen (PIVA-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-verstrekkingen-piva-v#piva-v-release-8-0">PIVA-V release 8.0</a></li>
            </ul>
            <h4>27 augustus</h4>
            <p>TOP250raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250raster#top250raster-r9-2016">TOP250raster R9-2016</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>September 2016</h3>
            <h4>1 september</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#afsprakenstelsel-release-1-10">Afsprakenstelsel release 1.10</a></li>
            </ul>
            <h4>15 september</h4>
            <p>TOP250raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250raster#top250raster-r11-2016">TOP250raster R11-2016</a></li>
            </ul>
            <h4>19 september</h4>
            <p>TOP100raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top100raster#top100raster-r9-2016">TOP100raster R9-2016</a></li>
            </ul>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r9-2016">TOP10NL R9-2016</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r9-2016">TOP25raster R9-2016</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r9-2016">TOP50NL R9-2016</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r9-2016">TOP50raster R9-2016</a></li>
            </ul>
            <h4>21 september</h4>
            <p>Nederlandse Taxonomie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/standard-business-reporting-sbr/' . $this->releasekalender_queryvar_product . '/nederlandse-taxonomie#nederlandse-taxonomie-10-2">Nederlandse Taxonomie 10.2</a></li>
            </ul>
            <h4>27 september</h4>
            <p>TOP100NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top100nl#top100nl-r9-2016">TOP100NL R9-2016</a></li>
            </ul>
            <p>TOP250NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250nl#top250nl-r9-2016">TOP250NL R9-2016</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Oktober 2016</h3>
            <h4>1 oktober</h4>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-gegevensverzameling#gegevensverzameling-br-woz-relatie-met-bag-gereed">Gegevensverzameling BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> - Relatie met <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> gereed</a></li>
            </ul>
            <p>Gemeentelijke <abbr title="Waarde Onroerende Zaken">WOZ</abbr>-applicaties</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/gemeentelijke-woz-applicaties#woz-applicaties-leveren-gegevens-aan-lv-woz"><abbr title="Waarde Onroerende Zaken">WOZ</abbr>-applicaties leveren gegevens aan <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr></a></li>
            </ul>
            <p>Landelijke voorziening <abbr title="Waarde Onroerende Zaken">WOZ</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/landelijke-voorziening-woz#lv-woz-1-0-gevuld"><abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Waarde Onroerende Zaken">WOZ</abbr> 1.0 gevuld</a></li>
            </ul>
            <h4>6 oktober</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-5-0-migratie-digid"><abbr title="Digitale Identiteit">DigiD</abbr> 5.0 Migratie <abbr title="Digitale Identiteit">DigiD</abbr></a></li>
            </ul>
            <h4>8 oktober</h4>
            <p>Beheervoorziening <abbr title="Burgerservicenummer">BSN</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/burgerservicenummer/' . $this->releasekalender_queryvar_product . '/beheervoorziening-bsn#bvbsn-release-6-3">BV<abbr title="Burgerservicenummer">BSN</abbr> release 6.3</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-logisch-ontwerp#gba-lo-3-10"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 3.10</a></li>
            </ul>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Verstrekkingen (<abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-verstrekkingen-gba-v#gba-v-release-8-1"><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr>-V release 8.1</a></li>
            </ul>
            <p>PIVA Logisch Ontwerp BES</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-logisch-ontwerp-bes#bes-lo-1-3">BES <abbr title="Logisch Ontwerp">LO</abbr> 1.3</a></li>
            </ul>
            <p>PIVA Verstrekkingen (PIVA-V)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/piva-verstrekkingen-piva-v#piva-v-release-8-1">PIVA-V release 8.1</a></li>
            </ul>
            <p>Registratie Niet-Ingezetenen (<abbr title="Registratie Niet Ingezetenen">RNI</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/registratie-niet-ingezetenen-rni#rni-3-23-3-24"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> 3.23/3.24</a></li>
            </ul>
            <p><abbr title="Registratie Niet Ingezetenen">RNI</abbr> Logisch Ontwerp</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/rni-logisch-ontwerp#rni-lo-2-12"><abbr title="Registratie Niet Ingezetenen">RNI</abbr> <abbr title="Logisch Ontwerp">LO</abbr> 2.12</a></li>
            </ul>
            <h4>15 oktober</h4>
            <p>Digikoppeling Compliancevoorziening</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digikoppeling/' . $this->releasekalender_queryvar_product . '/digikoppeling-compliancevoorziening#compliancevoorziening-3-0">Compliancevoorziening 3.0</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2016</h3>
            <h4>9 november</h4>
            <p>MijnOverheid portaal</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/mijnoverheid/' . $this->releasekalender_queryvar_product . '/mijnoverheid-portaal#release-3-0">Release 3.0</a></li>
            </ul>
            <h4>10 november</h4>
            <p>Digimelding 2.0</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digimelding/' . $this->releasekalender_queryvar_product . '/digimelding-2-0#digimelding-2-1">Digimelding 2.1</a></li>
            </ul>
            <h4>15 november</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-8-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.8 &quot;Beheer en beveiliging&quot;</a></li>
            </ul>
            <p>TOP100NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top100nl#top100nl-r11-2016">TOP100NL R11-2016</a></li>
            </ul>
            <p>TOP100raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top100raster#top100raster-r11-2016">TOP100raster R11-2016</a></li>
            </ul>
            <p>TOP10NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top10nl#top10nl-r11-2016">TOP10NL R11-2016</a></li>
            </ul>
            <p>TOP250NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top250nl#top250nl-r11-2016">TOP250NL R11-2016</a></li>
            </ul>
            <p>TOP25raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top25raster#top25raster-r11-2016">TOP25raster R11-2016</a></li>
            </ul>
            <p>TOP50NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50nl#top50nl-r11-2016">TOP50NL R11-2016</a></li>
            </ul>
            <p>TOP50raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top50raster#top50raster-r11-2016">TOP50raster R11-2016</a></li>
            </ul>
            <h4>25 november</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> webservice via <abbr title="Publieke Dienstverlening op de Kaart">PDOK</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top500raster">TOP500raster</a></li>
            </ul>
            <p>TOP500NL</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top500nl#top500nl-r11-2016">TOP500NL R11-2016</a></li>
            </ul>
            <p>TOP500raster</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/top500raster#top500raster-r11-2016">TOP500raster R11-2016</a></li>
            </ul>
          </div>
          <div class="unit visual">
            <h3>December 2016</h3>
            <h4>12 december</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-5-1-beheer-en-beveiliging"><abbr title="Digitale Identiteit">DigiD</abbr> 5.1 &quot;Beheer en beveiliging&quot;</a></li>
            </ul>
            <h4>14 december</h4>
            <p>Nederlandse Taxonomie</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/standard-business-reporting-sbr/' . $this->releasekalender_queryvar_product . '/nederlandse-taxonomie#nederlandse-taxonomie-11-0">Nederlandse Taxonomie 11.0</a></li>
            </ul>
            <h4>31 december</h4>
            <p>Berichtenbox voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#toevoegenmetadata">Toevoegenmetadata</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#aansluitvoorziening-via-digikoppeling">Aansluitvoorziening via Digikoppeling</a></li>
            </ul>
            <p><abbr title="Basisregistratie Ondergrond">BRO</abbr> algemeen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-algemeen#landelijke-voorziening-1-0">Landelijke Voorziening 1.0</a></li>
            </ul>
            <p><abbr title="Basisregistratie Ondergrond">BRO</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-gegevensverzameling#bro-gegevensverzameling"><abbr title="Basisregistratie Ondergrond">BRO</abbr> Gegevensverzameling</a></li>
            </ul>
            <p>Platform e-overheid voor bedrijven</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/platform-e-overheid-voor-bedrijven#uitfaseren-platform">Uitfaseren platform</a></li>
            </ul>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2017">2017</h2>
          <div class="unit">
            <h3>Januari 2017</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Kwaliteitsdashboard</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-kwaliteitsdashboard#beschikbaar-stellen-kwaliteitsdashboard-aan-afnemers">Beschikbaar stellen Kwaliteitsdashboard aan afnemers</a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Linked data</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-linked-data#ontsluiten-bag-als-linked-data">Ontsluiten <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> als Linked Data</a></li>
            </ul>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-gegevensverzameling#gegevensverzameling-br-woz-relatie-met-handelsregister">Gegevensverzameling BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> - Relatie met Handelsregister</a></li>
            </ul>
            <p><abbr title="Basisregistratie Ondergrond">BRO</abbr> Regelgeving</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-regelgeving#aanwijzing-registratieobjecten-en-bijbehorende-brondocumenten-bij-amvb">Aanwijzing registratieobjecten en bijbehorende brondocumenten bij AMvB</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-regelgeving#ministriele-regeling">Ministriële regeling</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-regelgeving#inwerkingtreding-wet-voor-bronhouders">Inwerkingtreding wet voor  bronhouders</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-regelgeving#wet-bro">Wet <abbr title="Basisregistratie Ondergrond">BRO</abbr></a></li>
            </ul>
            <p><abbr title="Basisregistratie Voertuigen">BRV</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-voertuigen-brv/' . $this->releasekalender_queryvar_product . '/brv-gegevensverzameling#kentekenregister-uitbreiding-met-voertuigsoorten-lbt-lbta-en-mm">Kentekenregister – uitbreiding met voertuigsoorten LBT, LBTA en MM</a></li>
            </ul>
            <p>Geocodeerservice</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/geocodeerservice#verbetering-geocodeerservice">Verbetering geocodeerservice</a></li>
            </ul>
            <p>Stelselcatalogus</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/stelselcatalogus/' . $this->releasekalender_queryvar_product . '/stelselcatalogus#stelselcatalogus-3-0">Stelselcatalogus 3.0</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2017</h3>
            <h4>13 februari</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-5-2"><abbr title="Digitale Identiteit">DigiD</abbr> 5.2</a></li>
            </ul>
            <h4>20 februari</h4>
            <p><abbr title="Basisregistratie Topografie">BRT</abbr> webservice via <abbr title="Publieke Dienstverlening op de Kaart">PDOK</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-topografie-brt/' . $this->releasekalender_queryvar_product . '/brt-webservice-via-pdok#top1000raster">TOP1000raster</a></li>
            </ul>
            <h4>21 februari</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid-machtigen#digid-machtigen-4-9-toegang-organisaties-via-eherkenning"><abbr title="Digitale Identiteit">DigiD</abbr> Machtigen 4.9 &quot;Toegang organisaties via <abbr title="Elektronische Herkenning">eHerkenning</abbr>&quot;</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2017</h3>
            <h4>1 maart</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#release-1-11">Release 1.11</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>April 2017</h3>
            <h4>1 april</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="TerugMeldVoorziening">TMV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-tmv#realisatie-generiek-terugmeldsysteem">Realisatie generiek terugmeldsysteem</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Mei 2017</h3>
            <h4>8 mei</h4>
            <p><abbr title="Digitale Identiteit">DigiD</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/digid/' . $this->releasekalender_queryvar_product . '/digid#digid-5-3"><abbr title="Digitale Identiteit">DigiD</abbr> 5.3</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Juni 2017</h3>
            <h4>1 juni</h4>
            <p><abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> Terugmeldvoorziening (<abbr title="TerugMeldVoorziening">TMV</abbr>)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/gba-terugmeldvoorziening-tmv#tmv-2-0"><abbr title="TerugMeldVoorziening">TMV</abbr> 2.0</a></li>
            </ul>
            <h4>30 juni</h4>
            <p><abbr title="Basisregistratie Personen">BRP</abbr> Burgerzakenmodule gemeenten</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-burgerzakenmodule-gemeenten#bz-gemeenten-1-0"><abbr title="Burgerzaken">BZ</abbr> gemeenten 1.0</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Juli 2017</h3>
            <h4>1 juli</h4>
            <p>BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> Gegevensverzameling</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-waarde-onroerende-zaken-woz/' . $this->releasekalender_queryvar_product . '/br-woz-gegevensverzameling#gegevensverzameling-br-woz-relatie-met-rni">Gegevensverzameling BR <abbr title="Waarde Onroerende Zaken">WOZ</abbr> - Relatie met <abbr title="Registratie Niet Ingezetenen">RNI</abbr></a></li>
            </ul>
            <p>IMKAD</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-kadaster-brk/' . $this->releasekalender_queryvar_product . '/imkad#imkad-2-2">IMKAD 2.2</a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Augustus 2017</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>September 2017</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Oktober 2017</h3>
            <h4>1 oktober</h4>
            <p>Afsprakenstelsel Elektronische Toegangsdiensten (<abbr title="Elektronische Herkenning">eHerkenning</abbr> en Idensys)</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/eherkenning/' . $this->releasekalender_queryvar_product . '/afsprakenstelsel-elektronische-toegangsdiensten-eherkenning-en-idensys#release-2-0">Release 2.0</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>November 2017</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>December 2017</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="back_to_top">
            <p><a href="#top">Naar boven</a></p>
          </div>
          <h2 id="j2018">2018</h2>
          <div class="unit">
            <h3>Januari 2018</h3>
            <h4>1 januari</h4>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> Koppelvlak bronhouders</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-koppelvlak-bronhouders#koppelvlakwijziging-lv-bag">Koppelvlakwijziging <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr></a></li>
            </ul>
            <p><abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr> <abbr title="Landelijke Voorziening">LV</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistraties-adressen-en-gebouwen-bag/' . $this->releasekalender_queryvar_product . '/bag-lv#koppelvlakwijziging-lv-bag">Koppelvlakwijziging <abbr title="Landelijke Voorziening">LV</abbr> <abbr title="Basisregistraties Adressen en Gebouwen">BAG</abbr></a></li>
            </ul>
          </div>
          <div class="unit">
            <h3>Februari 2018</h3>
            <p><em>Geen releases</em></p>
          </div>
        </div>
        <div class="row">
          <div class="unit">
            <h3>Maart 2018</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>April 2018</h3>
            <p><em>Geen releases</em></p>
          </div>
          <div class="unit">
            <h3>Mei 2018</h3>
            <h4>1 mei</h4>
            <p><abbr title="Basisregistratie Personen">BRP</abbr> centrale voorziening</p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-centrale-voorziening#brp-bevraging"><abbr title="Basisregistratie Personen">BRP</abbr> Bevraging</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-centrale-voorziening#brp-bijhouding"><abbr title="Basisregistratie Personen">BRP</abbr> Bijhouding</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-centrale-voorziening#brp-levering"><abbr title="Basisregistratie Personen">BRP</abbr> Levering</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-centrale-voorziening#brp-terugmelding"><abbr title="Basisregistratie Personen">BRP</abbr> Terugmelding</a></li>
            </ul>
            <p><abbr title="Basisregistratie Personen">BRP</abbr> Migratievoorziening <abbr title="Gemeentelijke Basisadministratie Personen">GBA</abbr> &lt;-&gt; <abbr title="Basisregistratie Personen">BRP</abbr></p>
            <ul>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-migratievoorziening-gba-brp#inter-stelsel-communicatie-isc">Inter Stelsel Communicatie (I<abbr title="Samenwerkende Catalogi">SC</abbr>)</a></li>
              <li><a href="' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-personen-brp-gba-rni/' . $this->releasekalender_queryvar_product . '/brp-migratievoorziening-gba-brp#gemeentelijke-gegevens-overdracht-ggo">Gemeentelijke Gegevens Overdracht (GGO)</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>';
    			
    		}
    		else {

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

$mailcontent = 'php bestand mist op de releasekalender: ' . $this->releasekalender_voorziening . '-' . get_query_var( $this->releasekalender_queryvar_product );

mail("vanbuuren@gmail.com", "[digitaleoverheid.nl] Releasekalender missend bestand: " . $this->releasekalender_voorziening . '-' . get_query_var( $this->releasekalender_queryvar_product ), $mailcontent, "From: paul@wbvb.nl");

      				
      				echo '<!-- error(51203a) ' . $this->releasekalender_voorziening . '-' . get_query_var( $this->releasekalender_queryvar_product ) . '-->';
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
		

		  add_rewrite_rule( '(.+?)/' . $this->releasekalender_queryvar_kalender . '/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_kalender . '=' . $this->releasekalender_queryvar_kalender, 'top');

		}

		//========================================================================================================
		
		function rijksreleasekalender_add_query_vars($vars) {
			$vars[] = $this->releasekalender_queryvar_voorziening;
			$vars[] = $this->releasekalender_queryvar_product;
			$vars[] = $this->releasekalender_queryvar_kalender;
			$vars[] = $this->releasekalender_queryvar_kalender;

			return $vars;
		}
		
		//========================================================================================================
		
		function rijksreleasekalender_sidebar_context_widgets() {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
  		
  		if ( $this->releasekalender_template_hoofdpagina == $page_template ) {
        $url = get_permalink( get_the_ID() );

        echo '<div id="releasekalender_kalender-widget_' . get_the_ID() . '" class="widget releasekalender releasekalender-kalender-widget"><div class="widget-wrap"><div class="text"><h3 class="widgettitle">' . __('Aankomende releases', '' ) . '</h3>';
        
        
        echo '<ul class="list">
         	<li>
        <h4><a href=' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#aansluitvoorziening-via-digikoppeling">Aansluitvoorziening via Digikoppeling</a></h4>
        Berichtenbox voor bedrijven
        <p class="details">31 december 2016</p>
        </li>
         	<li>
        <h4><a href=' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-gegevensverzameling#bro-gegevensverzameling"><abbr title="Basisregistratie Ondergrond">BRO</abbr> Gegevensverzameling</a></h4>
        <abbr title="Basisregistratie Ondergrond">BRO</abbr> Gegevensverzameling
        <p class="details">31 december 2016</p>
        </li>
         	<li>
        <h4><a href=' . $url . $this->releasekalender_queryvar_voorziening . '/basisregistratie-ondergrond-bro/' . $this->releasekalender_queryvar_product . '/bro-algemeen#landelijke-voorziening-1-0">Landelijke Voorziening 1.0</a></h4>
        <abbr title="Basisregistratie Ondergrond">BRO</abbr> algemeen
        <p class="details">31 december 2016</p>
        </li>
         	<li>
        <h4><a href=' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#toevoegenmetadata">Toevoegenmetadata</a></h4>
        Berichtenbox voor bedrijven
        <p class="details">31 december 2016</p>
        </li>
         	<li>
        <h4><a href=' . $url . $this->releasekalender_queryvar_voorziening . '/antwoord-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/platform-e-overheid-voor-bedrijven#uitfaseren-platform">Uitfaseren platform</a></h4>
        Platform e-overheid voor bedrijven
        <p class="details">31 december 2016</p>
        </li>
        </ul>';
        
        echo '<div class="category-link more"><a href="' . $url . $this->releasekalender_queryvar_kalender . '/">' . __( 'Volledige kalender','' ) . '</a></div>';
        
        
        echo '</div></div></div>';
        
  		}

		}
		
		//========================================================================================================
		
		function rijksreleasekalender_add_plain_html_kalender() {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
  		
  		if ( $this->releasekalender_template_hoofdpagina == $page_template ) {
//        echo '<div style="clear: both; float: none; border: 1px solid red;">HTML DOWNLOAD DINGES HIERO</div>';
  		}

		}


		
		//========================================================================================================
		

}
