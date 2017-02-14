<?php

/**
 * ICTU / WP Rijksreleasekalender plugin - rijksreleasekalender_template_release_info.php
 * ----------------------------------------------------------------------------------
 * Toont release info
 * ----------------------------------------------------------------------------------
 *
 * @author    Paul van Buuren
 * @license   GPL-2.0+
 * @version   1.0.1
 * @package   rijksreleasekalender
 * @desc.     Toont release info
 */


//========================================================================================================

add_action( 'genesis_entry_content', 'rijksreleasekalender_template_release_info', 15 );

remove_action( 'genesis_post_content', 'genesis_do_post_content' );
remove_action( 'genesis_entry_content', 'genesis_do_post_content' );


//========================================================================================================

genesis();

//========================================================================================================

function rijksreleasekalender_template_release_info() {

	$plugin_public = new rijksreleasekalender_Public(  );
  
  die( 'vaarwel!! ' . $this->plugin_name );
  
}


class rijksreleasekalender_Public_releasinfo extends rijksreleasekalender_Public {

	private $rijksreleasekalender;
	private $plugin_name;
	private $version;


	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

}

