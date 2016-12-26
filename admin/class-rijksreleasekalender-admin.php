<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://nostromo.nl
 * @since      1.0.0
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/admin
 * @author     Marcel Bootsman <marcel@nostromo.nl>
 */
class rijksreleasekalender_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $rijksreleasekalender The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The options name to be used in this plugin
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var    string $option_name Option name of this plugin
	 */
	private $option_name = 'rijksreleasekalender';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $rijksreleasekalender The name of this plugin.
	 * @param      string $version              The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * Styles for the admin
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rijksreleasekalender-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * JS for the admin
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rijksreleasekalender-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register all settings  for the options
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_register_settings() {

		// Sections for options page

		// Add a General section
		add_settings_section(
			$this->option_name . '_general',
			__( 'Algemeen', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_general_cb' ),
			$this->plugin_name
		);

		//Add a connection section
		add_settings_section(
			$this->option_name . '_connection',
			__( 'Verbinding', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_connection_cb' ),
			$this->plugin_name
		);

		// General options

		add_settings_field(
			$this->option_name . '_restapi_url',
			__( 'REST-API url', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_restapi_url_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_restapi_url' )
		);

		add_settings_field(
			$this->option_name . '_restapi_key',
			__( 'REST-API key', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_restapi_key_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_restapi_key' )
		);

		add_settings_field(
			$this->option_name . '_restapi_user',
			__( 'REST-API gebruikersnaam', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_restapi_user_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_restapi_user' )
		);

		add_settings_field(
			$this->option_name . '_restapi_pwd',
			__( 'REST-API wachtwoord', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_restapi_pwd_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_restapi_pwd' )
		);

		add_settings_field(
			$this->option_name . '_rss_size',
			__( 'Maximum aantal items in RSS', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_rss_size_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_rss_size' )
		);

		add_settings_field(
			$this->option_name . '_update_key',
			__( 'Update key', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_update_key_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_update_key' )
		);

		add_settings_field(
			$this->option_name . '_author_id',
			__( 'Gebruiker', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_author_id_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_author_id' )
		);


		add_settings_field(
			$this->option_name . '_recent_max_age',
			__( 'Widget: toon releases van de komende', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_recent_max_age_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_recent_max_age' )
		);

		// Register the general options

		register_setting( $this->plugin_name, $this->option_name . '_restapi_url' );
		register_setting( $this->plugin_name, $this->option_name . '_restapi_key' );
		register_setting( $this->plugin_name, $this->option_name . '_restapi_user' );
		register_setting( $this->plugin_name, $this->option_name . '_restapi_pwd' );
		register_setting( $this->plugin_name, $this->option_name . '_rss_size' );
		register_setting( $this->plugin_name, $this->option_name . '_update_key' );
		register_setting( $this->plugin_name, $this->option_name . '_author_id' );
		register_setting( $this->plugin_name, $this->option_name . '_recent_max_age' );

		// Connection options

		add_settings_field(
			$this->option_name . '_ignore_ssl',
			__( 'SSL negeren', '' ),
			array( $this, $this->option_name . '_ignore_ssl_cb' ),
			$this->plugin_name,
			$this->option_name . '_connection',
			array( 'label_for' => $this->option_name . '_ignore_ssl' )
		);

		add_settings_field(
			$this->option_name . '_proxy_host',
			__( 'Proxy host', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_proxy_host_cb' ),
			$this->plugin_name,
			$this->option_name . '_connection',
			array( 'label_for' => $this->option_name . '_proxy_host' )
		);

		add_settings_field(
			$this->option_name . '_proxy_port',
			__( 'Proxy port', 'rijksreleasekalender' ),
			array( $this, $this->option_name . '_proxy_port_cb' ),
			$this->plugin_name,
			$this->option_name . '_connection',
			array( 'label_for' => $this->option_name . '_proxy_port' )
		);

		// Register the connection options

		register_setting( $this->plugin_name, $this->option_name . '_ignore_ssl' );
		register_setting( $this->plugin_name, $this->option_name . '_proxy_host' );
		register_setting( $this->plugin_name, $this->option_name . '_proxy_port' );

	}

	/**
	 * Render the REST API url input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_restapi_url_cb() {
		$restapi_url = get_option( $this->option_name . '_restapi_url' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_restapi_url' . '" id="' . $this->option_name . '_restapi_url' . '" value="' . $restapi_url . '"> ';
	}

	/**
	 * Render the REST API key input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_restapi_key_cb() {
		$restapi_key = get_option( $this->option_name . '_restapi_key' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_restapi_key' . '" id="' . $this->option_name . '_restapi_key' . '" value="' . $restapi_key . '"> ';
	}

	/**
	 * Render the REST API user input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_restapi_user_cb() {
		$restapi_user = get_option( $this->option_name . '_restapi_user' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_restapi_user' . '" id="' . $this->option_name . '_restapi_user' . '" value="' . $restapi_user . '"> ';
	}

	/**
	 * Render the REST API key input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_restapi_pwd_cb() {
		$restapi_pwd = get_option( $this->option_name . '_restapi_pwd' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_restapi_pwd' . '" id="' . $this->option_name . '_restapi_pwd' . '" value="' . $restapi_pwd . '"> ';
	}

	/**
	 * Render the update key input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_update_key_cb() {
		$update_key = get_option( $this->option_name . '_update_key' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_update_key' . '" id="' . $this->option_name . '_update_key' . '" value="' . $update_key . '"> ';
	}

	/**
	 * Render the author dropdown
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_author_id_cb() {
		$author_id = get_option( $this->option_name . '_author_id' );
		$users     = get_users( 'orderby=display_name&order=ASC' );
		$selected  = selected( $author_id, '', false );

		echo '<select name="' . $this->option_name . '_author_id' . '" class="regular-text">';
		echo '<option value="" ' . $selected . '></option>';
		foreach ( $users as $user ) {

			$selected = selected( $author_id, $user->ID );
			echo '<option value="' . $user->ID . '" id="' . $user->ID . '" ' . $selected . '>' . $user->display_name . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Render the recent max age input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_recent_max_age_cb() {

    $maxdays = 90;

		$recent_max_age           = intval( get_option( $this->option_name . '_recent_max_age' ) );

    if ( is_int( $recent_max_age ) && $recent_max_age > 0  && $recent_max_age < $maxdays ) {
    }
    else {
      $recent_max_age = 10;
    }


		echo '<select name="' . $this->option_name . '_recent_max_age' . '" id="' . $this->option_name . '_recent_max_age' . '" class="regular-text">';
    
    for ($i = 1; $i <= $maxdays; $i++) {
      $selected = '';
      $name = __( 'dagen', 'rijksreleasekalender' );
      
      if ( $i < 2 ) {
        $name = __( 'dag', 'rijksreleasekalender' );
      }
      if ( $i == $recent_max_age ) {
        $selected = ' selected';
      }
      echo '<option value="' . $i . '"' . $selected . '>' . $i . ' ' . $name. '</option>';
    }		
    
		echo '</select>';

	}

	/**
	 * Render the ignore ssl user input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_ignore_ssl_cb() {
		$ignore_ssl = get_option( $this->option_name . '_ignore_ssl' );
		?>
			<fieldset>
				<label>
					<input type="radio" name="<?php echo $this->option_name . '_ignore_ssl' ?>" id="<?php echo $this->option_name . '_ignore_ssl' ?>" value="ja" <?php checked( $ignore_ssl, 'ja' ); ?>>
			<?php _e( 'Ja', 'rijskreleasekalender' ); ?>
				</label>
				<br>
				<label>
					<input type="radio" name="<?php echo $this->option_name . '_ignore_ssl' ?>" value="nee" <?php checked( $ignore_ssl, 'nee' ); ?>>
			<?php _e( 'Nee', 'rijskreleasekalender' ); ?>
				</label>
			</fieldset>
		<?php
	}

	/**
	 * Render the RSS size input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_rss_size_cb() {
		$rss_size = get_option( $this->option_name . '_rss_size' );
		echo '<select name="' . $this->option_name . '_rss_size' . '" id="' . $this->option_name . '_rss_size' . '" class="regular-text">';
    
    for ($i = 1; $i <= 20; $i++) {
      $selected = '';
      $name = __( 'items', 'rijksreleasekalender' );
      
      if ( $i < 2 ) {
        $name = __( 'item', 'rijksreleasekalender' );
      }
      if ( $i == $rss_size ) {
        $selected = ' selected';
      }
      echo '<option value="' . $i . '"' . $selected . '>' . $i . ' ' . $name. '</option>';
    }		
    
		echo '</select>';
	}


	/**
	 * Render the Proxy host input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_proxy_host_cb() {
		$proxy_host = get_option( $this->option_name . '_proxy_host' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_proxy_host' . '" id="' . $this->option_name . '_proxy_host' . '" value="' . $proxy_host . '"> ';
	}

	/**
	 * Render the Proxy port input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_proxy_port_cb() {
		$proxy_port = get_option( $this->option_name . '_proxy_port' );
		echo '<input class="code" type="text" name="' . $this->option_name . '_proxy_port' . '" id="' . $this->option_name . '_proxy_port' . '" value="' . $proxy_port . '"> ';
	}


	/**
	 * Register the options page
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_register_menu_pages() {


		add_menu_page(
			__( 'Releasekalender', 'rijksreleasekalender' ),
			__( 'Releasekalender', 'rijksreleasekalender' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'rijksreleasekalender_main_page' ),
			'dashicons-calendar'
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Releasekalender', 'rijksreleasekalender' ),
			__( 'Releasekalender', 'rijksreleasekalender' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'rijksreleasekalender_main_page' )
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Releasekalender instellingen', 'rijksreleasekalender' ),
			__( 'Instellingen', 'rijksreleasekalender' ),
			'manage_options',
			$this->plugin_name . '-instellingen',
			array( $this, 'rijksreleasekalender_options_page' )
		);
	}

	/**
	 * Render text for the general section
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_general_cb() {
		echo '<p>' . __( 'Algemene instellingen', 'rijksreleasekalender' ) . '</p>';
	}

	/**
	 * Render text for the connection section
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_connection_cb() {
		echo '<p>' . __( 'Verbindingsinstellingen', 'rijksreleasekalender' ) . '</p>';
	}

	/**
	 * Render the main page
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_main_page() {
		include_once 'partials/rijksreleasekalender-admin-main-display.php';
	}

	/**
	 * Render the options page
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_options_page() {
		include_once 'partials/rijksreleasekalender-admin-options-display.php';
	}

	/**
	 * Register Voorziening CPT
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_register_cpt_voorziening() {

		$labels = array(
			'name'               => _x( 'Voorzieningen', 'rijksreleasekalender' ),
			'singular_name'      => _x( 'Voorziening', 'rijksreleasekalender' ),
			'add_new'            => _x( 'Nieuwe toevoegen', 'rijksreleasekalender' ),
			'add_new_item'       => _x( 'Nieuwe voorziening toevoegen', 'rijksreleasekalender' ),
			'edit_item'          => _x( 'Bewerk voorziening voorziening', 'rijksreleasekalender' ),
			'new_item'           => _x( 'Nieuwe voorziening', 'rijksreleasekalender' ),
			'view_item'          => _x( 'Bekijk voorziening', 'rijksreleasekalender' ),
			'search_items'       => _x( 'Zoek voorzieningen', 'rijksreleasekalender' ),
			'not_found'          => _x( 'Geen voorzieningen gevonden', 'rijksreleasekalender' ),
			'not_found_in_trash' => _x( 'Geen voorzieningen gevonden in prullenbak', 'rijksreleasekalender' ),
			'parent_item_colon'  => _x( 'Hoofd voorziening:', 'rijksreleasekalender' ),
			'menu_name'          => _x( 'Voorzieningen', 'rijksreleasekalender' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'supports'            => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields',
				'revisions',
				'page-attributes'
			),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-portfolio',
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post'
		);

		register_post_type( 'voorziening', $args );
	}

	/**
	 * Register Voorziening Groep taxonomy
	 *
	 * @since    1.0.0
	 */
	function rijksreleasekalender_register_voorziening_groep() {
		register_taxonomy(
			'voorziening-groep',
			'voorziening',
			array(
				'labels'        => array(
					'name'          => __( 'Groep', 'rijksreleasekalender' ),
					'add_new_item'  => __( 'Groep toevoegen', 'rijksreleasekalender' ),
					'new_item_name' => __( 'Nieuwe groep', 'rijksreleasekalender' )
				),
				'show_ui'       => true,
				'show_tagcloud' => false,
				'hierarchical'  => true,
				'rewrite'       => true,
			)
		);
	}

	/**
	 * Register Product CPT
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_register_cpt_product() {

		$labels = array(
			'name'               => _x( 'Producten', 'rijksreleasekalender' ),
			'singular_name'      => _x( 'Product', 'rijksreleasekalender' ),
			'add_new'            => _x( 'Nieuwe toevoegen', 'rijksreleasekalender' ),
			'add_new_item'       => _x( 'Nieuw product toevoegen', 'rijksreleasekalender' ),
			'edit_item'          => _x( 'Bewerk product', 'rijksreleasekalender' ),
			'new_item'           => _x( 'Nieuw product', 'rijksreleasekalender' ),
			'view_item'          => _x( 'Bekijk product', 'rijksreleasekalender' ),
			'search_items'       => _x( 'Zoek producten', 'rijksreleasekalender' ),
			'not_found'          => _x( 'Geen producten gevonden', 'rijksreleasekalender' ),
			'not_found_in_trash' => _x( 'Geen producten gevonden in prullenbak', 'rijksreleasekalender' ),
			'parent_item_colon'  => _x( 'Hoofd product:', 'rijksreleasekalender' ),
			'menu_name'          => _x( 'Producten', 'rijksreleasekalender' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'supports'            => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields',
				'revisions',
				'page-attributes'
			),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-archive',
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post'
		);

		register_post_type( 'product', $args );
	}

	/**
	 * Register Product CPT
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_register_cpt_release() {

		$labels = array(
			'name'               => _x( 'Releases', 'rijksreleasekalender' ),
			'singular_name'      => _x( 'Release', 'rijksreleasekalender' ),
			'add_new'            => _x( 'Nieuwe toevoegen', 'rijksreleasekalender' ),
			'add_new_item'       => _x( 'Nieuwe release toevoegen', 'rijksreleasekalender' ),
			'edit_item'          => _x( 'Bewerk release', 'rijksreleasekalender' ),
			'new_item'           => _x( 'Nieuwe release', 'rijksreleasekalender' ),
			'view_item'          => _x( 'Bekijk release', 'rijksreleasekalender' ),
			'search_items'       => _x( 'Zoek releases', 'rijksreleasekalender' ),
			'not_found'          => _x( 'Geen releases gevonden', 'rijksreleasekalender' ),
			'not_found_in_trash' => _x( 'Geen releases gevonden in prullenbak', 'rijksreleasekalender' ),
			'parent_item_colon'  => _x( 'Hoofd release:', 'rijksreleasekalender' ),
			'menu_name'          => _x( 'Releases', 'rijksreleasekalender' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'supports'            => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields',
				'revisions',
				'page-attributes'
			),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-upload',
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post'
		);

		register_post_type( 'release', $args );
	}

	/**
	 * Do the synchronisation with remote API
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_do_sync() {
		//TODO retrieve and store voorzieningen, producten, releases, set start and end of sync

		$_step = array_key_exists( 'step', $_POST ) ? intval( $_POST[ 'step' ] ) : 0;

		$author_id           = get_option( $this->option_name . '_author_id' );

		switch ( $_step ) {
			case 0:
				$post_type           = 'voorziening';
				$voorzieningen       = $this->rijksreleasekalender_api_get( 'bouwstenen' );
				$voorzieningen_count = $this->rijksreleasekalender_count_api_objects( $voorzieningen );
				$messages[]          = date('H:i:s') . ' - ' . __( 'Aantal voorzieningen: ', 'rijksreleasekalender' ) . $voorzieningen_count;
				if ( 0 < $voorzieningen_count ) {

					$num = 0;
					foreach ( $voorzieningen->records as $voorziening ) {
						$num ++;
						$messages[] = '<strong>' . $num . '. ' . $voorziening->naam . '</strong>';

						$voorz_post_args = array(
							'post_author'    => $author_id,
							'post_content'   => $voorziening->beschrijving,
							'post_title'     => $voorziening->naam,
							'post_status'    => 'draft',
							'post_type'      => $post_type,
							'comment_status' => 'closed',
							'ping_status'    => 'closed'
						);
						// check if post already exists
						// check for voorziening ID, since this is (or should be) fixed.

						// todo make a function of this
						$voorz_query_args = array(
							'post_type'  => $post_type,
							'meta_key'   => 'voorziening_id',
							'meta_value' => $voorziening->id
						);
						$voorz_query      = new WP_Query( $voorz_query_args );

						if ( $voorz_query->have_posts() ) {
							// post exists
							$voorz_query->the_post();
							// store ID for future use
							$voorziening_post_id = get_the_ID();
							$messages[]          = date('H:i:s') . ' - ' . __( 'Voorziening gevonden met id: ', 'rijksreleasekalender' ) .
							                       $voorziening->id .
							                       ' (post_id: ' . $voorziening_post_id . ') ' .
							                       __( 'en titel: ', 'rijksreleasekalender' ) .
							                       get_the_title();
							$voorziening_exists  = true;
						} else {
							$voorziening_exists = false;
						}

						if ( ! $voorziening_exists ) {

							// post does not exist - so let's create it.
							$voorziening_post_id = wp_insert_post( $voorz_post_args, true );
							if ( $voorziening_post_id > 0 ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Voorziening aangemaakt: ', 'rijksreleasekalender' ) . $voorziening->naam . '(post_id: ' . $voorziening_post_id . ')';

								// add custom fields
								// todo make a function for this
								$eigenaar_organisatie = array(
									'id'      => $voorziening->eigenaarOrganisatie->id,
									'naam'    => $voorziening->eigenaarOrganisatie->naam,
									'website' => $voorziening->eigenaarOrganisatie->website,
									'updated' => $voorziening->eigenaarOrganisatie->updated
								);

								$eigenaar_contact = array(
									'id'          => $voorziening->eigenaarContact->id,
									'naam'        => $voorziening->eigenaarContact->naam,
									'organisatie' => array(
										'id'      => $voorziening->eigenaarContact->organisatie->id,
										'naam'    => $voorziening->eigenaarContact->organisatie->naam,
										'website' => $voorziening->eigenaarContact->organisatie->website,
										'updated' => $voorziening->eigenaarContact->organisatie->updated,
									)
								);

								// add all fields to array

								$custom_field_array = array(
									'voorziening_id'                  => $voorziening->id,
									'voorziening_website'             => $voorziening->website,
									'voorziening_aantekeningen'       => $voorziening->aantekeningen,
									'voorziening_updated'             => $voorziening->updated,
									'voorziening_eigenaarOrganisatie' => $eigenaar_organisatie,
									'voorziening_eigenaarContact'     => $eigenaar_contact
								);

								foreach ( $custom_field_array as $key => $value ) {
									update_post_meta( $voorziening_post_id, $key, $value );
								}
							} else {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Fout bij aanmaken voorziening: ', 'rijksreleasekalender' ) . $voorziening->naam . '(WP_Error: ' . $voorziening_post_id->get_error_message() . ')';
							}


						} else {
							// post exists - store all values in a temp custom field
							// var to check of we need to continue with the sync after temp storing new data.
							$continue = true;

							// add post ID to post_args
							$voorz_post_args[ 'ID' ] = $voorziening_post_id;
							// todo recreate post_name in case this has been changed. Use: sanitize_title_with_dashes()

							// store custom fields

							$eigenaar_organisatie = array(
								'id'      => $voorziening->eigenaarOrganisatie->id,
								'naam'    => $voorziening->eigenaarOrganisatie->naam,
								'website' => $voorziening->eigenaarOrganisatie->website,
								'updated' => $voorziening->eigenaarOrganisatie->updated
							);

							$eigenaar_contact = array(
								'id'          => isset($voorziening->eigenaarContact->id) ? $voorziening->eigenaarContact->id : '',
								'naam'        => isset($voorziening->eigenaarContact->naam) ? $voorziening->eigenaarContact->naam : '',
								'organisatie' => array(
									'id'      => isset($voorziening->eigenaarContact->organisatie->id) ? $voorziening->eigenaarContact->organisatie->id : '',
									'naam'    => isset($voorziening->eigenaarContact->organisatie->naam) ? $voorziening->eigenaarContact->organisatie->naam : '',
									'website' => isset($voorziening->eigenaarContact->organisatie->website) ? $voorziening->eigenaarContact->organisatie->website : '',
									'updated' => isset($voorziening->eigenaarContact->organisatie->updated) ? $voorziening->eigenaarContact->organisatie->updated : '',
								)
							);

							// add all fields to array

							$custom_field_array = array(
								'voorziening_id'                  => $voorziening->id,
								'voorziening_website'             => $voorziening->website,
								'voorziening_aantekeningen'       => $voorziening->aantekeningen,
								'voorziening_updated'             => $voorziening->updated,
								'voorziening_eigenaarOrganisatie' => $eigenaar_organisatie,
								'voorziening_eigenaarContact'     => $eigenaar_contact
							);

							$post_array[ 'args' ]          = $voorz_post_args;
							$post_array[ 'custom_fields' ] = $custom_field_array;

							// store new values in temp meta field.
							$meta_result = update_post_meta( $voorziening_post_id, 'temp_post_array', $post_array );
							if ( $meta_result ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Voorziening tijdelijk opgeslagen, post_id: ', 'rijksreleasekalender' ) . $voorziening_post_id;
							} else {
								$messages[]          = date('H:i:s') . ' - ' . __( 'FOUT - Voorziening niet tijdelijk opgeslagen, post_id: ', 'rijksreleasekalender' ) . $voorziening_post_id;
								$continue   = false;
							}
						}
						if ( $continue ) {
							// we may save the new data.
							$result = $this->rijksreleasekalender_update_post( $voorziening_post_id, $post_type, $post_array );
							if ( ( $result ) && ( ! is_wp_error( $result ) ) ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Voorziening bijgewerkt, post_id: ', 'rijksreleasekalender' ) . $result;
								// remove temp meta fields
								$messages[] = $this->rijksreleasekalender_delete_post_meta( $voorziening_post_id, 'temp_post_array' );

							}

						} else {
							// only remove the temp meta fields
							$messages[] = $this->rijksreleasekalender_delete_post_meta( $voorziening_post_id, 'temp_post_array' );
						}
					}
				} else {
					$messages[]          = date('H:i:s') . ' - ' . __( 'Geen voorzieningen gevonden...', 'rijksreleasekalender' );
				}

				$_step ++; // next step
				break;

			case 1:
				$post_type       = 'product';
				$producten       = $this->rijksreleasekalender_api_get( 'producten' );
				$producten_count = $this->rijksreleasekalender_count_api_objects( $producten );

				$messages[]          = date('H:i:s') . ' - ' . __( 'Aantal producten: ', 'rijksreleasekalender' ) . $producten_count;

				
				if ( 0 < $producten_count ) {
					$num = 0;
					foreach ( $producten->records as $product ) {
						$num ++;
						$messages[] = '<strong>' . $num . '. ' . $product->naam . '</strong>';

            // set publish for product to 'publish'
						$product_post_args = array(
							'post_author'    => $author_id,
							'post_content'   => $product->beschrijving,
							'post_title'     => $product->naam,
							'post_status'    => 'publish',
							'post_type'      => $post_type,
							'comment_status' => 'closed',
							'ping_status'    => 'closed'
						);
						// check if post already exists
						// check for product ID, since this is (or should be) fixed.

						// todo make a function of this
						$product_query_args = array(
							'post_type'  => $post_type,
							'meta_key'   => 'product_id',
							'meta_value' => $product->id
						);
						$prod_query         = new WP_Query( $product_query_args );

						if ( $prod_query->have_posts() ) {
							// post exists
							$prod_query->the_post();
							// store ID for future use
							$product_post_id = get_the_ID();
							$messages[]      = __( 'Product gevonden met id: ', 'rijksreleasekalender' ) .
							                   $product->id .
							                   ' (post_id: ' . $product_post_id . ') ' .
							                   __( 'en titel: ', 'rijksreleasekalender' ) .
							                   get_the_title();
							$product_exists  = true;
						} else {
							$product_exists = false;
						}

						if ( ! $product_exists ) {

							// post does not exist - so let's create it.
							$product_post_id = wp_insert_post( $product_post_args, true );
							if ( $product_post_id > 0 ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Product aangemaakt: ', 'rijksreleasekalender' ) . $product->naam . '(post_id: ' . $product_post_id . ')';

								// add custom fields
								// todo make a function for this

								$product_voorziening = array(
									'id'   => $product->bouwsteen->id,
									'naam' => $product->bouwsteen->naam
								);

								$product_productmanager = array(
									'id'          => $product->productmanager->id,
									'naam'        => $product->productmanager->naam,
									'organisatie' => array(
										'id'      => $product->productmanager->organisatie->id,
										'naam'    => $product->productmanager->organisatie->naam,
										'website' => $product->productmanager->organisatie->website,
										'updated' => $product->productmanager->organisatie->updated
									),
								);

								$product_contact_opdrachtgever = array(
									'id'          => $product->contactOpdrachtgever->id,
									'naam'        => $product->contactOpdrachtgever->naam,
									'organisatie' => array(
										'id'      => $product->contactOpdrachtgever->organisatie->id,
										'naam'    => $product->contactOpdrachtgever->organisatie->naam,
										'website' => $product->contactOpdrachtgever->organisatie->website,
										'updated' => $product->contactOpdrachtgever->organisatie->updated
									)
								);
								$product_opdrachtgever         = array(
									'id'      => $product->opdrachtgever->id,
									'naam'    => $product->opdrachtgever->naam,
									'website' => $product->opdrachtgever->website,
									'updated' => $product->opdrachtgever->updated
								);

								$product_aanbieder = array(
									'id'      => $product->aanbieder->id,
									'naam'    => $product->aanbieder->naam,
									'website' => $product->aanbieder->website,
									'updated' => $product->aanbieder->updated
								);
								// multiple producttypen may exist
								$product_producttypen = array();

  							foreach ( $product->producttypen as $product_product_type ) {
  								$product_producttypen[] = array(
    								'id'           => $product_product_type->id,
    								'naam'         => $product_product_type->naam,
    								'omschrijving' => $product_product_type->omschrijving,
    								'updated'      => $product_product_type->updated,
  								);
  							}

								// add all fields to array

								$product_custom_field_array = array(
									'product_id'                    => $product->id,
									'product_referentieProduct'     => $product->referentieProduct,
									'product_datumIngebruikname'    => $product->datumIngebruikname,
									'product_datumUitfasering'      => $product->datumUitfasering,
									'product_doelgroep'             => $product->doelgroep,
									'product_verwijzing'            => $product->verwijzing,
									'product_goedgekeurd'           => $product->goedgekeurd,
									'product_updated'               => $product->updated,
									'product_voorziening'           => $product_voorziening,
									'product_productmanager'        => $product_productmanager,
									'product_contact_opdrachtgever' => $product_contact_opdrachtgever,
									'product_opdrachtgever'         => $product_opdrachtgever,
									'product_aanbieder'             => $product_aanbieder,
									'product_producttypen'          => $product_producttypen
								);

								foreach ( $product_custom_field_array as $key => $value ) {
									update_post_meta( $product_post_id, $key, $value );
								}
							} else {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Fout bij aanmaken product: ', 'rijksreleasekalender' ) . $product->naam . '(WP_Error: ' . $product_post_id->get_error_message() . ')';
							}


						} else {
							// post exists - store all values in a temp custom field
							// var to check of we need to continue with the sync after temp storing new data.
							$continue = true;

							// add post ID to post_args
							$product_post_args[ 'ID' ] = $product_post_id;
							// todo recreate post_name in case this has been changed. Use: sanitize_title_with_dashes()

							// store custom fields
							// todo make a function for this

							$product_voorziening = array(
								'id'   => $product->bouwsteen->id,
								'naam' => $product->bouwsteen->naam
							);

							$product_productmanager = array(
								'id'          => $product->productmanager->id,
								'naam'        => $product->productmanager->naam,
								'organisatie' => array(
									'id'      => $product->productmanager->organisatie->id,
									'naam'    => $product->productmanager->organisatie->naam,
									'website' => $product->productmanager->organisatie->website,
									'updated' => $product->productmanager->organisatie->updated
								),
							);

							$product_contact_opdrachtgever = array(
								'id'          => isset($product->contactOpdrachtgever->id) ? $product->contactOpdrachtgever->id : '',
								'naam'        => isset($product->contactOpdrachtgever->naam) ? $product->contactOpdrachtgever->naam : '',
								'organisatie' => array(
  								'id'        => isset($product->contactOpdrachtgever->organisatie->id) ? $product->contactOpdrachtgever->organisatie->id : '',
  								'naam'      => isset($product->contactOpdrachtgever->organisatie->naam) ? $product->contactOpdrachtgever->organisatie->naam : '',
  								'website'   => isset($product->contactOpdrachtgever->organisatie->website) ? $product->contactOpdrachtgever->organisatie->website : '',
  								'updated'   => isset($product->contactOpdrachtgever->organisatie->updated) ? $product->contactOpdrachtgever->organisatie->updated : '',
								)
							);
							$product_opdrachtgever         = array(
								'id'      => $product->opdrachtgever->id,
								'naam'    => $product->opdrachtgever->naam,
								'website' => $product->opdrachtgever->website,
								'updated' => $product->opdrachtgever->updated
							);

							$product_aanbieder = array(
								'id'      => $product->aanbieder->id,
								'naam'    => $product->aanbieder->naam,
								'website' => $product->aanbieder->website,
								'updated' => $product->aanbieder->updated
							);
							// multiple producttypen may exist
							$product_producttypen = array();

							foreach ( $product->producttypen as $product_product_type ) {
								$product_producttypen[] = array(
  								'id'           => $product_product_type->id,
  								'naam'         => $product_product_type->naam,
  								'omschrijving' => $product_product_type->omschrijving,
  								'updated'      => $product_product_type->updated,
								);
							}

							// add all fields to array

							$product_custom_field_array = array(
								'product_id'                    => $product->id,
								'product_referentieProduct'     => $product->referentieProduct,
								'product_datumIngebruikname'    => $product->datumIngebruikname,
								'product_datumUitfasering'      => $product->datumUitfasering,
								'product_doelgroep'             => $product->doelgroep,
								'product_verwijzing'            => $product->verwijzing,
								'product_goedgekeurd'           => $product->goedgekeurd,
								'product_updated'               => $product->updated,
								'product_voorziening'           => $product_voorziening,
								'product_productmanager'        => $product_productmanager,
								'product_contact_opdrachtgever' => $product_contact_opdrachtgever,
								'product_opdrachtgever'         => $product_opdrachtgever,
								'product_aanbieder'             => $product_aanbieder,
								'product_producttypen'          => $product_producttypen
							);

							$product_post_array[ 'args' ]          = $product_post_args;
							$product_post_array[ 'custom_fields' ] = $product_custom_field_array;

							// store new values in temp meta field.
							$meta_result = update_post_meta( $product_post_id, 'temp_post_array', $product_post_array );
							if ( $meta_result ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Product tijdelijk opgeslagen, post_id: ', 'rijksreleasekalender' ) . $product_post_id;
							} else {
								$messages[]          = date('H:i:s') . ' - ' . __( 'FOUT - Product niet tijdelijk opgeslagen, post_id: ', 'rijksreleasekalender' ) . $product_post_id;
								$continue   = false;
							}
						}
						if ( $continue ) {
							// we may save the new data.
							$result = $this->rijksreleasekalender_update_post( $product_post_id, $post_type, $product_post_array );
							if ( ( $result ) && ( ! is_wp_error( $result ) ) ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Product bijgewerkt, post_id: ', 'rijksreleasekalender' ) . $result;
								// remove temp meta fields
								$messages[] = $this->rijksreleasekalender_delete_post_meta( $product_post_id, 'temp_post_array' );

							}

						} else {
							// only remove the temp meta fields
							$messages[] = $this->rijksreleasekalender_delete_post_meta( $product_post_id, 'temp_post_array' );
						}
					}
				} else {
					$messages[]          = date('H:i:s') . ' - ' . __( 'Geen producten gevonden...', 'rijksreleasekalender' );
				}
				$_step ++; // next step
				break;

			case 2:
				$post_type      = 'release';
				$releases       = $this->rijksreleasekalender_api_get( 'releases' );
				$releases_count = $this->rijksreleasekalender_count_api_objects( $releases );
				$messages[]     = date('H:i:s') . ' - ' . __( 'Aantal releases: ', 'rijksreleasekalender' ) . $releases_count;

				if ( 0 < $releases_count ) {
					$num = 0;
					foreach ( $releases->records as $release ) {
						$num ++;
						$messages[] = '<strong>' . $num . '. ' . $release->naam . '</strong>';
						$release_post_content = $release->nieuweFunctionaliteiten;
						// some releases don't have a value, so set it to an empty string
						if ( $release_post_content === null ) {
							$release_post_content = '';
						}
						

            $postdate = date( 'Y-m-d H:i:s', strtotime( $release->updated ) );
						
            // set publish for release to 'publish'
						$release_post_args = array(
							'post_author'    => $author_id,
							'post_content'   => $release_post_content,
							'post_title'     => $release->naam,
							'post_status'    => 'publish',
							'post_type'      => $post_type,
							'comment_status' => 'closed',
							'ping_status'    => 'closed',
							'post_date'      => $postdate,
							'post_date_gmt'  => get_date_from_gmt( $postdate )
						);
						// check if post already exists
						// check for release ID, since this is (or should be) fixed.

						// todo make a function of this
						$release_query_args = array(
							'post_type'  => $post_type,
							'meta_key'   => 'release_id',
							'meta_value' => $release->id
						);
						$rel_query          = new WP_Query( $release_query_args );

						if ( $rel_query->have_posts() ) {
							// post exists
							$rel_query->the_post();
							// store ID for future use
							$release_post_id = get_the_ID();
							$messages[]      = date('H:i:s') . ' - ' . __( 'Release gevonden met id: ', 'rijksreleasekalender' ) .
							                   $release->id .
							                   ' (post_id: ' . $release_post_id . ') ' .
							                   __( 'en titel: ', 'rijksreleasekalender' ) .
							                   get_the_title();
							$release_exists  = true;
						} else {
							$release_exists = false;
						}

						if ( ! $release_exists ) {

							// post does not exist - so let's create it.
							$release_post_id = wp_insert_post( $release_post_args, true );
							
							if ( $release_post_id > 0 ) {
  								$messages[]          = date('H:i:s') . ' - ' . __( 'Release aangemaakt: ', 'rijksreleasekalender' ) . $release->naam . '(post_id: ' . $release_post_id . ')';

								// add custom fields
								// todo make a function for this

								$release_product = array(
									'id'          => $release->product->id,
									'naam'        => $release->product->naam,
									'voorziening' => array(
										'id'   => $release->product->bouwsteen->id,
										'naam' => $release->product->bouwsteen->naam
									)
								);

								$release_release_status = array(
									'id'           => $release->releaseStatus->id,
									'naam'         => $release->releaseStatus->naam,
									'omschrijving' => $release->releaseStatus->omschrijving
								);

								$release_release_marge = array(
									'id'           => $release->releaseMarge->id,
									'naam'         => $release->releaseMarge->naam,
									'omschrijving' => $release->releaseMarge->omschrijving
								);

								// add all fields to array

								$release_custom_field_array = array(
									'release_id'                      => $release->id,
									'release_releasedatum'            => $release->releasedatum,
									'release_releasedatum_translated' => $this->rijksreleasekalender_format_a_date( $release->releasedatum ),
									'release_updated'                 => $release->updated,
									'release_updated_translated'      => $this->rijksreleasekalender_format_a_date( $release->updated ),
									'release_aandachtspunten'         => $release->aandachtspunten,
									'release_nieuwefunctionaliteiten' => $release->nieuweFunctionaliteiten,
									'release_website'                 => $release->website,
									'release_product'                 => $release_product,
									'release_release_status'          => $release_release_status,
									'release_release_marge'           => $release_release_marge
								);

								foreach ( $release_custom_field_array as $key => $value ) {
									update_post_meta( $release_post_id, $key, $value );
								}
							} else {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Fout bij aanmaken release: ', 'rijksreleasekalender' ) . $release->naam . '(WP_Error: ' . $release_post_id->get_error_message() . ')';
							}


						} else {
							// post exists - store all values in a temp custom field
							// var to check of we need to continue with the sync after temp storing new data.
							$continue = true;

							// add post ID to post_args
							$release_post_args[ 'ID' ] = $release_post_id;
							// todo recreate post_name in case this has been changed. Use: sanitize_title_with_dashes()

							// store custom fields
							// todo make a function for this

							$release_product        = array(
								'id'          => $release->product->id,
								'naam'        => $release->product->naam,
								'voorziening' => array(
									'id'   => $release->product->bouwsteen->id,
									'naam' => $release->product->bouwsteen->naam
								)
							);
							$release_release_status = array(
								'id'           => $release->releaseStatus->id,
								'naam'         => $release->releaseStatus->naam,
								'omschrijving' => $release->releaseStatus->omschrijving
							);

							$release_release_marge = array(
								'id'           => $release->releaseMarge->id,
								'naam'         => $release->releaseMarge->naam,
								'omschrijving' => $release->releaseMarge->omschrijving
							);
							
							// add all fields to array

							$release_custom_field_array = array(
								'release_id'                      => $release->id,
								'release_releasedatum'            => $release->releasedatum,
								'release_releasedatum_translated' => $this->rijksreleasekalender_format_a_date( $release->releasedatum ),
								'release_updated'                 => $release->updated,
								'release_updated_translated'      => $this->rijksreleasekalender_format_a_date( $release->updated ),
								'release_aandachtspunten'         => $release->aandachtspunten,
								'release_nieuwefunctionaliteiten' => $release->nieuweFunctionaliteiten,
								'release_website'                 => $release->website,
								'release_product'                 => $release_product,
								'release_release_status'          => $release_release_status,
								'release_release_marge'           => $release_release_marge
							);

							$messages[]          = date('H:i:s') . ' - ' . $release_post_id . __( ' bestaat al; publish date gezet op' ) . date( 'Y-m-d H:i:s', strtotime( $release->updated ) );


              // set post publish date to date last updated
              $postdate                           = date( 'Y-m-d H:i:s', strtotime( $release->updated ) );
              $release_post_args['post_date']     = $postdate;
              $release_post_args['post_date_gmt'] = get_date_from_gmt( strtotime( $release->updated ), 'Y-m-d H:i:s' );

							$release_post_array[ 'args' ]          = $release_post_args;
							$release_post_array[ 'custom_fields' ] = $release_custom_field_array;

							// store new values in temp meta field.
							$meta_result = update_post_meta( $release_post_id, 'temp_post_array', $release_post_array );
							if ( $meta_result ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Release tijdelijk opgeslagen, post_id: ', 'rijksreleasekalender' ) . $release_post_id;
							} else {
								$messages[]          = date('H:i:s') . ' - ' . __( 'FOUT - Product niet tijdelijk opgeslagen, post_id: ', 'rijksreleasekalender' ) . $release_post_id;
								$continue   = false;
							}
						}
						if ( $continue ) {
							// we may save the new data.
							$result = $this->rijksreleasekalender_update_post( $release_post_id, $post_type, $release_post_array );
							if ( ( $result ) && ( ! is_wp_error( $result ) ) ) {
								$messages[]          = date('H:i:s') . ' - ' . __( 'Product bijgewerkt, post_id: ', 'rijksreleasekalender' ) . $result;
								// remove temp meta fields
								$messages[] = $this->rijksreleasekalender_delete_post_meta( $release_post_id, 'temp_post_array' );

							}

						} else {
							// only remove the temp meta fields
							$messages[] = $this->rijksreleasekalender_delete_post_meta( $release_post_id, 'temp_post_array' );
						}
					}
				} else {
					$messages[]          = date('H:i:s') . ' - ' . __( 'Geen producten gevonden...', 'rijksreleasekalender' );
				}

				$_step ++;
				break;
		}


		// todo bij fout stoppen en foutmelding
		if ( 3 == $_step ) {
			$_result    = 'done';
			$messages[] = '<h2 style="background: green; color: white;">' . date('H:i:s') . ' - ' . __( 'Sync klaar!', 'rijksreleasekalender' ) . '</h2>';
		} else {
			$_result = $_step;
		}

		// todo if we are doing a cron, do not do this :)
		wp_send_json( array(
			'result'   => $_result,
			'step'     => $_step,
			'messages' => $messages
		) );
		exit();


	}

	/**
	 * Do the API call
	 *
	 * @param      string $api_parameters is a string containing the api parameters
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_api_get( $api_parameters ) {
		$api_url   = get_option( $this->option_name . '_restapi_url' );
		$username  = get_option( $this->option_name . '_restapi_user' );
		$password  = get_option( $this->option_name . '_restapi_pwd' );
		$apikey    = get_option( $this->option_name . '_restapi_key' );
		$format    = '.json'; // format to retrieve
		$page_size = 600; //maximum number of records in response
		$url       = $api_url . $api_parameters . $format . '?page_size=' . $page_size . '&api-key=' . $apikey;
		// todo connect through proxy

		// if username is empty, use API key
		if ( ! $username ) {
			// send request to server
			$ch = curl_init( $url );
			// save response in a variable from server, set headers;
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			// get response
			$response = curl_exec( $ch );

			// decode
			return ( json_decode( $response ) );

		}
	}

	/**
	 * Count number of records in API call
	 * @var    object $json_object is a JSON ojbject containing the API call response
	 * @return  string  $totalcount The total count of objects in the api response
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_count_api_objects( $json_object ) {
		$totalcount = $json_object->totalCount;

		return $totalcount;
	}

	/**
	 * Store temp meta field as post and meta fields
	 * @var    int    $post_id  post ID of post to update
	 * @var    string $post_type
	 * @var    array  $all_args holding all post args and meta key/values
	 *
	 * @return int post_id of changed post
	 * @return array $messages holding the WP_Error
	 *
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_update_post( $post_id, $post_type, $all_args ) {
		$post_args = array(
			'ID'             => $all_args[ 'args' ][ 'ID' ],
			'post_author'    => $all_args[ 'args' ][ 'post_author' ],
			'post_content'   => $all_args[ 'args' ][ 'post_content' ],
			'post_title'     => $all_args[ 'args' ][ 'post_title' ],
			'post_status'    => get_post_status(),
			'post_type'      => $post_type,
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		);

		$post_id = wp_update_post( $post_args, true );
		if ( is_wp_error( $post_id ) ) {
			$errors = $post_id->get_error_messages();
			foreach ( $errors as $error ) {
				$messages[] = $error;
			}

			return $messages;

		} else {
			// post is updated now do the meta fields
			switch ( $post_type ) {
				case 'voorziening':
					//set voorziening meta fields
					$meta_fields = array(
						'voorziening_id'                  => $all_args[ 'custom_fields' ][ 'voorziening_id' ],
						'voorziening_website'             => $all_args[ 'custom_fields' ][ 'voorziening_website' ],
						'voorziening_aantekeningen'       => $all_args[ 'custom_fields' ][ 'voorziening_aantekeningen' ],
						'voorziening_updated'             => $all_args[ 'custom_fields' ][ 'voorziening_updated' ],
						'voorziening_eigenaarOrganisatie' => maybe_unserialize( $all_args[ 'custom_fields' ][ 'voorziening_eigenaarOrganisatie' ] ),
						'voorziening_eigenaarContact'     => maybe_unserialize( $all_args[ 'custom_fields' ][ 'voorziening_eigenaarContact' ] )
					);
					break;
				case 'product':
					// set product meta fields
					$meta_fields = array(
						'product_id'                      => $all_args[ 'custom_fields' ][ 'product_id' ],
						'product_referentieProduct'       => $all_args[ 'custom_fields' ][ 'product_referentieProduct' ],
						'product_datumIngebruikname'      => $all_args[ 'custom_fields' ][ 'product_datumIngebruikname' ],
						'product_datumUitfasering'        => $all_args[ 'custom_fields' ][ 'product_datumUitfasering' ],
						'product_doelgroep'               => $all_args[ 'custom_fields' ][ 'product_doelgroep' ],
						'product_verwijzing'              => $all_args[ 'custom_fields' ][ 'product_verwijzing' ],
						'product_goedgekeurd'             => $all_args[ 'custom_fields' ][ 'product_goedgekeurd' ],
						'product_updated'                 => $all_args[ 'custom_fields' ][ 'product_updated' ],
						'product_voorziening'             => maybe_unserialize( $all_args[ 'custom_fields' ][ 'product_voorziening' ] ),
						'product_productmanager'          => maybe_unserialize( $all_args[ 'custom_fields' ][ 'product_productmanager' ] ),
						'product_contact_opdrachtgever'   => maybe_unserialize( $all_args[ 'custom_fields' ][ 'product_contact_opdrachtgever' ] ),
						'product_opdrachtgever'           => maybe_unserialize( $all_args[ 'custom_fields' ][ 'product_opdrachtgever' ] ),
						'product_aanbieder'               => maybe_unserialize( $all_args[ 'custom_fields' ][ 'product_aanbieder' ] ),
						'product_producttypen'            => maybe_unserialize( $all_args[ 'custom_fields' ][ 'product_producttypen' ] )
					);
					break;
				case 'release':
					// set release meta fields
					$meta_fields = array(
						'release_id'                      => $all_args[ 'custom_fields' ][ 'release_id' ],
						'release_releasedatum'            => $all_args[ 'custom_fields' ][ 'release_releasedatum' ],
						'release_releasedatum_translated' => $this->rijksreleasekalender_format_a_date( $all_args[ 'custom_fields' ][ 'release_releasedatum' ] ),
						'release_updated'                 => $all_args[ 'custom_fields' ][ 'release_updated' ],
						'release_updated_translated'      => $this->rijksreleasekalender_format_a_date( $all_args[ 'custom_fields' ][ 'release_updated' ] ),
						'release_aandachtspunten'         => $all_args[ 'custom_fields' ][ 'release_aandachtspunten' ],
						'release_nieuwefunctionaliteiten' => $all_args[ 'custom_fields' ][ 'release_nieuwefunctionaliteiten' ],
						'release_website'                 => $all_args[ 'custom_fields' ][ 'release_website' ],
						'release_product'                 => maybe_unserialize( $all_args[ 'custom_fields' ][ 'release_product' ] ),
						'release_release_status'          => maybe_unserialize( $all_args[ 'custom_fields' ][ 'release_release_status' ] ),
						'release_release_marge'           => maybe_unserialize( $all_args[ 'custom_fields' ][ 'release_release_marge' ] )
					);
					break;

			}


			foreach ( $meta_fields as $meta_key => $meta_value ) {

				update_post_meta( $post_id, $meta_key, $meta_value );
			}

			return $post_id;
		}
	}

	/**
	 * Remove temp values and return message
	 * @var    int    $post_id post ID of post to update
	 * @var    string $meta_key
	 *
	 * @return array $messages holding the WP_Error
	 *
	 *
	 * @since    1.0.0
	 */
	public function rijksreleasekalender_delete_post_meta( $post_id, $meta_key ) {
		$delete_result = delete_post_meta( $post_id, $meta_key );

		if ( $delete_result ) {
			$messages[] = date('H:i:s') . ' - ' . __('$messages[]          = time() . ' - ' .ijderd van post_id:', 'rijksreleasekalender' ) . ' ' . $post_id;
		} else {
			$messages[] = date('H:i:s') . ' - ' . __('FOUT - Bij verwijderen van tijdelijke data van post_id: ', 'rijksreleasekalender' ) . ' ' . $post_id;
		}

		return $messages;
	}
	

	/**
	 * Formats a date and corrects for GMT etc
	 * @var    string    $datestring date string to format
	 *
	 * @return string
	 *
	 *
	 * @since    1.0.0
	 */
  public function rijksreleasekalender_format_a_date( $datestring = '' ) {
    
    if ( $datestring ) {
      $datestring = date( 'Y-m-d H:i:s', strtotime( $datestring ) );
      $datestring = get_date_from_gmt( $datestring );
      $datestring = strtotime( $datestring );
    }

    return $datestring;
  }
	
	

} // end of class