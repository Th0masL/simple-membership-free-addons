<?php
/*
Plugin Name: Simple Membership Cloudflare Turnstile
Version: 1.3
Plugin URI: https://simple-membership-plugin.com/simple-membership-and-cloudflare-turnstile-integration-addon/
Author: smp7, wp.insider
Author URI: https://simple-membership-plugin.com/
Description: This addon allows you to add Cloudflare Turnstile to your membership registration and login form/page.
*/

//slug - swpm_cft_

//Direct access to this file is not permitted
if ( ! defined( 'ABSPATH' ) ) {
	exit; //Exit if accessed directly
}

define( 'SWPM_CFT_V3_VER', '1.3' );
define( 'SWPM_CFT_SITE_HOME_URL', home_url() );
define( 'SWPM_CFT_PATH', dirname( __FILE__ ) . '/' );
define( 'SWPM_CFT_URL', plugins_url( '', __FILE__ ) );
define( 'SWPM_CFT_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );

require_once SWPM_CFT_PATH . 'libs/Turnstile.php';
require_once SWPM_CFT_PATH . 'classes/class.swpm-cloudflare-turnstile.php';

function swpm_cft_plugin_loaded() {
	new SwpmCloudflareTurnstile();
}

add_action( 'plugins_loaded', "swpm_cft_plugin_loaded" );

//Add settings link in plugins listing page
function swpm_cft_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=simple_wp_membership_settings&tab=7">Settings</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

add_filter( 'plugin_action_links', 'swpm_cft_add_settings_link', 10, 2 );