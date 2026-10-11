<?php

class swpm_update_level_to {

    function __construct() {
        if ( !is_admin() ){
            //Only do the shortcode for front-end of the site.
            add_shortcode('swpm_update_level_to', array($this, 'shortcode_handler'));
        }
    }

    function process($btn_level) {
        if (isset($_POST['swpm_ms_update_level_to'])) { //swpm_update_level_to
            $update_to = intval($_POST['swpm_ms_update_level_to']);
            $todays_date = ( date( 'Y-m-d' ) );
            
            if ($btn_level != $update_to) {
                return false;
            } if (isset($_POST['swpm_ms_update_level_to_uid'])) {
                $uid = $_POST['swpm_ms_update_level_to_uid'];
            } else {
                //No UID
                return 'No UID.';
            }
            if (isset($_REQUEST['_wpnonce'])) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'swpm_ms_update_level_to_' . $uid)) {
                    //Nonce check failed
                    return (SwpmUtils::_("Sorry, Nonce verification failed."));
                }
            } else {
                //No nonce
                return(SwpmUtils::_("Sorry, Nonce verification failed."));
            }

            $all_levels = SWPMMiscShortcodes::get_all_levels();

            // Check if level exists
            if (!isset($all_levels[$update_to])) {
                return 'No level exists.';
            }

            if (!SwpmMemberUtils::is_member_logged_in()) {
                // No member logged in
                $err_msg = SwpmUtils::_("You must be logged in to upgrade a membership.");
                return $err_msg;
            }
            $member_id = SwpmMemberUtils::get_logged_in_members_id();

            // Trigger filter hook before the level update action so that other addons can also hook into it if needed.
            $update_to = apply_filters( 'swpm_msc_update_level_to', $update_to, array( 'member_id' => $member_id ) );

            //Update the membership level. 
            //Note: this function will also trigger the 'swpm_membership_level_changed' action hook.
            if(method_exists("SwpmMemberUtils", "update_membership_level_and_role")){
                SwpmMemberUtils::update_membership_level_and_role($member_id, $update_to);//Update the membership level and role
            } else {
                SwpmMemberUtils::update_membership_level($member_id, $update_to);//Update the membership level (legacy for pre SWPM v4.2.4)
            }
            //Update the account state to active.
            SwpmMemberUtils::update_account_state($member_id, 'active');//Activate the account (to make sure inactive accounts becomes usable)
            //Update the access starts date to today.
            SwpmMemberUtils::update_access_starts_date($member_id, $todays_date);//Reset the access start date to today (so the account can get the full duration for the new level).

            if (isset($_POST['swpm_ms_update_level_to_redirect'])) {
                if (class_exists('SwpmMiscUtils')){
                    SwpmMiscUtils::redirect_to_url($_POST['swpm_ms_update_level_to_redirect']);
                }
            }
            $msg = SwpmUtils::_("Membership level has been updated.");
            return $msg;
        }
        return false;
    }

    function shortcode_handler($atts) {
        // Check if SWMP plugin is enabled
        if (!class_exists('SimpleWpMembership')) {
            // Perhaps show message to install SWMP plugin?
            return SWPMMiscShortcodes::msg('Simple Membership plugin is not installed.');
        }

        $params = shortcode_atts(array(
            'level' => false,
            'button_text' => false,
            'redirect_to' => false,
            'class' => false,
                ), $atts);

        //Check if level is specified
        if ($params['level'] === false) {
            return 'No level specified';
        }
        $params['level'] = intval($params['level']);

        //Check if member is logged in
        if (!SwpmMemberUtils::is_member_logged_in()) {
            $err_msg = SwpmUtils::_("You must be logged in to upgrade a membership.");
            return SWPMMiscShortcodes::msg($err_msg);
        }

        //Check if current member level is not the one button supposed to upgrade it to
        $curr_level = SwpmMemberUtils::get_logged_in_members_level();
        if ($curr_level == $params['level'] && !isset($_POST['swpm_ms_update_level_to'])) {
            $msg = SwpmUtils::_("Already a member of this level.");
            return SWPMMiscShortcodes::msg($msg);
        }

        $all_levels = SWPMMiscShortcodes::get_all_levels();

        //Check if specified level exists
        if (!isset($all_levels[$params['level']])) {
            return SWPMMiscShortcodes::msg('No level exists.');
        }

        $submit_process = $this->process($params['level']);

        if ($submit_process) {
            return SWPMMiscShortcodes::msg($submit_process);
        }

        //Check if button_text is specified
        if ($params['button_text'] === false) {
            //If not - let's create our own text
            $params['button_text'] = 'Upgrade to ' . $all_levels[$params['level']]->alias;
        }

        //Set default class if not specified (use ternary operator instead of 'isset' since isset doesn't check if parameter value is false)
        $params['class'] = $params['class'] ? $params['class'] : 'swpm-update-level-to-button';

        $uid = uniqid();

        $params['redirect_to'] = ($params['redirect_to'] === false ? '' : '<input type="hidden" name="swpm_ms_update_level_to_redirect" value="' . esc_url_raw($params['redirect_to']) . '">');

        $output = '';
        $output .= '<form class="swpm_update_level_to" method="POST">';
        $output .= '<input type="hidden" name="swpm_ms_update_level_to" value="' . esc_attr($params['level']) . '">';
        $output .= '<input type="hidden" name="swpm_ms_update_level_to_uid" value="' . esc_attr($uid). '">';
        $output .= wp_nonce_field('swpm_ms_update_level_to_' . $uid);
        $output .= $params['redirect_to'];
        $output .= '<button type="submit" class="'.esc_attr($params['class']).'"><span>'.esc_attr($params['button_text']).'</span></button>';
        $output .= '</form>';
        return $output;
    }

}

$GLOBALS['swpm_update_level_to'] = new swpm_update_level_to();
