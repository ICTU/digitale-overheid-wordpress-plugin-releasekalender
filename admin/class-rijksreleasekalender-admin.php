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
			__( 'REST-API gebruiker', 'rijksreleasekalender' ),
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
			$this->option_name . '_recent_max_age',
			__( 'Maximum leeftijd van item om als recent gewijzigd gezien te worden', 'rijksreleasekalender' ),
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
	 * Render the recent max age input for this plugin
	 *
	 * @since  1.0.0
	 */
	public function rijksreleasekalender_recent_max_age_cb() {
		$recent_max_age = get_option( $this->option_name . '_recent_max_age' );
		echo '<input class="regular-text code" type="text" name="' . $this->option_name . '_recent_max_age' . '" id="' . $this->option_name . '_recent_max_age' . '" value="' . $recent_max_age . '"> ';
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
		echo '<input class="code" type="text" name="' . $this->option_name . '_rss_size' . '" id="' . $this->option_name . '_rss_size' . '" value="' . $rss_size . '"> ';
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


} // end of class