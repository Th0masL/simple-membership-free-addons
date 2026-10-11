<?php
/*
 * Various small utility type shortcodes can go in this file.
 */


/*
 * Shows the total members count of a site.
 */
if ( !is_admin() ){
    //Only do the shortcode on the front-end of the site.
    add_shortcode( 'swpm_show_members_count', 'swpm_msc_show_members_count_handler' );
}

function swpm_msc_show_members_count_handler( $atts ) {

    // Check if SWMP plugin is enabled
    if (!class_exists('SimpleWpMembership')) {
        // Perhaps show message to install SWMP plugin?
        return SWPMMiscShortcodes::msg('Simple Membership plugin is not installed.');
    }

    $params = shortcode_atts( array(
        'membership_level' => '',
        'account_status' => '',
        'class' => 'swpm-total-members-count',
        ), $atts );

    global $wpdb;
    $table = $wpdb->prefix . "swpm_members_tbl";

    //Count the members
    $members_count = 0;

    //Handle the where clause (if any)
    $where_clause = "";
    if ( isset( $params['membership_level'] ) && !empty( $params['membership_level'] ) ) {
        $where_clause = " WHERE membership_level = " . intval( $params['membership_level'] );
    }
    if ( isset( $params['account_status'] ) && !empty( $params['account_status'] ) ) {
        if ( empty( $where_clause ) ) {
            $where_clause = " WHERE account_state = '" . esc_sql( $params['account_status'] ) . "'";
        } else {
            $where_clause .= " AND account_state = '" . esc_sql( $params['account_status'] ) . "'";
        }
    }

    //Handle the query
    if ( !empty ( $where_clause )) {
        //Query for the total members using where clause
        $count_query = "SELECT COUNT(*) FROM $table " . $where_clause;
        $members_count = $wpdb->get_var( $count_query );
    } else {
        //Query for the total members
        //No need to use "prepare" for this one since prepare without placeholders will trigger a warning.
        $members_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }   

    $output = '';
    $output .= '<span class="'.$params['class'].'">';
    $output .= $members_count;
    $output .= '</span>';
    return $output;

}
