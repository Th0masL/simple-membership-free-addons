<?php
/*
Plugin Name: Simple Membership Google reCAPTCHA Enterprise
Version: 1.5
Plugin URI: https://simple-membership-plugin.com/
Author: smp7, wp.insider, Muhammad Arslan
Author URI: https://simple-membership-plugin.com/
Description: This addon allows you to add Google reCAPTCHA Enterprise to the Simple Membership plugin
*/

//Direct access to this file is not permitted
if (!defined('ABSPATH')){
    exit; //Exit if accessed directly
}

define('SWPM_CAPTCHA_VER', '1.5');
define('SWPM_CAPTCHA_SITE_HOME_URL', home_url());
define('SWPM_CAPTCHA_PATH', dirname(__FILE__) . '/');
define('SWPM_CAPTCHA_URL', plugins_url('', __FILE__));
define('SWPM_CAPTCHA_DIRNAME', dirname(plugin_basename(__FILE__)));

require_once( 'classes/class.swpm-captcha-enterprise.php' );

add_action('plugins_loaded', "swpm_captcha_plugins_loaded");

function swpm_captcha_plugins_loaded() {
    new SwpmCaptcha();
}

//Add settings link in plugins listing page
function swpm_recaptcha_ent_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=simple_wp_membership_settings&tab=7">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'swpm_recaptcha_ent_add_settings_link', 10, 2 );