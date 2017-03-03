<?php

/**
 * Provide a settings admin area view for the plugin
 *
 * @link       http://nostromo.nl
 * @since      1.0.0
 *
 * @package    rijksreleasekalender
 * @subpackage rijksreleasekalender/admin/partials
 */


// Call the schedule cron job method, $this is the _admin object

// But only do this when we have updated the settings
if ( isset( $_REQUEST[ 'settings-updated' ] ) && ($_REQUEST[ 'settings-updated' ] ) ) {
	$this->rijksreleasekalender_schedule_cron_job();
}


?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<form action="options.php" method="post">
	  <?php
	  settings_fields( $this->plugin_name );
	  do_settings_sections( $this->plugin_name );
	  submit_button();
	  ?>
	</form>
</div>