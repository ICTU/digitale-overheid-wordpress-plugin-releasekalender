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