<?php

//=== New Implementation (does the check/update/downgrade at init time) ===
add_action('init', 'swpm_msc_check_auto_downgrade_feature');
function swpm_msc_check_auto_downgrade_feature( $args ) {
    //Trigger an action hook.
    do_action('swpm_msc_check_auto_downgrade_feature');

    //#1 - check if the user is logged in.
    if(!class_exists('SwpmAuth') || !class_exists('SwpmMemberUtils')){
        //Required classes from the core plugin are not available. So bail out.
        return;
    }
    $auth = SwpmAuth::get_instance();
    //Check if the user is logged-into the site.
    if(!$auth->is_logged_in()){
        //Anonymous user (not logged-in). No need to check anything else.
        return;
    }

    //#2 - check if auto downgrade feature is enabled.
    $msc_options = get_option('swpm_msc_addon_settings');
    $auto_downgrade_account_enabled = isset($msc_options['auto_downgrade_account_enabled']) ? $msc_options['auto_downgrade_account_enabled'] : '';
    $downgrade_to_level = isset($msc_options['downgrade_to_level']) ? $msc_options['downgrade_to_level'] : '';
    $auto_downgrade_redirect_url = isset($msc_options['auto_downgrade_redirect_url']) ? $msc_options['auto_downgrade_redirect_url'] : '';

    //Check to see if the feature is enabled and if all the other required values are present.
    if (empty($auto_downgrade_account_enabled)) {
        return;
    }

    if (empty($downgrade_to_level)) {
        return;
    }

    //#3 - Get the username of the logged-in user.
    $username = $auth->get( 'user_name' );
    if (empty($username)) {
        SwpmLog::log_simple_debug('Auto downgrade feature: Username is empty. So bailing out.', false);
        return;
    }

    $swpm_user_row = SwpmMemberUtils::get_user_by_user_name($username);
    if (!isset($swpm_user_row->member_id)) {
        return;
    }

    $member_id = $swpm_user_row->member_id;
    $current_account_state = $swpm_user_row->account_state;
    if ( $current_account_state == 'active' || $current_account_state == 'activation_required' || $current_account_state == 'pending'){
        //we don't want to touch account with these account status. So bail out.
        return;
    }

    /*** We have an expired or inactive account that needs to be auto downgraded. ***/

    //Get the current membership level.
    $current_membership_level = $swpm_user_row->membership_level;

    // Trigger action hook before the auto downgrade so that other addons can also hook into it if needed.
    do_action(
            'swpm_msc_before_auto_downgrade_membership_level',
            array(
                    'member_id'  => $member_id,
                    'from_level' => $current_membership_level,
                    'to_level'   => $downgrade_to_level,
            )
    );    

    //Update the membership level to the target level
    //Note: this function will also trigger the 'swpm_membership_level_changed' action hook.
    SwpmMemberUtils::update_membership_level_and_role( $member_id, $downgrade_to_level );

    //Set the account status to active
    //Note: this function will also trigger the 'swpm_account_status_updated' action hook.
    SwpmMemberUtils::update_account_state( $member_id, 'active' );

    //Reset the access start date to today
    $todays_date = ( date( 'Y-m-d' ) );
    SwpmMemberUtils::update_access_starts_date($member_id, $todays_date);

    //Auto downgrade complete.
    // Trigger action hook after the auto downgrade so that other addons can also hook into it if needed.
    do_action(
            'swpm_msc_after_auto_downgrade_membership_level',
            array(
                    'member_id'  => $member_id,
                    'from_level' => $current_membership_level,
                    'to_level'   => $downgrade_to_level,
            )
    );

    //Log the auto downgrade action.
    SwpmLog::log_simple_debug('Auto downgrade complete. Member ID: ' . $member_id . ', Username: '. $username . ', Old membership level: ' . $current_membership_level . ', New membership level: ' . $downgrade_to_level, true);
    

    //Redirect the user to the auto downgrade redirect URL (if provided)
    if(!empty($auto_downgrade_redirect_url)){
        SwpmLog::log_simple_debug('Redirect after auto downgrade is enabled. Redirecting to: ' . $auto_downgrade_redirect_url, true);
        SwpmMiscUtils::redirect_to_url($auto_downgrade_redirect_url);
        exit;
    }

}
