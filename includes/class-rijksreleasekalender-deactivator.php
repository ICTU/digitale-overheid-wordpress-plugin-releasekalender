<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://nostromo.nl
 * @since      1.0.0
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/includes
 * @author     Marcel Bootsman <marcel@nostromo.nl>
 */
class rijksreleasekalender_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// clear event hook when deactivating
		wp_clear_scheduled_hook('rijksreleasekalender_create_sync_schedule_hook');

	}

}
