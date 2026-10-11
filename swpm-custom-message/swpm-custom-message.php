<?php
/*
Plugin Name: Simple Membership Custom Messages
Description: Simple Membership Addon to customize various content protection messages.
Plugin URI: https://simple-membership-plugin.com/simple-membership-custom-messages-addon/
Author: wp.insider
Author URI: https://simple-membership-plugin.com/
Version: 2.6
*/

//Slug - swpm_cm_

define('SWPM_CUSTOM_MSG_VERSION', '2.6' );
define('SWPM_CUSTOM_MSG_PATH', dirname(__FILE__) . '/');
define('SWPM_CUSTOM_MSG_URL', plugins_url('',__FILE__));
add_action('plugins_loaded', 'swpm_load_custom_message');
require_once(SWPM_CUSTOM_MSG_PATH . 'classes/class.swpm-custom-message.php');
require_once(SWPM_CUSTOM_MSG_PATH . 'classes/class.swpm-custom-message-settings.php');
function swpm_load_custom_message(){
    if (class_exists('SimpleWpMembership')) {
        new SwpmCustomMessage();
    }
}

//Add settings link in plugins listing page
function swpm_cm_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=swpm-custom-message">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'swpm_cm_add_settings_link', 10, 2 );
