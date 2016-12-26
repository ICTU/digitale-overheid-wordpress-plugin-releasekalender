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

		wp_enqueue_script( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'js/rijksreleasekalender-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Hides the custom post template for pages on WordPress 4.6 and older
     *
     * @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
     * @return array Expanded array of page templates.
     */
    function rijksreleasekalender_add_page_templates( $post_templates ) {

      $post_templates[$this->releasekalender_template_dossier]  = __( 'Releasekalender - Dossierpagina ', 'rijksreleasekalender' );    
      $post_templates[$this->releasekalender_template_hoofdpagina]   = __( 'Releasekalender - Hoofdpagina', 'rijksreleasekalender' );    
      return $post_templates;
      
    }
  
  	/**
  	 * Modify page content if using a specific page template.
  	 */
  	public function use_page_template() {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

      if ( $this->releasekalender_template_hoofdpagina == $page_template ) {
      
        // action for writing extra info in the alt-sidebar
        add_action( 'genesis_before_sidebar_widget_area',    array( $this, 'rijksreleasekalender_sidebar_context_widgets' )  );
        
      }  		
  
      if ( ( is_single() && ( 'voorziening' == get_post_type() ) ) ||
            ( is_single() && ( 'release' == get_post_type() ) ) ||
            ( is_single() && ( 'product' == get_post_type() ) ) ) {

        // Customize the entry meta in the entry header (requires HTML5 theme support)
        add_filter( 'genesis_post_info', array( $this, 'rijksreleasekalender_correct_postinfo' ) );

    		// filter the dossier template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_dossier_filter' ) );

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-dossier-template.css', array(), $this->version, 'all' );

  		}
  		elseif ( $this->releasekalender_template_dossier == $page_template ) {
    		// filter the dossier template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_dossier_filter' ) );

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-dossier-template.css', array(), $this->version, 'all' );

  		}
  		elseif ( $this->releasekalender_template_hoofdpagina == $page_template ) {
    		// filter the main template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_template_hoofdpagina_filter' ) );

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-main-page-template.css', array(), $this->version, 'all' );

  		}
  		
  	}
  
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_template_hoofdpagina_filter( $content ) {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

      // TODO: toon de juist informatie voor deze voorziening

//  		$content = '<h2>' . __( 'Hoofdpagina releasekalender', 'rijksreleasekalender' ) . '</h2><p>' . __( 'Meer volgt.', 'rijksreleasekalender' ) . '<br>' . $page_template . '</p>';
  		$content = '<h2>' . __( 'Hoofdpagina releasekalender', 'rijksreleasekalender' ) . '</h2><p style="background: red; color: white;">Hieronder staan alleen de voorzieniningen die aan een groep zijn toegekend.<br>De links tonen op dit moment alleen nog maar ruwe test-data</p>';
      
      $member_group_terms = get_terms( 'voorziening-groep' );
      
      foreach ( $member_group_terms as $member_group_term ) {
        
        $member_group_query = new WP_Query( array(
            'post_type' => 'voorziening',
            'tax_query' => array(
            array(
              'taxonomy' => 'voorziening-groep',
              'field' => 'slug',
              'terms' => array( $member_group_term->slug ),
              'operator' => 'IN'
            )
            )
          ) 
        );
        
        
        if ( $member_group_query->have_posts() ) : 

          $titletag = 'h2';
          
          if ( $member_group_term->parent > 0 ) {
            $titletag = 'h3';
          }
          
          $content .= '<' . $titletag . '>' . $member_group_term->name . '</' . $titletag . '>';
          $content .= '<ul>';

          while ( $member_group_query->have_posts() ) : $member_group_query->the_post(); 
            $content .= '<li><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></li>';
          endwhile; 
        endif; 

        $content .= '</ul>';

        // Reset things, for good measure
        $member_group_query = null;
        wp_reset_postdata();
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
    	
    	global $post;


      $tempcontent = $content;

      $tijdbalkhiero                = 'tijdbalkhiero';
      $datumnu                      = 'datumnu';
      $totaleprogramma              = 'totaleprogramma';
      $voorziening_updated          = get_post_meta( get_the_ID(), 'voorziening_updated', true ); // 'voorziening_updated';
      $voorziening_website          = get_post_meta( get_the_ID(), 'voorziening_website', true ); // 'voorziening_website';
      $voorziening_eigenaarContact  = get_post_meta( get_the_ID(), 'voorziening_eigenaarContact', true ); // 'voorziening_eigenaarContact';
      $voorziening_aantekeningen    = get_post_meta( get_the_ID(), 'voorziening_aantekeningen', true ); // 'voorziening_aantekeningen';

  		$content                      = ''; 
      

if ( 22 == 33 ) {
  

      // TODO: toon de juist informatie voor deze voorziening

  		$content = '<div id="releasekalenderoutput">'; 
  		$content .= '<div class="rk-bouwsteen">'; 
 
  		
  		$content .= '<p>' . get_the_title() . ' ' . _x( 'heeft de volgende producten en releases:', 'rijksreleasekalender' ) . '<br><a href="#beschrijving">'. _x( '(naar omschrijving)', 'rijksreleasekalender' ) . '</a></p>';

      // hier de tijdbalk
  		$content .= '<div class="tijdbalk">' . $tijdbalkhiero . '</div>';

      // de pijlstok voor het heden
  		$content .= '<div class="nu"><p>' . $datumnu . '</p></div>';
      
      // het overzicht van alle producten en releases
  		$content .= '<div class="programma">' . $totaleprogramma . '</div>';

      // de legenda
  		$content .= '<ul class="legenda"><li class="vervallen"><span class="status">Vervallen = </span> Vervallen release</li><li class="gerealiseerd"><span class="status">Gerealiseerd = </span> Gerealiseerde release</li><li><span class="status">Gepland of Verwacht = </span>Een geplande of verwachte release</li><li class="waarschuwing"><span class="status">Waarschuwing = </span> Release met mogelijk probleem bij afhankelijkheid</li></ul>';

      // de beschrijving
  		$content .= '<div><h2 id="omschrijving">' . __( 'Omschrijving', 'rijksreleasekalender' ) . '</h2>' . $tempcontent . '<p><em>' . __('Datum laatste wijziging:', 'rijksreleasekalender' ) . date_i18n( get_option( 'date_format' ), strtotime( $voorziening_updated ) ) . '</em></p></div>';

      // zie ook
      if ( $voorziening_website ) {
        
    		$content .= '<div class="block"><h2>' . __( 'Zie ook', 'rijksreleasekalender' ) . '</h2><ul class="external"><li><a href="' . $voorziening_website . '">' . get_the_title() . '</a></li></ul></div>';

      }

    

      
  		$content .= '</div>'; 
  		$content .= '</div>'; 

}

      //============================================================================================================
      $metadata = get_post_meta( $post->ID );    	

  		$content .= '<div class="metadata">'; 
  		$content .= '<h2>Tijdelijke testdata</h2>'; 
  		$content .= '<ul><li><a href="/voorziening/">Voorzieningen</a></li><li><a href="/product/">Producten</a></li><li><a href="/release/">Releases</a></li></ul>'; 
  		$content .= '<h3>(' . strtoupper( get_post_type() ) . ') ' . get_the_title() . '</h3>'; 
  		$content .= '<p><strong>Content:</strong></p><pre>' . esc_html( get_the_content() ) . '</pre>'; 
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
  		$content .= '</div>'; 
      //============================================================================================================

  		return $content;
  		
  	}

    
    function rijksreleasekalender_correct_postinfo($post_info) {
        global $wp_query;
        global $post;

        if ( ( is_single() && ( 'product' == get_post_type() ) ) ||
             ( is_single() && ( 'release' == get_post_type() ) ) ||
             ( is_single() && ( 'voorziening' == get_post_type() ) ) ) {
          return '';
        }
        else {
          return $post_info;
        }
        
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
          'post_type'   => 'release',
          'order'       => 'ASC',					
          'orderby'     => 'meta_value',					
          'meta_key'    => 'release_releasedatum_translated',
          'meta_query' => array(
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

$releasedatum = get_post_meta( get_the_id(), 'release_releasedatum_translated' );

$releasedatum = date_i18n( get_option( 'date_format' ), $releasedatum[0] );
          
    				echo '<li><h4>';
    				echo get_the_title();
    				echo '</h4>';
    				echo '<p class="details">' . $releasedatum . '</p>';
    				echo '</li>';
          endwhile;

          echo '</ul>';
  				

//          echo '<ul class="list">
//          <h4><a href=' . $url . $this->releasekalender_queryvar_voorziening . '/berichtenbox-voor-bedrijven/' . $this->releasekalender_queryvar_product . '/berichtenbox-voor-bedrijven#aansluitvoorziening-via-digikoppeling">Aansluitvoorziening via Digikoppeling</a></h4>
//          Berichtenbox voor bedrijven
//         <p class="details">31 december 2016</p>
//          </li>
//          </ul>';
  				
				}
				else {

          echo '<p>' . sprintf( _n( 'Geen releases gevonden voor morgen.', 'Geen releases gevonden voor de eerstkomende %s dagen.', $recent_max_age, 'rijksreleasekalender' ), $recent_max_age ) . '</p>';
  				
				}


        
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


}
