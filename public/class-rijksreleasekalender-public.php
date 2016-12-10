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
  		
  		if ( $this->releasekalender_dossieraginatemplate == $page_template ) {
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

  		$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

      // TODO: toon de juist informatie voor deze voorziening

  		$content = '<h2>' . __( 'Releasekalender info voor een dossier pagina', 'rijksreleasekalender' ) . '</h2><p>' . __( 'Meer volgt.', 'rijksreleasekalender' ) . '<br>' . $page_template . '</p>' . $content;
  		return $content;
  	}


}
