<?php
/*
Plugin Name: Simple Membership Google reCAPTCHA V3
Version: 1.4
Plugin URI: https://simple-membership-plugin.com/simple-membership-and-google-recaptcha-integration/
Author: smp7, wp.insider
Author URI: https://simple-membership-plugin.com/
Description: This addon allows you to add Google reCAPTCHA V3 to your membership registration form/page.
*/

//slug - swpm_recaptcha_v3_

//Direct access to this file is not permitted
if (!defined('ABSPATH')) {
    exit; //Exit if accessed directly
}

define('SWPM_CAPTCHA_V3_VER', '1.4');
define('SWPM_CAPTCHA_V3_SITE_HOME_URL', home_url());
define('SWPM_CAPTCHA_V3_PATH', dirname(__FILE__) . '/');
define('SWPM_CAPTCHA_V3_URL', plugins_url('', __FILE__));
define('SWPM_CAPTCHA_V3_DIRNAME', dirname(plugin_basename(__FILE__)));

require_once('classes/class.swpm-captcha-v3.php');

add_action('plugins_loaded', "swpm_recaptcha_v3_plugin_loaded");

function swpm_recaptcha_v3_plugin_loaded(){
    new SwpmCaptchaV3();
}

//Add settings link in plugins listing page
function swpm_recaptcha_v3_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=simple_wp_membership_settings&tab=7">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'swpm_recaptcha_v3_add_settings_link', 10, 2 );


