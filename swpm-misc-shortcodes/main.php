<?php

/**
 * Plugin Name: SWPM Misc Shortcodes
 * Plugin URI: https://simple-membership-plugin.com/simple-membership-miscellaneous-shortcodes-addon/
 * Description: Misc shortcodes and Features addon for Simple WordPress Membership plugin
 * Version: 2.8
 * Author: wp.insider, Alexander C
 * Author URI: https://simple-membership-plugin.com/
 * Requires at least: 6.0
 */

//slug - swpm_msc

if (!defined('ABSPATH')){
    exit;
}

if (!class_exists('SWPMMiscShortcodes')) {

    class SWPMMiscShortcodes {

        var $version = '2.8';
        var $plugin_url;
        var $plugin_path;
        var $all_levels;

        function __construct() {
            $this->define_constants();
            $this->includes();
            $this->loader_operations();
            add_action('init', array(&$this, 'plugin_init'), 0);
            add_action('wp_enqueue_scripts', array(&$this, 'plugin_scripts'), 0);
            add_action('wp_footer', array(&$this, 'plugin_footer'), 0);
        }

        function define_constants() {
            define('SWPM_MISC_SHORTCODES_VERSION', $this->version);
            define('SWPM_MISC_SHORTCODES_PATH', $this->plugin_path());
        }

        function includes() {
            //Include admin menu handling file
            include_once(SWPM_MISC_SHORTCODES_PATH . '/swpm-msc-admin-menu.php');

            //Include the shortcodes files
            include_once(SWPM_MISC_SHORTCODES_PATH . '/shortcodes/swpm_utility_shortcodes.php');
            include_once(SWPM_MISC_SHORTCODES_PATH . '/shortcodes/swpm_update_level_to.php');
            include_once(SWPM_MISC_SHORTCODES_PATH . '/shortcodes/swpm_after_login_page_link.php');

            //Include the features files
            include_once(SWPM_MISC_SHORTCODES_PATH . '/features/swpm-auto-downgrade-feature.php');

        }

        function loader_operations() {
            register_activation_hook(__FILE__, array(&$this, 'activate_handler')); //activation hook
            add_action('plugins_loaded', array(&$this, 'plugins_loaded_handler')); //plugins loaded hook
        }

        function plugins_loaded_handler() {//Runs when plugins_loaded action gets fired
        }

        function activate_handler() {//Will run when the plugin activates only
        }

        function do_db_upgrade_check() {
            //NOP
        }

        function plugin_init() {

            if (!is_admin()) {

            }
        }

        function plugin_scripts() {

        }

        function plugin_footer() {

        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_path() {
            if ($this->plugin_path)
                return $this->plugin_path;
            return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
        }

        static function get_all_levels() {
            global $wpdb;
            $query = "SELECT id, alias FROM " . $wpdb->prefix . "swpm_membership_tbl WHERE  id !=1 ORDER BY id ";
            return($wpdb->get_results($query, OBJECT_K));
        }

        static function msg($msg) {
            return '<p>' . $msg . '</p>';
        }

    }

    //End of class
}//End of class not exists check

$GLOBALS['SWPMMiscShortcodes'] = new SWPMMiscShortcodes();


//Add settings link in plugins listing page
function swpm_msc_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=swpm-msc">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'swpm_msc_add_settings_link', 10, 2 );
