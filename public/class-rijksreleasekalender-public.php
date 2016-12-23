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
	private $releasekalender_hoofdpaginatemplate;

	/**
	 * The page template slug for the dossier page template
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    page template slug
	 */
	private $releasekalender_dossieraginatemplate;

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
		
		$this->releasekalender_dossieraginatemplate   = 'releasekalender-dossier-template.php';
		$this->releasekalender_hoofdpaginatemplate    = 'releasekalender-main-page-template.php';
		

		// add the page templates
		add_filter( 'theme_page_templates', array( $this, 'rhswp_add_page_templates' ) );
		
		// activate the page filters
		add_action( 'template_redirect',    array( $this, 'use_page_template' )  );

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

//		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/rijksreleasekalender-public.css', array(), $this->version, 'all' );

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
    function rhswp_add_page_templates( $post_templates ) {

      $post_templates[$this->releasekalender_dossieraginatemplate]  = __( 'Releasekalender - Dossierpagina ', 'rijksreleasekalender' );    
      $post_templates[$this->releasekalender_hoofdpaginatemplate]   = __( 'Releasekalender - Hoofdpagina', 'rijksreleasekalender' );    
      return $post_templates;
      
    }
  
  	/**
  	 * Modify page content if using a specific page template.
  	 */
  	public function use_page_template() {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
  		
      if ( is_single() && ( 'voorziening' == get_post_type() ) ) {

        // Customize the entry meta in the entry header (requires HTML5 theme support)
        add_filter( 'genesis_post_info', array( $this, 'rijksreleasekalender_correct_postinfo' ) );

    		// filter the dossier template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_dossieraginatemplate_filter' ) );

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-dossier-template.css', array(), $this->version, 'all' );

  		}
  		elseif ( $this->releasekalender_dossieraginatemplate == $page_template ) {
    		// filter the dossier template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_dossieraginatemplate_filter' ) );

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-dossier-template.css', array(), $this->version, 'all' );

  		}
  		elseif ( $this->releasekalender_hoofdpaginatemplate == $page_template ) {
    		// filter the main template page
  			add_filter( 'the_content', array( $this, 'rijksreleasekalender_hoofdpaginatemplate_filter' ) );

    		wp_enqueue_style( $this->rijksreleasekalender, plugin_dir_url( __FILE__ ) . 'css/releasekalender-main-page-template.css', array(), $this->version, 'all' );

  		}
  		
  	}
  
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_hoofdpaginatemplate_filter( $content ) {

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

      // TODO: toon de juist informatie voor deze voorziening

  		$content = '<h2>' . __( 'Hoofdpagina releasekalender', 'rijksreleasekalender' ) . '</h2><p>' . __( 'Meer volgt.', 'rijksreleasekalender' ) . '<br>' . $page_template . '</p>' . $content;
  		return $content;
  	}

  
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function rijksreleasekalender_dossieraginatemplate_filter( $content ) {
    	
    	global $post;

      $tempcontent = $content;

      $tijdbalkhiero                = 'tijdbalkhiero';
      $datumnu                      = 'datumnu';
      $totaleprogramma              = 'totaleprogramma';
      $voorziening_updated          = get_post_meta( get_the_ID(), 'voorziening_updated', true ); // 'voorziening_updated';
      $voorziening_website          = get_post_meta( get_the_ID(), 'voorziening_website', true ); // 'voorziening_website';
      $voorziening_eigenaarContact  = get_post_meta( get_the_ID(), 'voorziening_eigenaarContact', true ); // 'voorziening_eigenaarContact';
      $voorziening_aantekeningen    = get_post_meta( get_the_ID(), 'voorziening_aantekeningen', true ); // 'voorziening_aantekeningen';
      


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



  		return $content;
  		
  	}

    
    function rijksreleasekalender_correct_postinfo($post_info) {
        global $wp_query;
        global $post;

        if ( is_single() && ( 'voorziening' == get_post_type() ) ) {
          return '';
        }
        else {
          return $post_info;
        }
        
    }



}
