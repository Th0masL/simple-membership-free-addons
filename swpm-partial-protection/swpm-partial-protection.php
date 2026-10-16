<?php
/*
Plugin Name: SWPM Partial Protection
Plugin URI: https://simple-membership-plugin.com/apply-partial-section-protection/
Description: Simple Membership plugin addon for applying partial or section protection to WordPress post/page content.
Author: wp.insider
Author URI: https://simple-membership-plugin.com/
Version: 2.2
*/

// Slug for this addon: swpm_partial_protection_

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SWPM_PARTIAL_PROTECT_VERSION', '2.2' );
define( 'SWPM_PARTIAL_PROTECT_PATH', dirname(__FILE__) . '/');
define( 'SWPM_PARTIAL_PROTECT_URL', plugins_url('',__FILE__));

require_once (SWPM_PARTIAL_PROTECT_PATH .'classes/class.swpm-partial-protection.php');
require_once( SWPM_PARTIAL_PROTECT_PATH . 'classes/class.swpm-partial-protect-shortcode-output.php' );

add_action('plugins_loaded', 'swpm_partial_protection_addon');
function swpm_partial_protection_addon(){
    new SwpmPartialProtection();
}
