<?php
/*
Plugin Name: SWPM Full Page Protection
Version: 2.2
Plugin URI: https://simple-membership-plugin.com/full-page-protection-addon-simple-membership/
Author: wp.insider
Author URI: https://simple-membership-plugin.com/
Description: Allows you to apply full page protection to posts, pages, custom post type posts. Protects the full post/page instead of just the post content.
*/

//slug - swpm_fpp

//Direct access to this file is not permitted
if (!defined('ABSPATH')){
    exit("Do not access this file directly.");
}

define('SWPM_FPP_PROTECTION_VER', '2.2');
define('SWPM_FPP_PROTECTION_PATH', dirname(__FILE__) . '/');
define('SWPM_FPP_PROTECTION_URL', plugins_url('', __FILE__));

include_once('swpm-full-page-protection-template.php');
include_once('classes/class.swpm-page-protection.php');
include_once('swpm-full-page-protection-admin-menu.php');

add_action('plugins_loaded', 'load_swpm_fpp_object');

function load_swpm_fpp_object() {
    new SWPM_FPP_Protection();
}

//Add user friendly settings link in the plugins menu of wp admin dashboard.
function swpm_fpp_add_settings_link($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $settings_link = '<a href="admin.php?page=swpm-fpp">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'swpm_fpp_add_settings_link', 10, 2);