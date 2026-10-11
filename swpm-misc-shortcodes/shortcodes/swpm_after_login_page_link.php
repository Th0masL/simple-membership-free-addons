<?php

add_shortcode('swpm_show_after_login_page_link', 'swpm_handle_show_after_login_page_link');

function swpm_handle_show_after_login_page_link($args){

    extract(shortcode_atts(array(
        'link_anchor' => 'Your Home Page',
        'link_target' => '_blank',
    ), $args));

    $output = "";
    $auth = SwpmAuth::get_instance();
    if ($auth->is_logged_in()) {
        //Member is logged in. Lets check if after login redirection URL is set for this member's membership level.

        if (!class_exists('SwpmMembershipLevelCustom')) {
            SwpmLog::log_simple_debug("Error - swpm_show_after_login_page_link shortcode. Missing the SwpmMembershipLevelCustom class.", true);
            return "";
        }

        $level = $auth->get('membership_level');
        $level_id = $level;
        $key = 'swpm_alr_after_login_page_field';
        $after_login_page_url = SwpmMembershipLevelCustom::get_value_by_key($level_id, $key);
        if (!empty($after_login_page_url)) {
            //Membership level specific after login page exists.
            $output .= '<span class="swpm_after_login_page_link"><a href="' . $after_login_page_url . '" target="' . $link_target . '" >' . $link_anchor . '</a></span>';
        }

    }

    return $output;
}
