<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wbvb.nl/
 * @since      1.0.5
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

	private $releasekalender_queryvar_voorziening;
	private $requestedvoorziening;
	private $releasekalender_queryvar_product;
	private $requestedproduct;
	private $releasekalender_queryvar_kalender;
	private $releasekalender_queryvar_plainhtml;
	private $option_name = 'rijksreleasekalender';
	private $feed_name;
	

	/**
	 * A string to preserve the original page title 
	 *
	 * @since    1.0.2
	 * @access   private
	 * @var      string    $version    page title
	 */
	private $TEMP_pagename_for_voorziening;
	private $TEMP_pagename_for_product;
	private $default_page_title_for_kalender;

	//==========================================================================================================
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

    $this->feed_name            = 'feedrijksreleasekalender';
    
		//========================================================================================================
		// set up 2 different virtual page templates
		// these are not actual page templates with their own .php file
		$this->releasekalender_template_dossier        = 'releasekalender-dossier-template.php';
		$this->releasekalender_template_hoofdpagina    = 'releasekalender-main-page-template.php';
		
		// add the page templates
		add_filter( 'theme_page_templates', array( $this, 'add_page_templates' ) );
		
    // activate the page filters
    add_action( 'template_redirect',    array( $this, 'use_page_template' )  );

		//========================================================================================================
    // initialize the query vars. Depending on the query vars we decide what to show
		$this->releasekalender_queryvar_voorziening       = 'voorziening';    // query var to recognize the voorziening / bouwsteen
		$this->releasekalender_queryvar_product           = 'product';        // query var to recognize the product
		$this->releasekalender_queryvar_kalender          = 'kalender';       // show the calendar overview of upcoming releases
		$this->releasekalender_queryvar_plainhtml         = 'plainhtml';      // show the complete data in plain HTML 

    // add rewrite rules and make WP recognize the query vars
		add_filter( 'init',				array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars',	array( $this, 'add_query_vars' ) );

		add_filter( 'init',				array( $this, 'add_acf_functions' ) );

    // custom RSS for overview of latest releases / voorzieningen / producten
		add_action( 'init',				array( $this, 'customRSS' ) );



	}

	//==========================================================================================================
	/**
  * Adds the custom post template for pages on WordPress 4.6 and older
	 *
	 * @since    1.0.0
   * @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
   * @return array Expanded array of page templates.
   */
  function add_page_templates( $post_templates ) {

    $post_templates[$this->releasekalender_template_dossier]      = __( 'Releasekalender - Dossierpagina ', 'rijksreleasekalender' );    
    $post_templates[$this->releasekalender_template_hoofdpagina]  = __( 'Releasekalender - Hoofdpagina', 'rijksreleasekalender' );    
    return $post_templates;
    
  }

	//==========================================================================================================
	/**
   * make sure the query vars are valid
	 *
   */
  function filter_queryvars(  ) {

    $this->requestedproduct     = '';    
    $this->requestedvoorziening = '';    

    if ( get_query_var( $this->releasekalender_queryvar_product ) ) {
    	$get_producten_args = array(
        'post_type'   => 'producten',
        'posts_per_page'  => '-1',
        'name'        => get_query_var( $this->releasekalender_queryvar_product ),
        'post_status' => 'publish',
    	);
      $productquery = new WP_Query( $get_producten_args );
      
      if ( $productquery->have_posts() ) {
        // valid apparently
        $this->requestedproduct     = get_query_var( $this->releasekalender_queryvar_product );    
      }
    }
    if ( get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
    	$get_voorzieningen_args = array(
        'post_type'   => 'voorzieningencpt',
        'posts_per_page'  => '-1',
        'name'        => get_query_var( $this->releasekalender_queryvar_voorziening ),
        'post_status' => 'publish',
    	);
      $voorzieningquery = new WP_Query( $get_voorzieningen_args );
      
      if ( $voorzieningquery->have_posts() ) {
        // valid apparently
        $this->requestedvoorziening     = get_query_var( $this->releasekalender_queryvar_voorziening );    
      }
    }

    if ( is_single() && ( 'releases' == get_post_type() ) ) {
      $this->requestedvoorziening   = get_post_meta( get_the_ID(), 'release_voorziening_real_id_slug', true );    
      $this->requestedproduct       = get_post_meta( get_the_ID(), 'release_product_real_id_slug', true ); 
    }
    elseif ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) {
      $this->requestedvoorziening   = $this->get_slug( get_the_permalink() );    
    }
    elseif ( is_single() && ( 'producten' == get_post_type() ) ) {
      $this->requestedvoorziening   = get_post_meta( get_the_ID(), 'product_voorziening_real_id_slug', true );    
      $this->requestedproduct       = $this->get_slug( get_the_permalink() );    
    }
    
    
  }

	//==========================================================================================================
  /**
  * Modify page content if using a specific page template.
  */
  public function use_page_template() {
    
    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

    // clean the query vars, only allow existing values
    $this->filter_queryvars();

    // set the alternative page names
    $this->set_names();

  	//=================================================
    if ( get_query_var( $this->releasekalender_queryvar_plainhtml ) == $this->releasekalender_queryvar_plainhtml ) {
      die($this->write_plainhtmloutput());
    }

  	//=================================================
    if( ( is_single() && 'releases' == get_post_type() ) || 
        ( is_single() && 'producten' == get_post_type() ) ||
        ( is_single() && 'voorzieningencpt' == get_post_type() ) ) {

      ob_clean();

      /* Redirect to a different page in the current directory that was requested */

  		$hoofdpagina = intval( get_option( $this->option_name . '_hoofdpagina' ) );
      if ( is_int( $hoofdpagina ) && $hoofdpagina > 0 ) {
      }
      else {
        $hoofdpagina = 73;
      }

      if ( is_single() && ( 'releases' == get_post_type() ) ) {
        $arguments = array(
          'currenturl'        => get_the_permalink( $hoofdpagina ),
          'product_slug'      => get_post_meta( get_the_id(), 'release_product_real_id_slug', true ),
          'voorziening_slug'  => get_post_meta( get_the_id(), 'release_voorziening_real_id_slug', true ),
          'inpage_id'         => $this->get_slug( get_the_permalink() ),
          'context'           => 'use_page_template'
        );
      } 
      elseif ( is_single() && ( 'producten' == get_post_type() ) ) {
        $arguments = array(
          'currenturl'        => get_the_permalink( $hoofdpagina ),
          'product_slug'      => $this->get_slug( get_the_permalink() ),
          'voorziening_slug'  => get_post_meta( get_the_id(), 'product_voorziening_real_id_slug', true ),
          'context'           => 'use_page_template'
        );
      } 
      elseif ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) {
        $arguments = array(
          'currenturl'        => get_the_permalink( $hoofdpagina ),
          'voorziening_slug'  => $this->get_slug( get_the_permalink() ),
          'context'           => 'use_page_template'
        );
      } 

      $url = $this->get_releaseurl( $arguments );
      header("Location: $url",TRUE,301);
      die();

    }

  	//=================================================
    if ( ( $this->releasekalender_template_hoofdpagina == $page_template ) ||
        ( $this->releasekalender_template_dossier == $page_template ) ||
        ( is_single() && ( 'releases' == get_post_type() ) ) ||
        ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) ||
        ( is_single() && ( 'producten' == get_post_type() ) ) ) {


      add_filter( 'genesis_post_info',   array( $this, 'filter_postinfo' ), 10, 2 );

      // dingen die voor beide templates gelden
        
      // check the breadcrumb
      add_filter( 'genesis_single_crumb',   array( $this, 'filter_breadcrumb' ), 10, 2 );
      add_filter( 'genesis_page_crumb',     array( $this, 'filter_breadcrumb' ), 10, 2 );
      add_filter( 'genesis_archive_crumb',  array( $this, 'filter_breadcrumb' ), 10, 2 ); 				

      // filter the <h1>
      add_filter( 'genesis_post_title_text', array( $this, 'filter_the_title' ), 16 );

    }


  	//=================================================
    if ( $this->releasekalender_template_hoofdpagina == $page_template ) {


      remove_filter( 'wp_title', 'genesis_default_title', 10, 3 );
      add_filter( 'wp_title', array( $this, 'filter_the_page_title' ), 10, 3 );

      if ( get_query_var( $this->releasekalender_queryvar_kalender ) == $this->releasekalender_queryvar_kalender ) {

        //* Force full-width-content layout
        add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
  
        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_hoofdpagina_kalender' ) );


      }
      elseif ( $this->requestedproduct &&  $this->requestedvoorziening ) {
        // we know the product and the voorziening


        //* Force full-width-content layout

        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_release_info' ) );
        
        
        // ADD DEBUG CONTENT
         if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'DEBUG_template_add_metadata_overview' ) );
        }

      }
      elseif ( $this->requestedvoorziening ) {
        // we know only the voorziening

        //* Force full-width-content layout
        add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_hoofdpagina_gantt_chart' ) );
        
        // ADD DEBUG CONTENT
         if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'DEBUG_template_add_metadata_overview' ) );
        }

        
      }
      elseif ( $this->requestedproduct ) {
        // we know only the product
        
        // filter the dossier template page
         if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'DEBUG_template_add_metadata_overview' ) );
        }
      }
      else {
        // plain layout. Write widget and the group overview
        
        // action for writing extra info in the alt-sidebar
        add_action( 'genesis_before_sidebar_widget_area',    array( $this, 'write_sidebar_context_widget' )  );
  
        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_hoofdpagina_groepen' ) );

        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_hoofdpagina_rss_and_plaintext' ) );

      }

    
    }  		
    elseif( ( is_single() && ( 'releases' == get_post_type() ) ) ||
            ( is_single() && ( 'producten' == get_post_type() ) ) || 
            ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) || 
            ( is_page() && ( $this->releasekalender_template_dossier == $page_template ) ) ) {

      if( get_field('releasekalender_voorziening') ) {
  			$this->requestedvoorziening = get_field('releasekalender_voorziening');
      }

      if ( $this->requestedproduct &&  $this->requestedvoorziening ) {

        // we know the product and the voorziening

        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_release_info' ) );

      }
      elseif ( $this->requestedvoorziening ) {
        // we know only the voorziening
       
        //* Force full-width-content layout
        add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
      
        // filter the main template page
        add_filter( 'the_content', array( $this, 'get_template_hoofdpagina_gantt_chart' ) );

      }      
      

      
      // filter the dossier template page
       if ( WP_DEBUG ) {
        add_filter( 'the_content', array( $this, 'DEBUG_template_add_metadata_overview' ) );
      }

      
      
    }
    
  }

	//==========================================================================================================
	/**
	 * Register the stylesheets for the public pages / overviews
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

    // check if we need to add the CSS or JS
    if ( ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) ||
        ( is_single() && ( 'releases' == get_post_type() ) ) ||
        ( is_single() && ( 'producten' == get_post_type() ) ) ||
        ( $this->releasekalender_template_hoofdpagina == $page_template ) ||
        ( $this->releasekalender_template_dossier == $page_template ) ) {
    
        wp_enqueue_style(   $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-main-page-template.css', array(), $this->version, 'all' );

    }

	}

	//==========================================================================================================
	/**
	 * Register the JavaScript for the public pages / overviews
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

    // check if we need to add the CSS or JS
    if ( ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) ||
        ( is_single() && ( 'releases' == get_post_type() ) ) ||
        ( is_single() && ( 'producten' == get_post_type() ) ) ||
        ( $this->releasekalender_template_hoofdpagina == $page_template ) ||
        ( $this->releasekalender_template_dossier == $page_template ) ) {

        // we do. Only on these pages, add the JS    
       wp_enqueue_script(  $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'js/min/releasekalender-min.js', array( 'jquery' ), $this->version, false );
    }
	}
  

	//==========================================================================================================
	/**
	 * Show the release info. We need to know the voorziening and product for this
	 *
	 * @param  string  $content  The page content
	 * @return string  $content  The modified page content
	 */
	public function get_template_release_info( $content ) {

    if ( WP_DEBUG ) {
      $content = 'get_template_release_info' . $content;
    }

    $args                     = array();
    $args['programma']        = '';
    $args['productslug']      = '';
    $args['voorzieningslug']  = '';
    $args['permalink']        = '';

    if ( $this->requestedproduct ) {
      $args['productslug']      = $this->requestedproduct;
    }
    if ( $this->requestedvoorziening ) {
      $args['voorzieningslug']  = $this->requestedvoorziening;
    }

    if ( $args['productslug'] ) {
    
    	$get_producten_args = array(
        'posts_per_page'  => '-1',
        'post_type'   => 'producten',
        'name'        => $args['productslug'],
        'post_status' => 'publish',
    	);
      $productquery = new WP_Query( $get_producten_args );
      
      if ( $productquery->have_posts() ) {
        while ($productquery->have_posts()) : 
          $productquery->the_post();
    
          $metadata     = get_post_meta( get_the_id() );    	
          $datestring   = date( 'Y-m-d H:i:s', strtotime( $metadata['product_datumIngebruikname'][0] ) );
          $datestring   = get_date_from_gmt( $datestring );
          $datestring   = strtotime( $datestring );
    
          $args['programma']  .= '<ul>'; // 18
          if ( $datestring ) {
            $args['programma']  .= '<li>' . __( 'Ingebruikname', 'rijksreleasekalender' ) . ': ' . date_i18n( get_option( 'date_format' ), $datestring ) . '</li>';
          }

          $argsforreleaseurl = array(
            'currenturl'        => get_the_permalink( intval( get_option( $this->option_name . '_hoofdpagina' ) ) ),
            'voorziening_id'    => $metadata['product_voorziening_id'][0],
            'voorziening_slug'  => $args['voorzieningslug'],
            'context'           => 'get_template_release_info'
          );
          
          
          
          // voorziening get_releaseurl
          $args['programma']  .= '<li>' . __( 'Voorziening', 'rijksreleasekalender' ) . ': <a href="' . $this->get_releaseurl($argsforreleaseurl) . '">' . get_the_title( $metadata['product_voorziening_real_id'][0] ) . '</a></li>';
    
          if ( $metadata['product_aanbieder'][0] ) {
    
            $data = maybe_unserialize( $metadata['product_aanbieder'][0] );
            
            if ( $data['website'] ) {
              $args['programma']  .= '<li>' . __( 'Aanbieder', 'rijksreleasekalender' ) . ': <a href="' . $data['website'] . '">' . $data['naam'] . '</a></li>';
            }            
            else {
              $args['programma']  .= '<li>' . __( 'Aanbieder', 'rijksreleasekalender' ) . ': ' . $data['naam'] . '</li>';
            }
    
          }
          if ( $metadata['product_doelgroep'][0] ) {
            $args['programma']  .= '<li>' . __( 'Doelgroep', 'rijksreleasekalender' ) . ': ' . $metadata['product_doelgroep'][0] . '</li>';
          }
          if ( $metadata['product_verwijzing'][0] ) {
            $args['programma']  .= '<li>' .   __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . $metadata['product_verwijzing'][0] . '">' . get_the_title( ) . '</a></li>';
          }
          $args['programma']  .= '</ul>';
          $args['programma']  .= get_the_content();
          
        endwhile;
      }
      wp_reset_postdata();        
    }

 		if ( $args['productslug'] && $args['voorzieningslug'] ) {

      $thepage            = $args['permalink'];
      $pagetype           = 'releases';
			$get_releases_args = array(
        'post_type'   => $pagetype,
        'order'       => 'ASC',					
        'posts_per_page'  => '-1',
        'orderby'     => 'meta_value',					
        'post_status' => 'publish',					
        'ignore_custom_sort'    => TRUE,
        'meta_key'    => 'release_releasedatum_translated',
        
        'meta_query' => array(
        	array(
        		'key'     => 'release_product_real_id_slug',
        		'value'   => $args['productslug'],
        		'compare' => '='
        	),
        	array(
        		'key'     => 'release_voorziening_real_id_slug',
        		'value'   => $args['voorzieningslug'],
        		'compare' => '='
        	)
        )				
			);
      $releases_query = new WP_Query( $get_releases_args );
      
      if ( $releases_query->have_posts() ) {

        $args['gerealiseerd'] = '';
        $args['vervallen']    = '';
        $args['gepland']      = '';
        
        while ($releases_query->have_posts()) : 

          $releases_query->the_post();
    
          $metadata       = get_post_meta( get_the_id() );    	
          $releasestatus  = maybe_unserialize( $metadata['release_release_status'][0] );

          $theurl = $thepage . $this->releasekalender_queryvar_voorziening . '/' . $args['voorzieningslug'] . '/' . $this->releasekalender_queryvar_product . '/' . $args['productslug'];
          $theid  = $releases_query->post->post_name;

          // diversify for release status
          if ( $releasestatus['naam'] == 'gerealiseerd' ) {
            $args['gerealiseerd']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
            $args['gerealiseerd']  .= '<ul><li>Gerealiseerd op <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</li>';
            if ( isset( $metadata['release_website'][0] ) ) {
              $args['gerealiseerd']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . $metadata['release_website'][0] . '">' . get_the_title( ) . '</a></li>';
            }
            $args['gerealiseerd']  .= '<li>' . __( 'Releasestatus', 'rijksreleasekalender' ) . ': ' . $releasestatus['naam'] . '</li>';
            $args['gerealiseerd']  .= '</ul>'; 
            $args['gerealiseerd']  .= get_the_content(); 
            $args['gerealiseerd']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ) ); 
            $args['gerealiseerd']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken en standaarden', 'rijksreleasekalender' ) ); 
          }
          elseif ( $releasestatus['naam'] == 'waarschuwing' ) {
          }
          elseif ( $releasestatus['naam'] == 'vervallen' ) {
              $args['vervallen']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
              $args['vervallen']  .= '<p>' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</p><ul>';
              if ( isset( $metadata['product_verwijzing'][0] ) ) {
                $args['vervallen']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_verwijzing'][0] ) . '">' . get_the_title( ) . '</a></li>';
              }
              $args['vervallen']  .= '<li>' . __( 'Releasestatus', 'rijksreleasekalender' ) . ': ' . $releasestatus['naam'] . '</li>';
              $args['vervallen']  .= '</ul>'; 
              $args['vervallen']  .= get_the_content(); 
              $args['vervallen']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ) ); 
              $args['vervallen']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken en standaarden', 'rijksreleasekalender' ) ); 
          }
          elseif ( ( $releasestatus['naam'] == 'gepland' ) || ( $releasestatus['naam'] == 'verwacht' ) ) {

              $release_release_marge  = maybe_unserialize( $metadata['release_release_marge'][0] );
              $marge                  = '';
              if ( $release_release_marge['label'] ) {
                $marge = ', ' . $release_release_marge['label'] . ' maanden marge';
              }

            
              $args['gepland']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
              $args['gepland']  .= '<p>' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . $marge .'</p><ul>';
              if ( isset( $metadata['product_verwijzing'][0] ) ) {
                $args['gepland']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_verwijzing'][0] ) . '">' . get_the_title( ) . '</a></li>';
              }
              if ( isset( $metadata['release_website'][0] ) ) {
                $args['gepland']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . $metadata['release_website'][0] . '">' . get_the_title( ) . '</a></li>';
              }
              $args['gepland']  .= '<li>' . __( 'Releasestatus', 'rijksreleasekalender' ) . ': ' . $releasestatus['naam'] . '</li>';
              $args['gepland']  .= '</ul>'; 
              $args['gepland']  .= get_the_content(); 
              $args['gepland']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ) ); 
              $args['gepland']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken en standaarden', 'rijksreleasekalender' ) ); 
          }  

        endwhile;

        // put it all together!

        // Gerealiseerde releases
        $args['programma'] .=  '<div class="block">';
        $args['programma'] .=  '<h2>' . __( 'Gerealiseerde releases', 'rijksreleasekalender' ) . '</h2>';
        if ( $args['gerealiseerd'] ) {
          $args['programma'] .=  $args['gerealiseerd'];
        }
        else {
          $args['programma'] .=  '<p>' . __( 'Er zijn geen gerealiseerde releases.', 'rijksreleasekalender' ) . '</p>';
        }
        $args['programma'] .=  '</div>';
        
        // Aankomende releases
        $args['programma'] .=  '<div class="block">';
        $args['programma'] .=  '<h2>' . __( 'Aankomende releases', 'rijksreleasekalender' ) . '</h2>';
        if ( $args['gepland'] ) {
          $args['programma'] .=  $args['gepland'];
        }
        else {
          $args['programma'] .=  '<p>' . __( 'Er zijn geen aankomende releases.', 'rijksreleasekalender' ) . '</p>';
        }
        $args['programma'] .=  '</div>';

        if ( $args['vervallen'] ) {
          $args['programma'] .=  '<div class="block">';
          $args['programma'] .=  '<h2>' . __( 'Vervallen releases', 'rijksreleasekalender' ) . '</h2>';
          $args['programma'] .=  $args['vervallen'];
          $args['programma'] .=  '</div>';
        }
        
      }
      wp_reset_postdata();        
		}
		
    return $args['programma'];
		
  }

	//==========================================================================================================
	/**
	 * standard formatting for afhankelijkheden / afspraken
	 *
	 * @param  array    $releaseafhankelijkheden  any possible afhankelijkheden / afspraken
	 * @param  string   $title                    Title above
	 * @param  boolean  $writetitle               Whether or not to write the title
	 * @return string   $return                   The HTML output for these afhankelijkheden / afspraken
	 */
	public function get_afhankelijkheden( $releaseafhankelijkheden = '', $title = 'Afhankelijkheden', $writetitle = true ) {

    $return  = ''; 

    if ( $writetitle ) {
      $return  = '<h4>' . $title . '</h4>'; 
    }
  	
  	if ( $releaseafhankelijkheden ) {
      $return  .= '<ul>'; 
      $releaseafhankelijkheden = maybe_unserialize( $releaseafhankelijkheden );

      foreach( $releaseafhankelijkheden as $key => $value ){        
        if ( is_array( $value ) ) {
          $return  .= '<li>' . $value['naam'] . '</li>'; 
      	}
    	}

      $return  .= '</ul>'; 
    	
  	}
  	else {
      if ( $writetitle ) {
        $return  .= '<p><em>Geen ' . strtolower( $title ) . '</em></p>'; 
    	}
  	}

    return $return; 
  }

	//==========================================================================================================
	/**
	 * Show the Gantt Chart. We need to know the voorziening for this
	 *
	 * @param  string  $content  The page content
	 * @return string  $content  The modified page content
	 */
	public function get_template_hoofdpagina_gantt_chart( $content ) {

    //* Force full-width-content layout
    add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

  	$content                = '';
    $url                    = get_permalink( get_the_ID() );
    $title                  = '';
    $omschrijving           = '';
    $voorziening_updated    = '';
    $voorzieningslug        = $this->requestedvoorziening;
  	

		if ( $voorzieningslug ) {
    	$get_producten_args = array(
        'post_type'   => 'voorzieningencpt',
        'post_status' => 'publish',					
        'name'        => $voorzieningslug,
        'posts_per_page'  => '-1',
        'post_status' => 'publish',
    	);
      $productquery = new WP_Query( $get_producten_args );
      
      if ( $productquery->have_posts() ) {
        while ($productquery->have_posts()) : 
          $productquery->the_post();
          $title                  = get_the_title();
          $omschrijving           = get_the_content();
          $voorziening_updated    = get_post_meta( get_the_ID(), 'voorziening_updated', true ); ;
        endwhile;
      }
      wp_reset_query();      

      $sortering = 'product_datumIngebruikname';
      //$sortering = 'product_id';

      // prepare to query productend with the voorzieningslug as selector
			$releases_query_args = array(
        'post_type'   => 'producten',
        'order'       => 'ASC',					
        'posts_per_page'  => '-1',
        'post_status' => 'publish',					
        'orderby'     => 'title',					
//        'ignore_custom_sort'    => TRUE,
        'meta_query' => array(
        	array(
        		'key'     => 'product_voorziening_real_id_slug',
        		'value'   => $voorzieningslug,
        		'compare' => '='
        	)
        )				
			);
    }
    else {
      // no $voorzieningslug
      // repare to query productend with the voorziening ID as selector
			$releases_query_args = array(
        'post_type'   => 'producten',
        'order'       => 'ASC',					
        'post_status' => 'publish',					
        'posts_per_page'  => '-1',
        'orderby'     => 'title',					
//        'ignore_custom_sort'    => TRUE,
        'meta_query' => array(
        	array(
        		'key'     => 'product_voorziening_real_id',
        		'value'   => get_the_ID(),
        		'compare' => '='
        	)
        )				
			);
    }
    // kick off the query
		$allproducts_query = new WP_Query( $releases_query_args );

		if ( $allproducts_query->have_posts() ) {
      // here be products
      $programma  = '<ul>';
      $programmaargs = array();        
      $programmaargs['year_start']            = ( date('Y') - 1 );
      $programmaargs['year_end']              = ( date('Y') + 1 );
      $programmaargs['programma']             = '';
      $programmaargs['permalink']             = $url;
      $programmaargs['global_last_update']    = strtotime( $voorziening_updated ); // this will be used to show a global last updated date

      // de pijlstok voor het heden
      $pijlstok   = '<div class="nu"><p data-datumnu="' . date( 'm/d/Y' ) . '">' . date_i18n( get_option( 'date_format' ) ) . '</p></div>';

      while ( $allproducts_query->have_posts() ) : 
        $allproducts_query->the_post();
        
        $product_datumIngebruikname = get_post_meta( get_the_ID(), 'product_datumIngebruikname', true );
        
        $product_updated            = get_post_meta( get_the_ID(), 'product_updated', true );

        if ( strtotime( $product_updated ) > $programmaargs['global_last_update'] ) {
          // Product update is recenter dan voorziening update;          
          $programmaargs['global_last_update']   = strtotime( $product_updated );

        } 
        else {
          // Voorziening update is recenter dan product update;          
        }       

        $productslug  = $this->get_slug( get_the_permalink( get_the_ID() ) );
        $theurl       = $url . $this->releasekalender_queryvar_voorziening . '/' . $voorzieningslug . '/' . $this->releasekalender_queryvar_product . '/' . $productslug . '/';

        $programma .= '<li> <a href="' . $theurl . '" data-product-datum-ingebruikname="' . $product_datumIngebruikname . '">' . get_the_title() . '</a>';          

        $programmaargs['programma']       = '';
        $programmaargs['voorzieningslug'] = $voorzieningslug;
        $programmaargs['productslug']     = $productslug;
        $programmaargs                    = $this->get_releases_for_voorziening_and_product( $programmaargs );

        $programma .= $programmaargs['programma'];          
        $programma .= '</li>';          
        
      endwhile;

      $programma  .= '</ul>';

      $legenda_kalender           = get_option( $this->option_name . '_legenda_kalender' );
      
      if ( ! $legenda_kalender ) {
        $legenda_kalender = '<ul class="legenda"><li class="vervallen"><span class="status">Vervallen = </span> Vervallen release</li><li class="gerealiseerd"><span class="status">Gerealiseerd = </span> Gerealiseerde release</li><li><span class="status">Gepland of Verwacht = </span>Een geplande of verwachte release</li><li class="waarschuwing"><span class="status">Waarschuwing = </span> Release met mogelijk probleem bij afhankelijkheid</li></ul>';
      }  

  		$tijdbalk = '<div class="tijdbalk">' . $this->get_tijdbalk( $programmaargs['year_start'], $programmaargs['year_end'] ) . '</div>';
      
      $content = '<div id="releasekalenderoutput">'; 
      $content .= '<div class="rk-bouwsteen">'; 
      $content .= '<p>' . $title . ' ' . __('heeft de volgende producten en releases:', 'rijksreleasekalender' ) . '<br>(<a href="#omschrijving">' . __('naar omschrijving', 'rijksreleasekalender' ) . '</a>) </p>';
      
      $content .= $legenda_kalender;
      
      $content .= $tijdbalk; 
      $content .= $pijlstok; 
  		$content .= '<div class="programma">' . $programma . '</div>'; 
      $content .= '<div id="kolom12" class="block"><h2 id="omschrijving">' . __('Omschrijving', 'rijksreleasekalender' ) . '</h2>';
      $content .= $omschrijving;
      $content .= '<p>' . __('Datum laatste wijziging', 'rijksreleasekalender' ) . ': ' . date_i18n( get_option( 'date_format' ), $programmaargs['global_last_update'] ) . '</p>';
  		$content .= '</div>'; 
  		$content .= '</div>'; 
  		$content .= '</div>'; 
  		
    }      

    wp_reset_query();      
    return $content;
  	
	}

	//==========================================================================================================
	/**
	 * Select all possible releases, order them by releasedatum and give full history of release back
	 *
	 * @param  none
	 * @return string  $content  The HTML for the kalender overview
	 */
	public function get_template_hoofdpagina_kalender(  ) {

    $url = get_permalink( get_the_ID() );

		$releases_args = array(
      'post_type'       => 'releases',
      'posts_per_page'  => '-1',
      'order'           => 'ASC',					
      'orderby'         => 'meta_value',					
      'post_status' => 'publish',					
      'ignore_custom_sort'    => TRUE,
      'meta_key'        => 'release_releasedatum_translated',
		);
    $releases_query = new WP_Query($releases_args);

    // prepare for the year list. Default next and previous year
    $year_start  = intval( date('Y') - 1 );
    $year_end    = intval( date('Y') + 1 );

    if ( $releases_query->have_posts() ) { 

      // store the releases in this array, by month
      $releases_by_month = array();

      while ($releases_query->have_posts()) :

        $releases_query->the_post(); 
        $release_id                       = get_the_id();          
        $releasedatum                     = get_post_meta( get_the_id(), 'release_releasedatum_translated', true );
        $release_year                     = date( 'Y', $releasedatum );
        $release_month                    = date( 'm', $releasedatum );
        $release_product_real_id          = get_post_meta( get_the_id(), 'release_product_real_id', true );
        $release_voorziening_id           = get_post_meta( get_the_id(), 'release_voorziening_real_id', true );
        $release_product_real_id_slug     = get_post_meta( get_the_id(), 'release_product_real_id_slug', true );
        $release_voorziening_real_id_slug = get_post_meta( get_the_id(), 'release_voorziening_real_id_slug', true );

        // check if we need to expand the year list
        if ( intval( $release_year ) < intval( $year_start ) ) {
          $year_start = intval( $release_year );
        }
        if ( intval( $release_year ) > intval( $year_end ) ) {
          $year_end = intval( $release_year );
        }

        $showdate       = strtotime( $release_year . '-' . $release_month . '-1' );
        $maandsleutel   = 'maandsleutel_' . date_i18n( "d-F-Y", $showdate );
        $dagsleutel     = $releasedatum;
//        $keystring    = 'maandsleutel_' . date_i18n( "d-F-Y", $releasedatum );

        $argsforreleaseurl = array(
          'currenturl'        => $url,
          'release_id'        => $release_id,
          'voorziening_id'    => $release_voorziening_id,
          'product_id'        => $release_product_real_id,
          'product_slug'      => $release_product_real_id_slug,
          'voorziening_slug'  => $release_voorziening_real_id_slug,
          'inpage_id'         => $this->get_slug( get_the_permalink() ),
          'context'           => 'get_template_hoofdpagina_kalender'
        );

        $title                = get_the_title( $release_product_real_id );
        $titlekey             = strtoupper($title);
        
        $releases_by_month[$maandsleutel][$dagsleutel]['sortkeys'][$titlekey]         = $titlekey;
        $releases_by_month[$maandsleutel][$dagsleutel][$titlekey]['titel']            = $title;
        $releases_by_month[$maandsleutel][$dagsleutel][$titlekey]['datum']            = $releasedatum;
        $releases_by_month[$maandsleutel][$dagsleutel][$titlekey]['datum_translated'] = date_i18n( get_option( 'date_format' ) . " " . get_option( 'time_format' ), $releasedatum );
        $releases_by_month[$maandsleutel][$dagsleutel][$titlekey]['listitems'][]      = '<a href="' . $this->get_releaseurl($argsforreleaseurl) . '">' . get_the_title() . '</a>';  
        
        

      endwhile;
      
    }
    // Reset things, for good measure
    $releases_query = null;
    wp_reset_postdata();
    //==

    $tijdbalk = '';
    $dejaren  = '';
    $rowcounter = 0;

    if ( is_int( $year_start ) && is_int( $year_end ) &&  ( $year_start < $year_end ) ) {
      $tijdbalk = '<div class="kalender"><div class="months">';
      $dejaren  = '<div id="skipjaar" class="hide">';
      for ($currentyear = $year_start; $currentyear <= $year_end; $currentyear++) {
        $theid = 'j' . $currentyear;

        $currentyearstring = '<div class="back_to_top"><p><a href="#top">' . __( "Naar boven", 'rijksreleasekalender' ) . '</a></p></div><h2 id="' . $theid . '">' . $currentyear . '</h2>';
        
        // append to years collection
        $dejaren  .= '<a href="#' . $theid . '">' . $currentyear . '</a> ';

        for ($maand = 1; $maand <= 12; $maand++) {

          if ( $rowcounter >= 4 ) {
            $rowcounter = 0;
          }
          $rowcounter++;

          if ( $rowcounter == 1 ) {
            $tijdbalk .= '<div class="row">' . $currentyearstring;
            $totop = '';
            $currentyearstring = '';
          }

          $showdate     = strtotime( $currentyear . '-' . $maand . '-1' );
          $keystring    = date_i18n( "d-F-Y", $showdate );
          $maandsleutel = 'maandsleutel_' . date_i18n( "d-F-Y", $showdate );

          $datastring = '<p><em>' . __( "Geen releases", 'rijksreleasekalender' ) . '</em></p>';
          
          if ( isset( $releases_by_month[$maandsleutel] ) ) {
            // voor deze maand/jaar-combinatie zijn er 1 of meer releases

            $datastring   = ''; // effe leegmaken, zodat we geen onzin tonen
            $currentdate  = '';
            $previousdate = '';

            // sortering op datum ASC
            if ( count( $releases_by_month[$maandsleutel] ) > 1 ) {
              ksort( $releases_by_month[$maandsleutel] );
            }

            foreach ( $releases_by_month[$maandsleutel] as $member_group_term => $producten_per_datum ) {
              // we lopen nu alle dagen af

              // sortering op productnaam, alfabetisch ASC
              $sortkeys = $producten_per_datum['sortkeys'];
              natcasesort( $sortkeys );

              // schrijf de dag
              $datastring .= '<h4>' . date_i18n( "j F", $member_group_term ) . '</h4>';


              foreach ( $sortkeys as $sortedkey ) {

                $datastring .= '<p>' . $producten_per_datum[$sortedkey]['titel'] . '</p><ul>';

                // binnen dit product, sortering op release 
                if ( count( $producten_per_datum[$sortedkey]['listitems'] ) > 1 ) {
                  asort( $producten_per_datum[$sortedkey]['listitems'] );
                }
  
                foreach ( $producten_per_datum[$sortedkey]['listitems'] as $listitem ) {
                  $datastring .= '<li>' . $listitem . '</li>';
                }
                $datastring .= '</ul>';

              }

              $previousdate = $currentdate;

            }
          }

          $markering = '';
          if ( date_i18n( "F Y" ) == date_i18n( "F Y", $showdate ) ) {
            $markering = ' visual';
          }

          $tijdbalk .= '<div class="unit' . $markering . '">
          <h3>' . date_i18n( "F Y", $showdate ) . '</h3>
          ' . $datastring . '
          </div>';

          if ( $rowcounter == 4 ) {
            $tijdbalk .= '</div>';
          }
        }
      }
      $dejaren  .= '</div>';
      $tijdbalk .= '</div></div>';
    }

  	return '<div id="releasekalenderoutput"><div class="rk-kalender">' . $dejaren . $tijdbalk  . '</div></div>';

	}

	//========================================================================================================
	/**
	 * Adds extra links for the RSS feed and the plain text table view
	 *
	 * @param  string  $content  The page content
	 * @return string  $content  The modified page content
	 */
  public function get_template_hoofdpagina_rss_and_plaintext( $content ) {

    $url = get_permalink( get_the_ID() );

		$rss_beschikbaar = ( get_option( $this->option_name . '_rss_beschikbaar' ) ? get_option( $this->option_name . '_rss_beschikbaar' ) : 'nee' );
		$rsshtml = '';
		
		if ( $rss_beschikbaar == 'nee' ) {
		}
		else {
  		$rsshtml = '<a href="/feed/' . $this->feed_name . '/">' . __( 'RSS Recente wijzigingen', 'rijksreleasekalender' ) . '</a></li>';
		}
    
    return $content . '<div class="block"><h2>' . __( "Extra's", 'rijksreleasekalender' ) . '</h2><ul class="links"><li class="rss">' . $rsshtml . '<li><a href="' . $url . $this->releasekalender_queryvar_plainhtml . '/">' . __( 'Releasekalender als herbruikbare tabel', 'rijksreleasekalender' ) . '</a></li></ul></div>';  
	}

	//========================================================================================================
	/**
	 * Filter for the dossier page template
	 *
	 * @param  string  $content  The page content
	 * @return string  $content  The modified page content
	 */
  public function get_template_hoofdpagina_groepen( $content ) {
    
    $tempcontent = $content;
    
    $url = get_permalink( get_the_ID() );

    $groepargs  = array(
      'taxonomy'    => 'voorziening-groep',
      'order'       => 'ASC',
      'orderby'     => 'slug',
      'ignore_custom_sort'    => TRUE,
      'hide_empty'  => true
    ); 
    
    $member_group_terms = get_terms( $groepargs );

    $tempterms      = '';
    
    foreach ( $member_group_terms as $member_group_term ) {
      
      $geengegevensgevonden = true;      
      
      $titletag     = 'h2';
      $blockclass   = 'block';
      if ( $member_group_term->parent > 0 ) {
        $titletag     = 'h3';
        $blockclass   = 'block no-top';
      }
      
      
      $voorzieningen_query = new WP_Query( array(
      'post_type'         => 'voorzieningencpt',
      'order'             => 'ASC',
      'orderby'           => 'title',
      'post_status' => 'publish',					
      'ignore_custom_sort'    => TRUE,
      'posts_per_page'    => '-1',
      'tax_query'         => array(
          array(
            'taxonomy'    => 'voorziening-groep',
            'field'       => 'slug',
            'include_children'  => false,
            'terms'       => array( $member_group_term->slug ),
            'operator'    => 'IN'
          ) ) ) );

      $tempterms .= '<' . $titletag . '>' . $member_group_term->name . '</' . $titletag . '><p>Geen voorzieningen gevonden</p>';

      
      if ( $voorzieningen_query->have_posts() ) { 
        
        $geengegevensgevonden = false;
        
        $content .= '<div class="' . $blockclass . '">';
        $content .= '<' . $titletag . '>' . $member_group_term->name . '</' . $titletag . '>';
        $content .= '<ul class="links">';
        
        while ( $voorzieningen_query->have_posts() ) : 
          $voorzieningen_query->the_post(); 
          $theslug    = $this->get_slug( get_the_permalink( get_the_ID() ) );
          $posturl    = $url . $this->releasekalender_queryvar_voorziening . '/' . $theslug . '/';
          

          $arguments = array(
            'currenturl'        => $url,
            'voorziening_id'    => get_the_ID(),
            'voorziening_slug'  => $this->get_slug( get_the_permalink( get_the_ID() ) ),
            'context'           => 'get_template_hoofdpagina_groepen'
          );

          $extra = '';
          
          if ( function_exists( 'get_field' ) ) {
            // ACF
            $planningspagina    = get_field('releasekalender_link_voorziening_aan_planningspagina', get_the_ID() );
            if ( $planningspagina ) {
              $arguments['currenturl'] = get_the_permalink( $planningspagina[0] );
            }
          }


          $content .= '<li><a href="' . $this->get_releaseurl( $arguments ) . '">' . get_the_title() . $extra . '</a></li>';
        endwhile; 
        
        $content .= '</ul>';
        $content .= '</div>';
      
      }
      else {
        
      } 

      // Reset things, for good measure
      $voorzieningen_query = null;
      wp_reset_postdata();
    }  

    if ( $geengegevensgevonden ) {
      return $tempcontent .= $tempterms;
    } 
    else {
      return $content;
    }     
    
  }

	//========================================================================================================
  /**
  * loop for the tijdbalk, goes from start year to end year
  *
  * @param  string  $year_start, $year_end, both years
  * @return string  $content  the tijdbalk
  */
  function get_tijdbalk( $year_start, $year_end ) {

    $year_start = intval($year_start);
    $year_end   = intval($year_end);
        
    $tijdbalk = '';
    if ( is_int( $year_start ) && is_int( $year_end ) &&  ( $year_start < $year_end ) ) {
      $tijdbalk = '<ul>';
      for ($i = $year_start; $i <= $year_end; $i++) {
        $tijdbalk .= '<li>' . $i . ' <ul> <li>Q1</li> <li>Q2</li> <li>Q3</li> <li>Q4</li> </ul></li>';
      }
      $tijdbalk .= '</ul>';
    }
    else {
      // DEBUG
//      $tijdbalk = 'Onvolledige gegevens: start: "' . $year_start . '", end: "' . $year_end . '"';
    }
    return $tijdbalk;
  }

	//========================================================================================================
  /**
  * for single posts of the correct kind and type: NO post info
  *
  * @param  string  $post_info
  * @return string  $post_info
  */
  function filter_postinfo($post_info) {
    global $wp_query;
    global $post;
    
    if ( ( is_single() && ( 'producten' == get_post_type() ) ) ||
          ( is_single() && ( 'releases' == get_post_type() ) ) ||
          ( is_single() && ( 'voorzieningencpt' == get_post_type() ) ) ) {
      return '';
    }
    else {
      return $post_info;
    }
  }

	//========================================================================================================
  /**
  * modify the breadcrumb
  *
  * @param  string  $crumb the existing crumb
  * @param  array   $args
  * @return string  $crumb the modified crumb
  */
  public function filter_breadcrumb( $crumb, $args ) {
    
    global $post;
    
    $page_template  = get_post_meta( get_the_ID(), '_wp_page_template', true );
    $thelink        = get_permalink( get_the_id() ) . $this->releasekalender_queryvar_voorziening . '/' . $this->requestedvoorziening . '/';
    
    // DEBUG
    $nieuwetitle    = get_the_title( get_the_id() );
    
    if ( ( $this->releasekalender_template_dossier == $page_template ) || ( $this->releasekalender_template_hoofdpagina == $page_template ) ) {
      $span_before_start  = '<span class="breadcrumb-link-wrap" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';  
      $span_between_start = '<span itemprop="name">';  
      $span_before_end    = '</span>';  
      if ( $this->releasekalender_template_dossier == $page_template ) {
        if ( $this->requestedproduct ) {
          $replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
          $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
          $crumb .= $args['sep'] . $this->TEMP_pagename_for_product;
        }
      }	
      else {
        if ( $this->requestedproduct && $this->requestedvoorziening ) {
          $replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
          $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
          $crumb .= $args['sep'] . '<a href="' . $thelink . '">' . $this->TEMP_pagename_for_voorziening .'</a>';
          $crumb .= $args['sep'] . $this->TEMP_pagename_for_product;
        }
        elseif ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {
          $replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
          $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
          $crumb .= $args['sep'] . $this->default_page_title_for_kalender;
        }
        elseif ( $this->requestedvoorziening ) {
          $replacer = '<a href="' . get_permalink( get_the_id() ) . '">' . $nieuwetitle .'</a>';
          $crumb = str_replace( $nieuwetitle, $replacer, $crumb);
          $crumb .= $args['sep'] . $this->TEMP_pagename_for_voorziening;
        }
      }
    }
    
    return $crumb;
    
  }

	//========================================================================================================
	/**
	 * Adds an extra widget display the upcoming releases
	 *
	 * @param  none
	 * @return  none
	 * this function will echo its content
	 */
  function write_sidebar_context_widget() {
    
    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
    
    if ( $this->releasekalender_template_hoofdpagina == $page_template ) {
      
      $max_items_in_widget    = intval( get_option( $this->option_name . '_max_items_in_widget' ) );
  		$widget_beschikbaar     = ( get_option( $this->option_name . '_widget_beschikbaar' ) ? get_option( $this->option_name . '_widget_beschikbaar' ) : 'ja' );
  		$rsshtml = '';
  		
  		if ( $widget_beschikbaar == 'nee' ) {
    		return;
  		}
      
      // set the max number of days to look ahead under
      // [admin] > 'Rijksreleasekalender' > 'Instellingen' > 'Widget: toon releases van de komende
      if ( is_int( $max_items_in_widget ) && $max_items_in_widget > 0 ) {
      }
      else {
        $max_items_in_widget = 10;
      }
      
      $url = get_permalink( get_the_ID() );
      
      // start day is today
      $start  = strtotime( date('y:m:d') );


        // Select the upcoming x releases
      $releases_query_args = array(
        'post_type'       => 'releases',
        'order'           => 'ASC',					
        'orderby'             => 'meta_value',					
        'ignore_custom_sort'  => TRUE,
        'posts_per_page'      => ( $max_items_in_widget + 5 ),
        'meta_key'        => 'release_releasedatum_translated',
        'meta_query'      => array(
                        array(
                          'key' => 'release_releasedatum_translated',
                          'value' => $start,
                          'compare' => '>='
                        )
        )				
      );
      
      $releases_query = new WP_Query( $releases_query_args );
      
      echo '<div id="releasekalender_kalender-widget_' . get_the_ID() . '" class="widget releasekalender releasekalender-kalender-widget">
      <div class="widget-wrap">
        <div class="text">
          <h3 class="widgettitle">' . __('Aankomende releases', 'rijksreleasekalender' ) . '</h3>';
      
      if ( $releases_query->have_posts() ) {

        $aankomendereleases = array();

        while ($releases_query->have_posts()) : 
          $releases_query->the_post();

          $theID = get_the_id();
        
          $release_voorziening_slug   = get_post_meta( $theID, 'release_voorziening_real_id_slug', true );
          $release_product_slug       = get_post_meta( $theID, 'release_product_real_id_slug', true );
          $release_product_name       = maybe_unserialize( get_post_meta( $theID, 'release_product', true ) );
          $release_product            = $this->releasekalender_queryvar_product . '/' . $release_product_slug . '/';
          $release_voorziening        = $this->releasekalender_queryvar_voorziening . '/' . $release_voorziening_slug . '/';
          $releasestatus              = maybe_unserialize( get_post_meta( $theID, 'release_release_status', true ) );
  
          $releasedatum               = get_post_meta( $theID, 'release_releasedatum_translated' );
          $releasedatum               = date_i18n( get_option( 'date_format' ), $releasedatum[0] );
          
          $posturl                    = $url . $release_voorziening . $release_product;
        
          $datumstring = '';
          
          // array maken met als key: datum en als value een array met als key de naam en value de bijbehorende 
          // HTML. We gaan deze array later sorteren op value.
          $huidigearray = array();

          $separator = '__/|\__';

          $arguments = array(
            'currenturl'        => $url,
            'voorziening_id'    => $theID,
            'product_slug'      => $release_product_slug,
            'voorziening_slug'  => $release_voorziening_slug,
            'inpage_id'         => $this->get_slug( get_the_permalink() ),
            'context'           => 'write_sidebar_context_widget'
          );

          $valuehtml = '<li><h4><a href="' . $this->get_releaseurl( $arguments ) . '" data-releasestatus="' . $releasestatus['naam'] . '">';
          $valuehtml .= get_the_title();
          $valuehtml .= '</a></h4>';
          $valuehtml .= '<p>' . $release_product_name['naam'] . '</p>'; 
          $valuehtml .= '<p class="details">' . $releasedatum . '</p>'; 
          $valuehtml .= '</li>';

          $value      = get_the_title() . $separator . $valuehtml;

          $key        = get_post_meta( $theID, 'release_releasedatum_translated' )[0]; 
          $huidigearray[$release_product_name['naam'] . '_' . $theID ] = $value;
          $aankomendereleases[$key][$release_product_name['naam'] . '_' . $theID ] = $value;

        endwhile;


        ksort($aankomendereleases);

        $vorigedatum = '';
        $tellertje = 0;
        
        echo '<ul>';
        foreach ($aankomendereleases as $key => $subarray) {


          // Subarray sorteren naar een logische volgorde. 
          // Zie ik iets over het hoofd voor het sorteren van keys? Want values kan ik wel logisch sorteren
          natcasesort($subarray);
          foreach ($subarray as $subkey => $subvalue) {
            // ik vermoed dat we binnenkort willen groeperen op datum, dus vandaar deze nu ongebruikte code
            $datumstring    = '';
            $tellertje ++;
//            $releasedatum   = date_i18n( get_option( 'date_format' ), $key );
//            if ( $vorigedatum != $releasedatum ) {
//              $datumstring = '<h3><strong style="background: black; color: white;">' . $releasedatum . '</strong></h3>';
//              $vorigedatum = $releasedatum;
//            }
            $htmloutput = explode($separator, $subvalue);

            if ( $tellertje > $max_items_in_widget ) {
              break;
            }
            
            echo '<li>' . $datumstring . '' . $htmloutput[1] . '</li>';
          }
        }
        echo '</ul>';

      }
      else {
      
        echo '<p>' . __( 'Geen releases gevonden.', 'rijksreleasekalender' ) . '</p>';
      
      }


      wp_reset_postdata();

      echo '<div class="category-link more"><a href="' . $url . $this->releasekalender_queryvar_kalender . '/">' . __( 'Bekijk de kalender','' ) . '</a></div>';
      
      
      echo '
      </div>
      </div>
      </div>';
      
    }
  }

  
		
	//========================================================================================================
	/**
	 *
	 * @param  none
	 * @return  none
	 * this function will modify a voorzieningen list
	 */
  function add_acf_functions() {


    $vars = array();

		$voorzieningen_args = array(
      'post_type'       => 'voorzieningencpt',
      'posts_status'    => 'publish',
      'posts_per_page'  => '-1',
      'order'           => 'ASC',					
      'ignore_custom_sort'    => TRUE,
      'orderby'         => 'title',
		);
    $voorzieningen_query = new WP_Query($voorzieningen_args);

    if ( $voorzieningen_query->have_posts() ) { 
      while ($voorzieningen_query->have_posts()) :
        $voorzieningen_query->the_post(); 
        $vars[ $this->get_slug( get_the_permalink() ) ] = get_the_title();
      endwhile;
    }
    // Reset things, for good measure
    $voorzieningen_query = null;
    wp_reset_postdata();
    //==


    if( function_exists('acf_add_local_field_group') ):
    
      acf_add_local_field_group(array (
      	'key' => 'group_58500d27b83da',
      	'title' => 'Kies bijbehorende voorzieningen (releasekalender)',
      	'fields' => array (
      		array (
      			'layout' => 'vertical',
      			'choices' => $vars,
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
    
      if ( defined( 'RHSWP_CT_DOSSIER' ) ) {
      
        $dossiers = array();
      
        $terms = get_terms( array(
            'taxonomy'    => RHSWP_CT_DOSSIER,
            'hide_empty'  => true,
        ) );
      
        if ($terms && ! is_wp_error( $dossiers ) ) {       
          foreach ( $dossiers as $term ) {
            $dossiers[] = RHSWP_CT_DOSSIER . ':' . $term->slug;
          }
        }
      
      
        acf_add_local_field_group(array (
        	'key' => 'group_586d656ad468b',
        	'title' => 'Link met dossier-planningspagina',
        	'fields' => array (
        		array (
        			'post_type' => array (
        				0 => 'page',
        			),
        			'taxonomy' => $dossiers,
        			'min' => 0,
        			'max' => 1,
        			'filters' => array (
        				0 => 'search',
        				1 => 'taxonomy',
        			),
        			'elements' => '',
        			'return_format' => 'id',
        			'key' => 'field_586d658b4da11',
        			'label' => 'Selecteer de pagina met planningsinformatie',
        			'name' => 'releasekalender_link_voorziening_aan_planningspagina',
        			'type' => 'relationship',
        			'instructions' => 'Selecteer een pagina met planningsinformatie uit een dossier. Maximaal 1 pagina.',
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
        				'param' => 'post_type',
        				'operator' => '==',
        				'value' => 'voorzieningencpt',
        			),
        		),
        	),
        	'menu_order' => 0,
        	'position' => 'normal',
        	'style' => 'default',
        	'label_placement' => 'top',
        	'instruction_placement' => 'label',
        	'hide_on_screen' => '',
        	'active' => 1,
        	'description' => '',
        ));
      
      }
    
    endif;

    
  }
    
		
	//========================================================================================================
	/**
	 * Adds rewrite rules
	 *
	 * @param  none
	 * @return  none
	 * this function will modify the rewrite rules
	 */
  function add_rewrite_rules() {
    
    // rewrite rules for posts in dossier context
    add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)(/' . $this->releasekalender_queryvar_product . '/)(.+?)/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]&' . $this->releasekalender_queryvar_product . '=$matches[5]', 'top');
    add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]', 'top');
    
    add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)(/' . $this->releasekalender_queryvar_product . '/)(.+?)?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]&' . $this->releasekalender_queryvar_product . '=$matches[5]', 'top');
    add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]', 'top');
    
    
    add_rewrite_rule( '(.+?)/' . $this->releasekalender_queryvar_kalender . '/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_kalender . '=' . $this->releasekalender_queryvar_kalender, 'top');
    
    add_rewrite_rule( '(.+?)/' . $this->releasekalender_queryvar_plainhtml . '/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_plainhtml . '=' . $this->releasekalender_queryvar_plainhtml, 'top');
    
  }

	//========================================================================================================
	/**
	 * Make WordPress listen to these query variables in pretty permalinks
	 *
	 * @param   $vars - the existing collection of query variables
	 * @return  $vars - the new collection of query variables
	 */
  function add_query_vars($vars) {
    
    $vars[] = $this->releasekalender_queryvar_voorziening;
    $vars[] = $this->releasekalender_queryvar_product;
    $vars[] = $this->releasekalender_queryvar_kalender;
    $vars[] = $this->releasekalender_queryvar_plainhtml;
    
    return $vars;
  }


	//========================================================================================================
	/**
	 * Retrieves a list of releases with a voorziening and product as arguments
	 *
	 * @param   array $args - arguments
	 * @return  array $args - modified arguments
	 */
	function get_releases_for_voorziening_and_product( $args ) {

		if ( $args['productslug'] && $args['voorzieningslug'] ) {
      $thepage            = $args['permalink'];
      $pagetype           = 'releases';
			$get_releases_args = array(
        'post_type'   => $pagetype,
        'order'       => 'ASC',					
        'orderby'     => 'meta_value',					
        'post_status' => 'publish',					
        'ignore_custom_sort'    => TRUE,
        'posts_per_page'  => '-1',
        'meta_key'    => 'release_releasedatum_translated',
        'meta_query' => array(
        	array(
        		'key'     => 'release_product_real_id_slug',
        		'value'   => $args['productslug'],
        		'compare' => '='
        	),
        	array(
        		'key'     => 'release_voorziening_real_id_slug',
        		'value'   => $args['voorzieningslug'],
        		'compare' => '='
        	)
        )				
			);
      $releases_query = new WP_Query( $get_releases_args );
    
      if ( $releases_query->have_posts() ) {
        $args['programma']  = '<ul>';

        while ($releases_query->have_posts()) : 
          $releases_query->the_post();
          $metadata         = get_post_meta( get_the_id() );    	
          $releasestatus    = maybe_unserialize( $metadata['release_release_status'][0] );
          $release_year     = date( 'Y', $metadata['release_releasedatum_translated'][0] );
          $release_updated  = $metadata['release_updated_translated'][0];


          if ( $release_updated > $args['global_last_update'] ) {
            // Release update is recenter dan voorziening update          
            $args['global_last_update']   = $release_updated;
          } 
          else {
            // Voorziening update is recenter dan release update          
          }       


          if ( intval( $release_year ) < intval( $args['year_start'] ) ) {
            $args['year_start'] = ( $release_year - 1 );
          }
          if ( intval( $release_year ) > intval( $args['year_end'] ) ) {
            $args['year_end'] = $release_year;
          }
  
          $theurl = $thepage . $this->releasekalender_queryvar_voorziening . '/' . $args['voorzieningslug'] . '/' . $this->releasekalender_queryvar_product . '/' . $args['productslug'];
          $theid  = $releases_query->post->post_name;

          switch ( $releasestatus['naam'] ) {
          	case 'gerealiseerd':
              $statusspan = '<span class="status"> Gerealiseerd op <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</span></span>';
          	  break;
          	case 'vervallen':
              $statusspan = '<span class="status"> Vervallen: oorspr. releasedatum <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</span></span>';
              break;
          	case 'gepland':
              $statusspan = '<span class="status"> Gepland op <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</span></span>';
              break;
          	case 'verwacht':
              $statusspan = '<span class="status"> Verwacht op <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</span></span>';
              break;
          	case 'waarschuwing':
          	// todo
              $statusspan = '<span class="status">Waarschuwing <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</span></span>';
              break;
            default:
              $statusspan = '<span class="status"> Onbekende status: ' . $releasestatus['naam'] . ' <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</span></span>';
              break;
          }
  
          $args['programma']  .= '<li class="' . $releasestatus['naam'] . '"><a href="' . $theurl . '#' . $theid . '" data-releasedatum="' . $metadata['release_releasedatum'][0] . '">' . get_the_title() . '</a>'  . $statusspan . '</li>';
        endwhile;

        $args['programma']  .= '</ul>';
        
      }

      wp_reset_postdata();        

		}

    return $args;

	}

		
		
		//========================================================================================================

		function get_releaseurl( $arguments ) {

//      'currenturl'        => 
//      'release_id'        => 
//      'voorziening_id'    => 
//      'product_id'        => 
//      'voorziening_slug'  => 
//      'product_slug'      => 
//      'inpage_id'         => 
//      'context'           => 

      // todo
      $theurl = '';

  		if ( isset( $arguments['currenturl'] ) ) {
        $theurl =  $arguments['currenturl'] ;
    		if ( isset( $arguments['voorziening_slug'] ) ) {
          $theurl .=  $this->releasekalender_queryvar_voorziening . '/' . $arguments['voorziening_slug'] . '/';
    		}
    		if ( isset( $arguments['product_slug'] ) ) {
          $theurl .=  $this->releasekalender_queryvar_product . '/' . $arguments['product_slug'] . '/';
    		}
  		}
  		elseif ( isset( $arguments['release_id'] ) ) {
        $theurl =  get_permalink( $arguments['release_id'] );
  		}
  		elseif ( isset( $arguments['voorziening_id'] ) ) {
        $theurl =  get_permalink( $arguments['voorziening_id'] );
  		}

  		if ( isset( $arguments['inpage_id'] ) ) {
        $theurl .=  '#' . $arguments['inpage_id'];
  		}

      return $theurl;

		}
		
		//========================================================================================================

		function get_slug( $theurl ) {

  		if ( $theurl ) {
        $theurl = explode('/', $theurl);
        $theurl = array_filter($theurl);
        $theurl =  array_pop($theurl);
  		}

      return $theurl;

		}
  
  	//==========================================================================================================
    /**
    * Set the alternative page names
    */
    public function set_names() {
      $this->TEMP_pagename_for_voorziening  = '';
      $this->TEMP_pagename_for_product      = '';
      $this->default_page_title_for_kalender     = __( 'Kalender', 'rijksreleasekalender' );

      if ( $this->requestedproduct ) {
      	$get_producten_args = array(
          'post_type'   => 'producten',
          'name'        => $this->requestedproduct,
          'posts_per_page'  => '-1',
          'post_status' => 'publish',
      	);
        $productquery = new WP_Query( $get_producten_args );

        if ( $productquery->have_posts() ) {
          while ($productquery->have_posts()) : 
            $productquery->the_post();
            $this->TEMP_pagename_for_product      = get_the_title();
          endwhile;
        }
        // Reset things, for good measure
        $productquery = null;
        wp_reset_postdata();
        
      }
      if ( $this->requestedvoorziening ) {
      	$get_producten_args = array(
          'post_type'   => 'voorzieningencpt',
          'name'        => $this->requestedvoorziening,
          'posts_per_page'  => '-1',
          'post_status' => 'publish',
      	);
        $productquery = new WP_Query( $get_producten_args );

        if ( $productquery->have_posts() ) {
          while ($productquery->have_posts()) : 
            $productquery->the_post();
            $this->TEMP_pagename_for_voorziening      = get_the_title();
          endwhile;
        }
        // Reset things, for good measure
        $productquery = null;
        wp_reset_postdata();
      }

    }
		
		//========================================================================================================


    function filter_the_page_title( $title, $sep, $seplocation ) {

      if ( get_query_var( $this->releasekalender_queryvar_kalender ) == $this->releasekalender_queryvar_kalender ) {
        $title = $this->default_page_title_for_kalender;
      }  
      elseif ( $this->requestedproduct &&  $this->requestedvoorziening ) {
        $title = $this->TEMP_pagename_for_product;
      }  
      elseif ( $this->requestedvoorziening ) {
        $title = $this->TEMP_pagename_for_voorziening;
      }  
      elseif ( $this->requestedproduct ) {
        $title = $this->TEMP_pagename_for_product;
      }  
      
      if ( 'right' === $seplocation ) {
        return $title . ' ' . $sep . ' ' . get_bloginfo( 'name' );
      }
      else {
        return get_bloginfo( 'name' ) . ' ' . $sep . ' ' . $title;
      }
    }

		//========================================================================================================

    function filter_the_title( $title ) {

      if ( get_query_var( $this->releasekalender_queryvar_kalender ) == $this->releasekalender_queryvar_kalender ) {
        $title = $this->default_page_title_for_kalender;
      }  
      elseif ( $this->requestedproduct &&  $this->requestedvoorziening ) {
        $title = "Product - " . $this->TEMP_pagename_for_product;
      }  
      elseif ( $this->requestedvoorziening && $this->TEMP_pagename_for_voorziening ) {
        $title = "Voorziening " . $this->TEMP_pagename_for_voorziening;
      }  
      elseif ( $this->requestedproduct && $this->TEMP_pagename_for_product ) {
        $title = "Product - " . $this->TEMP_pagename_for_product;
      }  
      
      return $title;
    }
		
		//========================================================================================================

  function write_plainhtmloutput() {

    $stylesheet = '<link rel="stylesheet" href="' . plugin_dir_url( __FILE__ ) . 'css/releasekalender_tabel_raw.css' . '" type="text/css" media="all">';
        
      echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl-nl" lang="nl-nl" dir="ltr"><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /><title>' . get_the_title() . '</title>' . $stylesheet . '</head><body>';
      echo '<h1>' . get_the_title() . ' in tabelvorm </h1>';

      $inleiding_tabelvorm           = get_option( $this->option_name . '_inleiding_tabelvorm' );

  	  if ( ! $inleiding_tabelvorm ) {
  	    $inleiding_tabelvorm = '<p>De releasekalender digitale overheid biedt afnemers – en hun leveranciers – planningsinformatie over de digitale overheid voorzieningen, met bijbehorende producten en releases. De releasekalender geeft weer wat, wanneer opgeleverd is, wordt of moet worden. Op basis van deze gegevens kan een afnemer of leverancier bepalen welke releases relevant zijn voor de eigen implementatieplanning.</p><p>Dit document bevat dezelfde planningsinformatie als de online versie van de releasekalender en maakt het eenvoudiger de releasekalender informatie intern beschikbaar te stellen of informatie te selecteren en te kopiëren zodat dit gecombineerd kan worden met eigen planningsinformatie.</p><p>Heeft u vragen of opmerkingen over de releasekalender dan kunt u contact opnemen met <a href="mailto:oplossingen@ictu.nl">oplossingen@ictu.nl</a></p>';
  	  }  
      
      if ( $inleiding_tabelvorm ) {
        echo $inleiding_tabelvorm;
      }
      
      echo '<h2>' . __( 'Voorzieningen', 'rijksreleasekalender' ) . '</h2>';

      $member_group_query = new WP_Query( array(
        'post_type'         => 'voorzieningencpt',
        'order'             => 'ASC',
        'orderby'           => 'title',
        'ignore_custom_sort'    => TRUE,
        'include_children'  => false,
        'posts_per_page'    => '-1',
        'post_status'       => 'publish'  ) );
      
      if ( $member_group_query->have_posts() ) {

        while ( $member_group_query->have_posts() ) : 
          $member_group_query->the_post(); 
          $voorziening_id     = get_the_id();
          $voorziening_title  = get_the_title();
          $voorziening_site   = get_post_meta( $voorziening_id, 'voorziening_website', true );
          if ( $voorziening_site ) {
            $voorziening_site = '<a href="' . $voorziening_site . '">' . $voorziening_site . '</a>';
          }
          else {
            $voorziening_site = '';
          }
          
          echo '<h2>' . $voorziening_title . '</h2>';
          echo '<table summary="Details van ' . $voorziening_title . '" class="rawtable">
                <tr>
                  <th scope="row">Voorziening</th>
                  <td>' . $voorziening_title . '</td>
                </tr>
                <tr>
                  <th scope="row">Beschrijving</th>
                  <td><p>' . get_the_content() . '</p></td>
                </tr>';
          echo '<tr>
                  <th scope="row">Website</th>
                  <td>' . $voorziening_site . '</td>
                </tr>';
          echo '</table>';

          // Get the producten
    			$producten_args = array(
            'post_type'   => 'producten',
            'posts_per_page'  => '-1',
            'order'       => 'ASC',
            'orderby'     => 'title',
            'post_status' => 'publish',					
            'ignore_custom_sort'    => TRUE,
            'meta_key'    => 'product_voorziening_real_id',
            'meta_query'  => array(
            	array(
            		'key'     => 'product_voorziening_real_id',
            		'value'   => $voorziening_id,
            		'compare' => '='
            	)
            )				
    			);
          $producten_query    = new WP_Query($producten_args);
          $productentable     = '';
          $releases_table     = '';

          if ( $producten_query->have_posts() ) { 
            echo '<h3>Producten voor ' . $voorziening_title . '</h3>';
            while ($producten_query->have_posts()) :
              $producten_query->the_post(); 
              // do something
              $product_id     = get_the_id();
              $product_title  = get_the_title();
              $productentable = '<table summary="Details van product ' . $product_title . '" class="rawtable">
                      <tr>
                        <th scope="col">Naam product</th>
                        <th scope="col">Beschrijving</th>
                      </tr>';
              $productentable .= '<tr><td>' . $product_title . '</td><td><p>' . get_the_content() . '</p>';
              $voorziening_website = get_post_meta( $voorziening_id, 'voorziening_website', true );
              if ( $voorziening_website ) {
                $productentable .= '<p>Zie ook: <a class="external_link" href="' . $voorziening_website . '">' . $product_title . '</a></p>';
              }
              $productentable .= '</td></tr>';
              $productentable .= '</table>';      

              // Get the releases for this product
        			$releases_args = array(
                'post_type'   => 'releases',
                'order'       => 'DESC',					
                'orderby'     => 'meta_value',					
                'ignore_custom_sort'    => TRUE,
                'post_status' => 'publish',					
                'posts_per_page'  => '-1',
                'meta_key'    => 'release_releasedatum_translated',
                'meta_query' => array(
                      	array(
                      		'key'     => 'release_voorziening_real_id',
                      		'value'   => $voorziening_id,
                      		'compare' => '='
                      	),
                      	array(
                      		'key'     => 'release_product_real_id',
                      		'value'   => $product_id,
                      		'compare' => '='
                      	)
                      )			
            			);
              $releases_query = new WP_Query($releases_args);

              $releases_table = '  <p>Er zijn geen releases bij ' . $product_title . '</p><p>&nbsp;</p>';
              
              if ( $releases_query->have_posts() ) { 
                $releases_table = '  <h4>Releases bij ' . $product_title . '</h4>';
                $releases_table .= '<table summary="Release overzicht voor product ' . $product_title . '" class="tableraw_releases">
                  <tr>
                    <th scope="col">Release</th>
                    <th scope="col">Release datum</th>
                    <th scope="col">Release status</th>
                    <th scope="col">Release marge in maanden</th>
                    <th scope="col">Beschrijving</th>
                    <th scope="col">Afhankelijkheden</th>
                    <th scope="col">Gerealiseerde afspraken en standaarden</th>
                  </tr>';

                while ($releases_query->have_posts()) :
                  $releases_query->the_post(); 
                  $release_id             = get_the_id();
                  $release_title          = get_the_title();
                  $metadata               = get_post_meta( $release_id );    	
                  $releasestatus          = maybe_unserialize( $metadata['release_release_status'][0] );
                  $release_release_marge  = maybe_unserialize( $metadata['release_release_marge'][0] );
                  $releaseafhankelijkheden        = $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ), false ); 
                  $afspraken              = $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken', 'rijksreleasekalender' ), false ); 

                  if ( $release_release_marge['numericValue'] ) {
//                    $release_release_marge = $release_release_marge['numericValue'] . ' (' . $release_release_marge['label'] . ' maanden)';
                    $release_release_marge = $release_release_marge['label'];
                  }
                  else {
                    $release_release_marge = '';
                  }

                  $releases_table .= '<tr>';
                  $releases_table .= '<td>' . $release_title . '</td>';
                  $releases_table .= '<td>' . date_i18n( 'd-m-y', $metadata['release_releasedatum_translated'][0] ) . '</td>';
                  $releases_table .= '<td>' . $releasestatus['naam'] . '</td>';
                  $releases_table .= '<td>' . $release_release_marge . '</td>';
                  $releases_table .= '<td>' . wp_strip_all_tags( get_the_content() );

                  $release_website = get_post_meta( $release_id, 'release_website', true );
                  if ( $release_website ) {
                    $releases_table .= '<p>Zie ook: <a class="external_link" href="' . $release_website . '">' . $release_title . '</a></p>';
                  }
                  $releases_table .= '</td>';

                  
                  $releases_table .= '<td>' . $releaseafhankelijkheden . '</td>';
                  $releases_table .= '<td>' . $afspraken . '</td>';
                  $releases_table .= '</tr>';
                  
                endwhile;
                  
                $releases_table .= '</table>';

                  
              }

              // Reset things, for good measure
              $releases_query = null;
              wp_reset_postdata();

              echo $productentable;
              echo $releases_table;
 
            endwhile;
 
          }    

          // Reset things, for good measure
          $producten_query = null;
          wp_reset_postdata();

        endwhile; 

      }      

      // Reset things, for good measure
      $member_group_query = null;
      wp_reset_postdata();

      if ( 'www.digitaleoverheid.nl' == $_SERVER["HTTP_HOST"] || 'digitaleoverheid.nl' == $_SERVER["HTTP_HOST"] ) { 
            echo '<!-- Piwik -->
    <script type="text/javascript">
      var _paq = _paq || [];
      _paq.push(["enableLinkTracking"]);
      _paq.push(["setLinkTrackingTimer", 750]);
      _paq.push(["enableHeartBeatTimer"]);
      _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
      _paq.push(["trackPageView"]);
      _paq.push(["enableLinkTracking"]);
      (function() {
        var u="//statistiek.rijksoverheid.nl/piwik/";
        _paq.push(["setTrackerUrl", u+"js/tracker.php"]);
        _paq.push(["setSiteId", 147]);
        var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];
        g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"js/tracker.php"; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <!-- End Piwik Code -->';
        }
        else {
            if ( WP_DEBUG ) {
              echo '<!-- Geen Piwik: ' . $_SERVER["HTTP_HOST"] . '-->';
            }
        }

      
      echo '</body></html>';
      
    }

		//========================================================================================================
    function customRSS(){

  		$rss_beschikbaar = ( get_option( $this->option_name . '_rss_beschikbaar' ) ? get_option( $this->option_name . '_rss_beschikbaar' ) : 'nee' );
      
      if ( $rss_beschikbaar == 'nee' ) {
        
      }
      else {
    		add_feed( $this->feed_name,				array( $this, 'customRSSFunc' ) );
      }
    }


		//========================================================================================================
    function customRSSFunc(){
      
      /**
       * Template Name: Custom RSS Template - Feedname
       */

      $aggregatedposts  = array();
      $url = '';

  		$hoofdpagina = intval( get_option( $this->option_name . '_hoofdpagina' ) );
      if ( is_int( $hoofdpagina ) && $hoofdpagina > 0 ) {
      }
      else {
        $hoofdpagina = 73;
      }
      $url = get_the_permalink( $hoofdpagina );

           
      $thepermalink     = '[]';          
      $postCount        = intval( get_option( $this->option_name . '_rss_size' ) );
      if ( is_int( $postCount ) && $postCount > 0 ) {
      }
      else {
        $postCount = 20;
      }

      // ======= Selecteer voorzieningen =======================================================
      $countertje = 0;
      
    	$get_voorzieningen_args = array(
        'post_type'       => 'voorzieningencpt',
        'posts_per_page'  => $postCount,
        'post_status'     => 'publish',
        'order'           => 'DESC',					
        'orderby'         => 'meta_value',					
        'ignore_custom_sort'    => TRUE,
        'meta_key'        => 'voorziening_updated',
    	);
    	
      $voorzieningquery = new WP_Query( $get_voorzieningen_args );

      if ( $voorzieningquery->have_posts() ) {
        while ($voorzieningquery->have_posts()) : 
          $voorzieningquery->the_post();
          $countertje++;
          $product_updated            = get_post_meta( get_the_ID(), 'voorziening_updated', true );
          $product_updated_datetime   = strtotime( $product_updated );

          $key = strtotime( $product_updated );

          $argsforreleaseurl = array(
            'currenturl'        => $url,
            'voorziening_slug'  => $this->get_slug( get_the_permalink() ),
            'context'           => 'customRSSFunc'
          );
          $thepermalink = $this->get_releaseurl( $argsforreleaseurl );    

          $aggregatedposts[$key] = array(
            'the_title'   => get_the_title(),
            'the_content' => get_the_excerpt(),
            'the_url'     => $thepermalink,
          );

        endwhile;
      }    
      // Reset things, for good measure
      $voorzieningquery = null;
      wp_reset_postdata();



      // ======= Selecteer producten ===========================================================
      $countertje = 0;
      
    	$get_producten_args = array(
        'post_type'       => 'producten',
        'posts_per_page'  => $postCount,
        'post_status'     => 'publish',
        'order'           => 'DESC',					
        'orderby'         => 'meta_value',					
        'ignore_custom_sort'    => TRUE,
        'meta_key'        => 'product_updated',

    	);
      $productquery = new WP_Query( $get_producten_args );

      if ( $productquery->have_posts() ) {
        while ($productquery->have_posts()) : 
          $productquery->the_post();
          $countertje++;
          $product_updated            = get_post_meta( get_the_ID(), 'product_updated', true );
          $product_updated_datetime   = strtotime( $product_updated );

          $key = strtotime( $product_updated );

          $argsforreleaseurl = array(
            'currenturl'        => $url,
            'voorziening_slug'  => get_post_meta( get_the_ID(), 'product_voorziening_real_id_slug', true ),
            'product_slug'      => $this->get_slug( get_the_permalink() ),
            'context'           => 'customRSSFunc'
          );
          $thepermalink = $this->get_releaseurl( $argsforreleaseurl );    


          $aggregatedposts[$key] = array(
            'the_title'   => get_the_title(),
            'the_content' => get_the_title( get_post_meta( get_the_ID(), 'product_voorziening_real_id', true ) ),
            'the_url'     => $thepermalink,
          );

        endwhile;
      }    
      // Reset things, for good measure
      $productquery = null;
      wp_reset_postdata();

      // ======= Selecteer releases ============================================================
    	$get_releases_args = array(
        'post_type'       => 'releases',
        'posts_per_page'  => $postCount,
        'post_status'     => 'publish',
        'order'           => 'DESC',					
        'orderby'         => 'meta_value',					
        'ignore_custom_sort'    => TRUE,
        'meta_key'        => 'release_updated_translated',

    	);
      $releasequery = new WP_Query( $get_releases_args );

      if ( $releasequery->have_posts() ) {
        while ($releasequery->have_posts()) : 
          $releasequery->the_post();
          $countertje++;
          $release_updated            = get_post_meta( get_the_ID(), 'release_updated', true );
          $product_updated_datetime   = strtotime( $product_updated );

          $key = strtotime( $release_updated );

          $argsforreleaseurl = array(
            'currenturl'        => $url,
            'voorziening_slug'  => get_post_meta( get_the_ID(), 'release_voorziening_real_id_slug', true ),
            'product_slug'      => get_post_meta( get_the_ID(), 'release_product_real_id_slug', true ),
            'inpage_id'         => $this->get_slug( get_the_permalink() ),
            'context'           => 'customRSSFunc'
          );
          $thepermalink = $this->get_releaseurl( $argsforreleaseurl );    
 

          $aggregatedposts[$key] = array(
            'the_title'   => get_the_title(),
            'the_content' => get_the_title( get_post_meta( get_the_ID(), 'release_product_real_id', true ) ),
            'the_url'     => $thepermalink,
          );

        endwhile;
      }    
      // Reset things, for good measure
      $releasequery = null;
      wp_reset_postdata();



      
      header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
      echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
      ?>
      <rss version="2.0"
              xmlns:content="http://purl.org/rss/1.0/modules/content/"
              xmlns:wfw="http://wellformedweb.org/CommentAPI/"
              xmlns:dc="http://purl.org/dc/elements/1.1/"
              xmlns:atom="http://www.w3.org/2005/Atom"
              xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
              xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
              <?php do_action('rss2_ns'); ?>>
      <channel>
              <title><?php _e( 'Releasekalender feed', 'rijksreleasekalender' ); ?> - <?php bloginfo_rss('name'); ?></title>
              <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
              <link><?php self_link() ?></link>
              <description><?php bloginfo_rss('description') ?></description>
              <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
              <language><?php echo get_option('rss_language'); ?></language>
              <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
              <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
              <?php do_action('rss2_head'); ?>
              <?php
              
              // reverse sort the array with post info
              krsort($aggregatedposts);
              $counter = 0;
              
              foreach ($aggregatedposts as $key => $val) {
              //    echo "$key = $val\n";
              
                $counter++;
                
                if ( $counter > $postCount ) {
                  break;
                }
                $keyval = date( 'Y-m-d H:i:s', $key );  
                ?>          
                      <item>
                              <title><?php echo esc_html( $val['the_title'] ); ?></title>
                              <link><?php echo $val['the_url']; ?></link>
                              <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $keyval, false); ?></pubDate>
                              <guid isPermaLink="true"><?php echo $val['the_url']; ?></guid>
                              <description><![CDATA[<?php echo esc_html( $val['the_content'] ); ?>]]></description>
                              <content:encoded><![CDATA[<?php echo esc_html( $val['the_content'] ); ?>]]></content:encoded>
                              <?php rss_enclosure(); ?>
                              <?php do_action('rss2_item'); ?>
                      </item>

              <?php
              }
              ?>          


      </channel>
      </rss>      
      <?php
      
      die('');

    }



	//========================================================================================================
  //	include_once 'partials/DEBUG_template_add_metadata_overview.php';
	//========================================================================================================
	/**
	 * THIS FUNCTION SHOULD BE DELETED
	 *
	 */
	public function DEBUG_template_add_metadata_overview( $content ) {

  	global $post;
  	
  	$thepostid  = 0;
    $pagetype   = get_post_type();

    if ( ( ! $this->requestedproduct ) && ( ! $this->requestedvoorziening ) ) {
      $thepostid = $post->ID;
    }
    else {
        
      if ( $this->requestedproduct &&  $this->requestedvoorziening ) {
        $pagetype   = 'releases';
  			$postid_query_args = array(
          'post_type'   => $pagetype,
          'meta_query' => array(
          	array(
          		'key'     => 'release_product_real_id_slug',
          		'value'   => $this->requestedproduct,
          		'compare' => '='
          	),
          	array(
          		'key'     => 'release_voorziening_real_id_slug',
          		'value'   => $this->requestedvoorziening,
          		'compare' => '='
          	)
          )				
  			);
      }
      elseif ( $this->requestedproduct ) {
        $pagetype   = 'producten';
        $postid_query_args = array(
          'name'        => $this->requestedproduct,
          'post_type'   => $pagetype,
          'post_status' => 'publish'
        );
      }
      elseif ( $this->requestedvoorziening ) {
        $pagetype   = 'voorzieningencpt';
        $postid_query_args = array(
          'name'        => $this->requestedvoorziening,
          'post_type'   => $pagetype,
          'post_status' => 'publish'
        );
      }

      $my_posts = get_posts( $postid_query_args );
      if( $my_posts ) {
        $thepostid = $my_posts[0]->ID;
      }
      wp_reset_postdata();        
    }

    $metadata = get_post_meta( $thepostid );    	

		$content .= '<div class="metadata">'; 
		$content .= '<h2>Tijdelijke testdata</h2>'; 
		$content .= '<ul><li><a href="/voorzieningencpt/">Voorzieningen</a></li><li><a href="/producten/">Producten</a></li><li><a href="/releases/">Releases</a></li></ul>'; 
		$content .= '<h3>(' . strtoupper( $pagetype ) . ') ' . get_the_title() . '</h3>'; 
		$content .= '<p><strong>Content:</strong></p><pre>' . esc_html( get_the_content() ) . '</pre>'; 
		
		if ( is_array( $metadata ) ) {
  		$content .= '<ul>'; 
      foreach( $metadata as $key => $value ){        
        if ( is_array( $value ) ) {
          $data = maybe_unserialize( $value[0] );
          if ( is_array( $data ) ) {
          	$content .= "<li><strong>" . $key . ':</strong><ul>'; 
            foreach( $data as $key1 => $value1 ){        
              $data2 = maybe_unserialize( $value1 );
              if ( is_array( $data2 ) ) {
              	$content .= "<li><strong>" . $key1 . ':</strong><ul>'; 
                foreach( $data2 as $key2 => $value2 ){        
                  if ( is_array( $value2 ) ) {
                    $content .= "<li>" . $key2 . ' => ' . implode( ', ', $value2 ) . "</li>"; 
                  }
                  else {
                    $content .= "<li>" . $key2 . ' => ' . $value2 . "</li>"; 
                  }
                }
              	$content .= "</ul></li>"; 
              }
              else {
              	$content .= "<li>" . $key1 . ' => ' . $value1 . "</li>"; 
              }
            }
          	$content .= "</ul></li>"; 
          }
          else {
            if ( $key == 'release_releasedatum_translated' || $key == 'release_updated_translated' ) {
              $releasedate = implode( '', $value );
            	$content .= "<li><strong>" . $key . '</strong> => ' . date( 'j F Y -  H:i:s', $releasedate ) . " (" . $releasedate . ")</li>"; 
            }
            else {
            	$content .= "<li><strong>" . $key . '</strong> => ' . implode( ', ', $value ) . "</li>"; 
            }
          }
        }
        else {
        	$content .= "<li>" . $key . ' => ' . $value . "</li>"; 
        }
      }
  		$content .= '</ul>'; 
		}
		$content .= '</div>'; 

		return $content;
		
	}

}
