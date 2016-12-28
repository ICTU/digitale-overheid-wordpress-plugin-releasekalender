<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


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

	private $releasekalender_queryvar_voorziening;
	private $releasekalender_queryvar_product;
	private $releasekalender_queryvar_kalender;
	private $releasekalender_queryvar_plainhtml;

	private $option_name = 'rijksreleasekalender';

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

		//========================================================================================================
		// set up 2 different virtual page templates
		// these are not actual page templates with their own .php file
		$this->releasekalender_template_dossier        = 'releasekalender-dossier-template.php';
		$this->releasekalender_template_hoofdpagina    = 'releasekalender-main-page-template.php';
		
		// add the page templates
		add_filter( 'theme_page_templates', array( $this, 'rijksreleasekalender_add_page_templates' ) );
		
    // activate the page filters
    add_action( 'template_redirect',    array( $this, 'use_page_template' )  );

		//========================================================================================================
    // initialize the query vars. Depending on the query vars we decide what to show
		$this->releasekalender_queryvar_voorziening   = 'voorziening';    // query var to recognize the voorziening / bouwsteen
		$this->releasekalender_queryvar_product   		= 'product';        // query var to recognize the product
		$this->releasekalender_queryvar_kalender   		= 'kalender';       // show the calendar overview of upcoming releases
		$this->releasekalender_queryvar_plainhtml   	= 'plainhtml';      // show the complete data in plain HTML 

    // add rewrite rules and make WP recognize the query vars
		add_filter( 'init',				array( $this, 'rijksreleasekalender_add_rewrite_rules' ) );
		add_filter( 'query_vars',	array( $this, 'rijksreleasekalender_add_query_vars' ) );
		

    // ToDo // DEBUG
    $this->TEMP_pagename_for_voorziening  = 'TEMP_pagename_for_voorziening ';
    $this->TEMP_pagename_for_product      = 'TEMP_pagename_for_product ';
		

	}

	//==========================================================================================================
  /**
  * Adds the custom post template for pages on WordPress 4.6 and older
  *
  * @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
  * @return array Expanded array of page templates.
  */
  function rijksreleasekalender_add_page_templates( $post_templates ) {
    
    $post_templates[$this->releasekalender_template_dossier]      = __( 'Releasekalender - Dossierpagina ', 'rijksreleasekalender' );    
    $post_templates[$this->releasekalender_template_hoofdpagina]  = __( 'Releasekalender - Hoofdpagina', 'rijksreleasekalender' );    
    return $post_templates;
    
  }
  
	//==========================================================================================================
  /**
  * Modify page content if using a specific page template.
  */
  public function use_page_template() {
    
    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );


    $stylesheet = '<link rel="stylesheet" href="' . plugin_dir_url( __FILE__ ) . 'css/releasekalender_tabel_raw.css' . '" type="text/css" media="all">';


    if ( get_query_var( $this->releasekalender_queryvar_plainhtml ) ) {

      echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl-nl" lang="nl-nl" dir="ltr"><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /><title>' . get_the_title() . '</title>' . $stylesheet . '</head><body>';
      echo '<h1>' . get_the_title() . ' in tabelvorm </h1>';
      // todo: content komt niet door (filter?)
      echo '<p>' . get_the_content() . '</p>';
      echo '<h2>Bouwstenen</h2>';

      $member_group_query = new WP_Query( array(
        'post_type'       => 'voorzieningen',
        'order'           => 'ASC',
        'orderby'         => 'title',
        'posts_per_page'  => '-1',
        'post_status'     => 'publish'  ) );
      
      if ( $member_group_query->have_posts() ) {

        while ( $member_group_query->have_posts() ) : 
          $member_group_query->the_post(); 
          $voorziening_id     = get_the_id();
          $voorziening_title  = get_the_title();

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
                    <td>' . get_post_meta( $voorziening_id, 'voorziening_website', true ) . '</td>
                  </tr>';
            echo '<tr>
                    <th scope="row">Aantekeningen</th>
                    <td>' . get_post_meta( $voorziening_id, 'voorziening_aantekeningen', true ) . '</td>
                  </tr>';
            echo '<tr>
                    <th scope="row">Laatst bijgewerkt op</th>
                    <td>' . get_post_meta( $voorziening_id, 'voorziening_updated', true ) . '</td>
                  </tr>';
          echo '</table>';


    			$producten_args = array(
            'post_type'   => 'producten',
            'order'       => 'ASC',
            'orderby'     => 'title',
            'meta_key'    => 'product_voorziening_real_id',
            'meta_query'  => array(
            	array(
            		'key'     => 'product_voorziening_real_id',
            		'value'   => $voorziening_id,
            		'compare' => '='
            	)
            )				
    			);


          $producten_query = new WP_Query($producten_args);
          $productentabel = '';
          $releases_for_product_tabel  = '';

          if ( $producten_query->have_posts() ) { 
            
            echo '<h3>Producten voor ' . $voorziening_title . '</h3>';



            while ($producten_query->have_posts()) :

              $producten_query->the_post(); 
              // do something
              $product_id     = get_the_id();
              $product_title  = get_the_title();

              $productentabel = '<table summary="Details van product ' . $product_title . '" class="rawtable">
                      <tr>
                        <th scope="col">Naam product</th>
                        <th scope="col">Beschrijving</th>
                      </tr>';


              $productentabel .= '<tr><td>' . $product_title . '</td><td><p>' . get_the_content() . '</p>';
              $url = get_post_meta( $voorziening_id, 'voorziening_website', true );
              if ( $url ) {
                $productentabel .= '<p>Zie ook: <a class="external_link" href="' . $url . '">' . $product_title . '</a></p>';
              }
              $productentabel .= '</td></tr>';
              $productentabel .= '</table>';      


        			$releases_args = array(
                'post_type'   => 'releases',
                'order'       => 'ASC',					
                'orderby'     => 'meta_value',					
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
    
              if ( $releases_query->have_posts() ) { 

                $releases_for_product_tabel = '  <h4>Releases bij ' . $product_title . '</h4>';

                $releases_for_product_tabel .= '<table summary="Release overzicht voor product ' . $product_title . '" class="tableraw_releases">
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

                  $release_id     = get_the_id();
                  $release_title  = get_the_title();
                  $metadata       = get_post_meta( $release_id );    	
                  $releasestatus  = maybe_unserialize( $metadata['release_release_status'][0] );
                  $release_release_marge  = maybe_unserialize( $metadata['release_release_marge'][0] );
                  $afhankelijkhede  = $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ), false ); 
                  $afspraken  = $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken', 'rijksreleasekalender' ), false ); 

                  if ( $release_release_marge['numericValue'] ) {
                    $release_release_marge = $release_release_marge['numericValue'] . ' (' . $release_release_marge['label'] . ' maanden)';
                  }
                  else {
                    $release_release_marge = '-';
                  }

                  $releases_for_product_tabel .= '<tr>';
                  $releases_for_product_tabel .= '<td>' . $release_title . '</td>';
                  $releases_for_product_tabel .= '<td>' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</td>';
                  $releases_for_product_tabel .= '<td>' . $releasestatus['naam'] . '</td>';
                  $releases_for_product_tabel .= '<td>' . $release_release_marge . '</td>';
                  $releases_for_product_tabel .= '<td>' . get_the_content() . '</td>';
                  $releases_for_product_tabel .= '<td>' . $afhankelijkhede . '</td>';
                  $releases_for_product_tabel .= '<td>' . $afspraken . '</td>';
                  $releases_for_product_tabel .= '</tr>';
                  
                endwhile;
                  
                $releases_for_product_tabel .= '</table>';

                  
              }

              // Reset things, for good measure
              $releases_query = null;
              wp_reset_postdata();
              //==

              echo $productentabel;
              echo $releases_for_product_tabel;
              

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


      
      echo '</body></html>';
      
      die();
    }

    if ( $this->releasekalender_template_hoofdpagina == $page_template ) {

      // check the breadcrumb
      add_filter( 'genesis_single_crumb',   array( $this, 'rijksreleasekalender_breadcrumb_modify' ), 10, 2 );
      add_filter( 'genesis_page_crumb',     array( $this, 'rijksreleasekalender_breadcrumb_modify' ), 10, 2 );
      add_filter( 'genesis_archive_crumb',  array( $this, 'rijksreleasekalender_breadcrumb_modify' ), 10, 2 ); 				


      if ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {

        $this->TEMP_pagename_for_kalender     = 'Kalender';
  
        // todo correct the <title>
        remove_filter( 'wp_title', 'genesis_default_title', 10, 3 ); //Default title
        add_filter( 'wp_title', array( $this, 'rijksreleasekalender_modify_title' ), 10, 3 );
        
        // filter the main template page
        add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_toon_kalender' ) );

      }
      elseif ( get_query_var( $this->releasekalender_queryvar_product ) &&  get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
        // we know the product and the voorziening

        //* Force full-width-content layout
//        add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

        // filter the main template page
        add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_release_info' ) );
        
        // ADD DEBUG CONTENT
        if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'rijksreleasekalender_DEBUG_template_add_metadata_overview' ) );
        }

      }
      elseif ( get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
        // we know only the voorziening

        //* Force full-width-content layout
        add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

        // filter the main template page
        add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_gantt_chart' ) );
        
        // ADD DEBUG CONTENT
        if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'rijksreleasekalender_DEBUG_template_add_metadata_overview' ) );
        }
        
      }
      elseif ( get_query_var( $this->releasekalender_queryvar_product ) ) {
        // we know only the product
        
        // filter the dossier template page
        if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'rijksreleasekalender_DEBUG_template_add_metadata_overview' ) );
        }
        
      }
      else {
        // plain layout. Write widget and the group overview
        
        // action for writing extra info in the alt-sidebar
        add_action( 'genesis_before_sidebar_widget_area',    array( $this, 'rijksreleasekalender_sidebar_context_widgets' )  );
  
        // filter the main template page
        add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_hoofdpagina_toon_groepen' ) );

        // filter the main template page
        add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_hoofdpagina_add_rss_and_plaintext' ) );

      }

    
    }  		
    elseif ( ( is_single() && ( 'voorzieningen' == get_post_type() ) ) ||
      ( is_single() && ( 'releases' == get_post_type() ) ) ||
      ( is_single() && ( 'producten' == get_post_type() ) ) ||
      ( $this->releasekalender_template_dossier == $page_template ) ) {
      
      // Customize the entry meta in the entry header (requires HTML5 theme support)
      add_filter( 'genesis_post_info', array( $this, 'rijksreleasekalender_correct_postinfo' ) );
      
      // filter the dossier template page
        if ( WP_DEBUG ) {
          add_filter( 'the_content', array( $this, 'rijksreleasekalender_DEBUG_template_add_metadata_overview' ) );
        }
      
      
    }
    
  }

	/**
	 * Register the stylesheets for the public pages / overviews
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

    // check if we need to add the CSS or JS
    if ( ( is_single() && ( 'voorzieningen' == get_post_type() ) ) ||
        ( is_single() && ( 'releases' == get_post_type() ) ) ||
        ( is_single() && ( 'producten' == get_post_type() ) ) ||
        ( $this->releasekalender_template_hoofdpagina == $page_template ) ||
        ( $this->releasekalender_template_dossier == $page_template ) ) {
    
        // we do. Only on these pages, add the CSS    
//        wp_enqueue_style(   $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender.css', array(), $this->version, 'all' );
        wp_enqueue_style(   $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-main-page-template.css', array(), $this->version, 'all' );

    }

	}

	/**
	 * Register the JavaScript for the public pages / overviews
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

    // check if we need to add the CSS or JS
    if ( ( is_single() && ( 'voorzieningen' == get_post_type() ) ) ||
        ( is_single() && ( 'releases' == get_post_type() ) ) ||
        ( is_single() && ( 'producten' == get_post_type() ) ) ||
        ( $this->releasekalender_template_hoofdpagina == $page_template ) ||
        ( $this->releasekalender_template_dossier == $page_template ) ) {
    
        // we do. Only on these pages, add the JS    
        wp_enqueue_script(  $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'js/min/releasekalender-min.js?v12', array( 'jquery' ), $this->version, false );
          
    }
    

	}
  
  


  	/**
  	 * Show the release info. We need to know the voorziening and product for this
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_template_release_info( $content ) {

      $args                     = array();
      $args['programma']        = '';
      $args['productslug']      = 'ledig';
      $args['voorzieningslug']  = 'ledig';
 
      if ( get_query_var( $this->releasekalender_queryvar_product ) &&  get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
        $args['productslug']      = get_query_var( $this->releasekalender_queryvar_product );
        $args['voorzieningslug']  = get_query_var( $this->releasekalender_queryvar_voorziening );
        $args['permalink']        = 'permalink';
      }


      if ( $args['productslug'] ) {
      
      	$get_producten_args = array(
          'post_type'   => 'producten',
          'name'        => $args['productslug'],
          'post_status' => 'publish',
      	);
      
      
        $productquery = new WP_Query( $get_producten_args );
        
        if ( $productquery->have_posts() ) {
          while ($productquery->have_posts()) : 
            $productquery->the_post();
      
            $metadata       = get_post_meta( get_the_id() );    	
      
            $datestring = date( 'Y-m-d H:i:s', strtotime( $metadata['product_datumIngebruikname'][0] ) );
            $datestring = get_date_from_gmt( $datestring );
            $datestring = strtotime( $datestring );
      
            $args['programma']  .= '<h1>' . get_the_title() . '</h1>';
            $args['programma']  .= '<ul>'; // 18
            if ( $datestring ) {
              $args['programma']  .= '<li>' . __( 'Ingebruikname', 'rijksreleasekalender' ) . ': ' . date_i18n( get_option( 'date_format' ), $datestring ) . '</li>';
            }
            $args['programma']  .= '<li>' . __( 'Voorziening', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_voorziening_real_id'][0] ) . '">' . get_the_title( $metadata['product_voorziening_real_id'][0] ) . '</a></li>';
      
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
              $args['programma']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_verwijzing'][0] ) . '">' . get_the_title( ) . '</a></li>';
            }
            $args['programma']  .= '</ul>';
            $args['programma']  .= get_the_content();
            
          endwhile;
        }
        else {
        }
      
      
        wp_reset_postdata();        
      
      }


   		if ( $args['productslug'] && $args['voorzieningslug'] ) {
  
          $thepage            = $args['permalink'];
          $pagetype           = 'releases';
    			$get_releases_args = array(
            'post_type'   => $pagetype,
  
            'order'       => 'ASC',					
            'orderby'     => 'meta_value',					
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
            
                $args['gepland']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
                $args['gepland']  .= '<p>' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</p><ul>';
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
        else {
          // DEBUG    		
          dovardump( $get_releases_args );
          $args['programma']      = 'WFT? product: ' . $args['productslug'] . '/ voorziening: ' . $args['voorzieningslug'];
          
        }
  
        wp_reset_postdata();        


  		}
  		else {
        // DEBUG    		
        $args['programma']      = 'WFT? product: ' . $args['productslug'] . '/ voorziening: ' . $args['voorzieningslug'];


  		}

      return $args['programma'];

    }
    

  	public function get_afhankelijkheden( $afhankelijkheden = '', $title = 'Afhankelijkheden', $writetitle = true ) {

      $return  = ''; 

      if ( $writetitle ) {
        $return  = '<h4>' . $title . '</h4>'; 
      }
    	
    	if ( $afhankelijkheden ) {
        $return  .= '<ul>'; 
        $afhankelijkheden = maybe_unserialize( $afhankelijkheden );

        foreach( $afhankelijkheden as $key => $value ){        
          if ( is_array( $value ) ) {
            $return  .= '<li>' . $value['naam'] . '</li>'; 
        	}
      	}

        $return  .= '</ul>'; 
      	
    	}
    	else {
        $return  .= '<p><em>Geen ' . strtolower( $title ) . '</em></p>'; 
    	}

      return $return; 
    }


  	/**
  	 * Show the Gantt Chart. We need to know the voorziening for this
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_template_gantt_chart( $content ) {

      //* Force full-width-content layout
      add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

    	$content                = '';
      $url                    = get_permalink( get_the_ID() );
      $title                  = '';
      $omschrijving           = '';
      $voorziening_updated  = '';
      $voorzieningslug        = get_query_var( $this->releasekalender_queryvar_voorziening );
    	
    	// DEBUG
    	$content = '';

			if ( $voorzieningslug ) {

      	$get_producten_args = array(
          'post_type'   => 'voorzieningen',
          'name'        => $voorzieningslug,
          'post_status' => 'publish',
      	);
      
      
        $productquery = new WP_Query( $get_producten_args );
        
        if ( $productquery->have_posts() ) {
          while ($productquery->have_posts()) : 
            $productquery->the_post();
            $title                  = get_the_title();
            $omschrijving           = get_the_content();
            $voorziening_updated    = get_post_meta( get_the_ID(), 'voorziening_updated', true ); // 'voorziening_updated';
          endwhile;
        }
        wp_reset_query();      





  			$releases_query_args = array(
          'post_type'   => 'producten',
          'order'       => 'ASC',					
          'orderby'     => 'meta_value',					
          'meta_key'    => 'product_voorziening_real_id_slug',
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

  			$releases_query_args = array(
          'post_type'   => 'producten',
          'order'       => 'ASC',					
          'orderby'     => 'meta_value',					
          'meta_key'    => 'product_voorziening_real_id_slug',
          'meta_query' => array(
          	array(
          		'key'     => 'product_voorziening_real_id',
          		'value'   => get_the_ID(),
          		'compare' => '='
          	)
          )				
  			);
      }

			$allproducts_query = new WP_Query( $releases_query_args );

			if ( $allproducts_query->have_posts() ) {

        $programma  = '<ul>';

        $programmaargs = array();        
        $programmaargs['year_start']  = ( date('Y') - 1 );
        $programmaargs['year_end']    = ( date('Y') + 2 );
        $programmaargs['programma']   = 'ledig';
        $programmaargs['permalink']   = $url;
        

        // de pijlstok voor het heden
        $pijlstok   = '<div class="nu"><p>' . date(get_option('date_format')) . '</p></div>';

        while ( $allproducts_query->have_posts() ) : 

          $allproducts_query->the_post();
          

          $productslug  = $this->get_slug( get_the_permalink( get_the_ID() ) );
          $theurl       = $url . $this->releasekalender_queryvar_voorziening . '/' . $voorzieningslug . '/' . $this->releasekalender_queryvar_product . '/' . $productslug . '/';

          $programma .= '<li> <a href="' . $theurl . '">' . get_the_title() . '</a>';          

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
        
    		$content = '<div id="releasekalenderoutput">'; 
    		$content .= '<div class="rk-bouwsteen">'; 

    		$content .= '<p>' . $title . ' heeft de volgende producten en releases:<br>
      (<a href="#omschrijving">naar omschrijving</a>) </p>';
    		
    		$tijdbalk = '<div class="tijdbalk">' . $this->get_tijdbalk( $programmaargs['year_start'], $programmaargs['year_end'] ) . '</div>';

    		$content .= $tijdbalk; 
    		$content .= $pijlstok; 
    		$content .= '<div class="programma">' . $programma . '</div>'; 
        $content .= $legenda_kalender;
    		
        $content .= '<h2 id="omschrijving">Omschrijving</h2>';
        $content .= '<p>' . $omschrijving . '</p>';
        $content .= '<p>' . __('Datum laatste wijziging', 'rijksreleasekalender' ) . ': ' . date_i18n( get_option( 'date_format' ), strtotime( $voorziening_updated ) ) . '</p>';
        

    
    		
    		$content .= '</div>'; 
    		$content .= '</div>'; 
    		
      }      
      else {
        // DEBUG
//    		$content .= 'ER ZIJN GEEN PRODUCTEN'; 
      }
      wp_reset_query();      
      return $content;
    	
  	}

  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_template_toon_kalender( $content ) {

      //* Force full-width-content layout
      add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

    	return '<p>Hier de kalender</p>';
  	}





	//========================================================================================================
  public function rijksreleasekalender_template_hoofdpagina_add_rss_and_plaintext( $content ) {

    $url = get_permalink( get_the_ID() );
    
    return $content . '<div class="block"><h3>' . __( "Extra's", 'rijksreleasekalender' ) . '</h3><ul><li class="rss"><a href="/releases/feed/">' . __( 'RSS Recente wijzigingen', 'rijksreleasekalender' ) . '</a></li><li><a href="' . $url . $this->releasekalender_queryvar_plainhtml . '/">' . __( 'Releasekalender als herbruikbare tabel', 'rijksreleasekalender' ) . '</a></li></ul></div>';  
	}


	//========================================================================================================

	/**
	 * Filter for the dossier page template
	 *
	 * @param  string  $content  The page content
	 * @return string  $content  The modified page content
	 */
  public function rijksreleasekalender_template_hoofdpagina_toon_groepen( $content ) {
    
    $url = get_permalink( get_the_ID() );
    
    $member_group_terms = get_terms( 'voorziening-groep' );
    
    foreach ( $member_group_terms as $member_group_term ) {
      
      $member_group_query = new WP_Query( array(
      'post_type' => 'voorzieningen',
      'tax_query' => array(
                    array(
                      'taxonomy' => 'voorziening-groep',
                      'field' => 'slug',
                      'terms' => array( $member_group_term->slug ),
                      'operator' => 'IN'
                    )
                  ) ) );
      
      if ( $member_group_query->have_posts() ) : 
        
        $titletag = 'h2';
        if ( $member_group_term->parent > 0 ) {
          $titletag = 'h3';
        }
        
        $content .= '<' . $titletag . '>' . $member_group_term->name . '</' . $titletag . '>';
        $content .= '<ul>';
        
        while ( $member_group_query->have_posts() ) : $member_group_query->the_post(); 
          $theslug    = $this->get_slug( get_the_permalink( get_the_ID() ) );
          $posturl    = $url . $this->releasekalender_queryvar_voorziening . '/' . $theslug . '/';
          $content .= '<li><a href="' . $posturl . '">' . get_the_title() . '</a></li>';
        endwhile; 
        
        $content .= '</ul>';
      
      endif; 
      
      
      // Reset things, for good measure
      $member_group_query = null;
      wp_reset_postdata();
    }  
    
    return $content;
    }

  
	//========================================================================================================
//	include_once 'partials/rijksreleasekalender_DEBUG_template_add_metadata_overview.php';

	
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_DEBUG_template_add_metadata_overview( $content ) {

    	global $post;
    	
    	$thepostid  = 0;
      $pagetype   = get_post_type();

      if ( ( ! get_query_var( $this->releasekalender_queryvar_product ) ) && ( ! get_query_var( $this->releasekalender_queryvar_voorziening ) ) ) {
        $thepostid = $post->ID;
      }
      else {
          
        if ( get_query_var( $this->releasekalender_queryvar_product ) &&  get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
          $pagetype   = 'releases';
    			$postid_query_args = array(
            'post_type'   => $pagetype,
            'meta_query' => array(
            	array(
            		'key'     => 'release_product_real_id_slug',
            		'value'   => get_query_var( $this->releasekalender_queryvar_product ),
            		'compare' => '='
            	),
            	array(
            		'key'     => 'release_voorziening_real_id_slug',
            		'value'   => get_query_var( $this->releasekalender_queryvar_voorziening ),
            		'compare' => '='
            	)
            )				
    			);
        }
        elseif ( get_query_var( $this->releasekalender_queryvar_product ) ) {
          $pagetype   = 'producten';
          $postid_query_args = array(
            'name'        => get_query_var( $this->releasekalender_queryvar_product ),
            'post_type'   => $pagetype,
            'post_status' => 'publish'
          );
        }
        elseif ( get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
          $pagetype   = 'voorzieningen';
          $postid_query_args = array(
            'name'        => get_query_var( $this->releasekalender_queryvar_voorziening ),
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

          // DEBUG
  		$content .= '<div class="metadata">'; 
  		$content .= '<h2>Tijdelijke testdata</h2>'; 
  		$content .= '<ul><li><a href="/voorzieningen/">Voorzieningen</a></li><li><a href="/producten/">Producten</a></li><li><a href="/releases/">Releases</a></li></ul>'; 
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
      //============================================================================================================

  		return $content;
  		
  	}

	//========================================================================================================
  /**
  * Filter for the <title>
  *
  * @param  string  $content  The page content
  * @return string  $content  The modified page content
  */
  function rijksreleasekalender_modify_title( $title) {
    if ( get_query_var( $this->releasekalender_queryvar_kalender ) ) {
      $title = $this->TEMP_pagename_for_kalender .' ' . $title;
    }
    return $title;
  }


	//========================================================================================================
  /**
  * loop for the tijdbalk, goes from start year to end year
  *
  * @param  string  $year_start, $year_end, both years
  * @return string  $content  the tijdbalk
  */
  function get_tijdbalk( $year_start, $year_end ) {
    
    $tijdbalk = '';
    if ( is_int( $year_start ) && is_int( $year_end ) &&  ( $year_start < $year_end ) ) {
      $tijdbalk = '<ul>';
      for ($i = $year_start; $i <= $year_end; $i++) {
        $tijdbalk .= '<li>' . $i . '<ul><li>Q1</li><li>Q2</li><li>Q3</li><li>Q4</li></ul></li>';
      }
      $tijdbalk .= '</ul>';
    }
    else {
      // DEBUG
      $tijdbalk = 'Onvolledige gegevens: start: "' . $year_start . '", end: "' . $year_end . '"';
    }
    return $tijdbalk;
    
  }

  
    
  //========================================================================================================
  function rijksreleasekalender_correct_postinfo($post_info) {
    global $wp_query;
    global $post;
    
    if ( ( is_single() && ( 'producten' == get_post_type() ) ) ||
          ( is_single() && ( 'releases' == get_post_type() ) ) ||
          ( is_single() && ( 'voorzieningen' == get_post_type() ) ) ) {
      return '';
    }
    else {
      return $post_info;
    }
  }

	//========================================================================================================
  public function rijksreleasekalender_breadcrumb_modify( $crumb, $args ) {
    
    global $post;
    
    $page_template  = get_post_meta( get_the_ID(), '_wp_page_template', true );
    $thelink        = get_permalink( get_the_id() ) . $this->releasekalender_queryvar_voorziening . '/' . get_query_var( $this->releasekalender_queryvar_voorziening ) . '/';
    
    // DEBUG
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
    

	//========================================================================================================
  function rijksreleasekalender_sidebar_context_widgets() {
    
    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
    
    if ( $this->releasekalender_template_hoofdpagina == $page_template ) {
      
      $recent_max_age           = intval( get_option( $this->option_name . '_recent_max_age' ) );
      
      if ( is_int( $recent_max_age ) && $recent_max_age > 0 ) {
      }
      else {
        $recent_max_age = 10;
      }
      
      $url = get_permalink( get_the_ID() );
      
      $start  = strtotime( date('y:m:d') );
      $end    = strtotime( date('y:m:d') . ' + ' . $recent_max_age . ' days' );
      
      // Select the upcoming releases for the x few days
      $releases_query_args = array(
        'post_type'   => 'releases',
        'order'       => 'ASC',					
        'orderby'     => 'meta_value',					
        'meta_key'    => 'release_releasedatum_translated',
        'meta_query'  => array(
                        array(
                          'key' => 'release_releasedatum_translated',
                          'value' => array($start, $end),
                          'compare' => 'BETWEEN'
                        )
        )				
      );
      $releases_query = new WP_Query( $releases_query_args );
      
      echo '<div id="releasekalender_kalender-widget_' . get_the_ID() . '" class="widget releasekalender releasekalender-kalender-widget">
      <div class="widget-wrap">
        <div class="text">
          <h3 class="widgettitle">' . __('Aankomende releases', 'rijksreleasekalender' ) . '</h3>';
      
      if ( $releases_query->have_posts() ) {
        
        echo '<p>' . sprintf( _n( 'Dit zijn de releases voor morgen.', 'Dit zijn de releases van de eerstkomende %s dagen.', $recent_max_age, 'rijksreleasekalender' ), $recent_max_age ) . '</p>';
        echo '<ul class="list">';
        
        while ($releases_query->have_posts()) : $releases_query->the_post();
        
          $release_voorziening_slug   = get_post_meta( get_the_id(), 'release_voorziening_real_id_slug' );
          $release_product_slug       = get_post_meta( get_the_id(), 'release_product_real_id_slug' );
          $release_product            = $this->releasekalender_queryvar_product . '/' . $release_product_slug[0] . '/';
          $release_voorziening        = $this->releasekalender_queryvar_voorziening . '/' . $release_voorziening_slug[0] . '/';
  
          $releasedatum               = get_post_meta( get_the_id(), 'release_releasedatum_translated' );
          $releasedatum               = date_i18n( get_option( 'date_format' ), $releasedatum[0] );
          
          $posturl                    = $url . $release_voorziening . $release_product;
                
          echo '<li><h4><a href="' . $posturl . '">';
          echo get_the_title();
          echo '</a></h4>';
          echo '<p class="details">' . $releasedatum . '</p>';
          echo '</li>';

        endwhile;
        
        echo '</ul>';

      }
      else {
      
        echo '<p>' . sprintf( _n( 'Geen releases gevonden voor morgen.', 'Geen releases gevonden voor de eerstkomende %s dagen.', $recent_max_age, 'rijksreleasekalender' ), $recent_max_age ) . '</p>';
      
      }
      wp_reset_postdata();
      
      
      
      echo '<div class="category-link more"><a href="' . $url . $this->releasekalender_queryvar_kalender . '/">' . __( 'Volledige kalender','' ) . '</a></div>';
      
      
      echo '
      </div>
      </div>
      </div>';
      
    }
  }
		
		//========================================================================================================
		
		function rijksreleasekalender_add_rewrite_rules() {

		  // rewrite rules for posts in dossier context
		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)(/' . $this->releasekalender_queryvar_product . '/)(.+?)/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]&' . $this->releasekalender_queryvar_product . '=$matches[5]', 'top');
		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]', 'top');

		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)(/' . $this->releasekalender_queryvar_product . '/)(.+?)?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]&' . $this->releasekalender_queryvar_product . '=$matches[5]', 'top');
		  add_rewrite_rule( '(.+?)(/' . $this->releasekalender_queryvar_voorziening . '/)(.+?)?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_voorziening . '=$matches[3]', 'top');
		

		  add_rewrite_rule( '(.+?)/' . $this->releasekalender_queryvar_kalender . '/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_kalender . '=' . $this->releasekalender_queryvar_kalender, 'top');

		  add_rewrite_rule( '(.+?)/' . $this->releasekalender_queryvar_plainhtml . '/?$', 'index.php?pagename=$matches[1]&' . $this->releasekalender_queryvar_plainhtml . '=' . $this->releasekalender_queryvar_plainhtml, 'top');

		}

		//========================================================================================================
		
		function rijksreleasekalender_add_query_vars($vars) {
			$vars[] = $this->releasekalender_queryvar_voorziening;
			$vars[] = $this->releasekalender_queryvar_product;
			$vars[] = $this->releasekalender_queryvar_kalender;
			$vars[] = $this->releasekalender_queryvar_plainhtml;

			return $vars;
		}

		//========================================================================================================
		
		function get_releases_for_voorziening_and_product( $args ) {

  		if ( $args['productslug'] && $args['voorzieningslug'] ) {

        $thepage            = $args['permalink'];
        $pagetype           = 'releases';
  			$get_releases_args = array(
          'post_type'   => $pagetype,

          'order'       => 'ASC',					
          'orderby'     => 'meta_value',					
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
    
          $metadata       = get_post_meta( get_the_id() );    	
          $releasestatus  = maybe_unserialize( $metadata['release_release_status'][0] );

          $releasejaar = date( 'Y', $metadata['release_releasedatum_translated'][0] );
          
          if ( intval( $releasejaar ) < intval( $args['year_start'] ) ) {
            $args['year_start'] = $releasejaar;
          }
          if ( intval( $releasejaar ) > intval( $args['year_end'] ) ) {
            $args['year_end'] = $releasejaar;
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

          $args['programma']  .= '<li class="' . $releasestatus['naam'] . '"><a href="' . $theurl . '#' . $theid . '">' . get_the_title() . '</a>'  . $statusspan . '</li>';


        endwhile;
//min.js
        $args['programma']  .= '</ul>';
        
      }

        wp_reset_postdata();        

        
//        $args['programma']  = '<ul><li class="gerealiseerd"><a href="' . $url . '">Q2 2012</a> <span class="status">' . $args['year_start'] . ' /' . $args['year_end'] . ' Gerealiseerd op <span class="datum">01 juli 2012</span></span></li></ul>';

  		}

      return $args;

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
		
		//========================================================================================================


}
