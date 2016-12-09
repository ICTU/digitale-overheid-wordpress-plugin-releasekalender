<?php

/**
 * @link              http://nostromo.nl
 * @since             1.0.0
 * @package           rijksreleasekalender
 *
 * @wordpress-plugin
 * Plugin Name:       Rijksreleasekalender
 * Plugin URI:        http://nostromo.nl
 * Description:       Synchroniseert met Rijksrelease REST API en zorgt voor weergave
 * Version:           1.0.0
 * Author:            Marcel Bootsman
 * Author URI:        http://nostromo.nl/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rijksreleasekalender
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rijksreleasekalender-activator.php
 */
function activate_rijksreleasekalender() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rijksreleasekalender-activator.php';
	rijksreleasekalender_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rijksreleasekalender-deactivator.php
 */
function deactivate_rijksreleasekalender() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rijksreleasekalender-deactivator.php';
	rijksreleasekalender_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rijksreleasekalender' );
register_deactivation_hook( __FILE__, 'deactivate_rijksreleasekalender' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rijksreleasekalender.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rijksreleasekalender() {

	$plugin = new rijksreleasekalender();
	$plugin->run();

}
run_rijksreleasekalender();
