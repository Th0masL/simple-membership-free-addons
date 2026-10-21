<?php
/*
Plugin Name: SWPM Auto Username
Plugin URI: https://simple-membership-plugin.com/
Description: Simple Membership plugin addon that hides the username field on the registration and profile forms, generates a unique username for each new member, and lets members log in with their email address.
Author: wp.insider, Th0masL
Author URI: https://simple-membership-plugin.com/
Version: 1.0
*/

// Slug for this addon: swpm_auto_username_

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SWPM_AUTO_USERNAME_VERSION', '1.0' );

add_action( 'plugins_loaded', 'swpm_auto_username_init' );

function swpm_auto_username_init() {
	if ( ! class_exists( 'SwpmSettings' ) ) {
		return; // Simple Membership is not active.
	}

	// Settings (Simple Membership > Settings > Addons Settings).
	add_action( 'swpm_addon_settings_section', 'swpm_auto_username_settings_ui' );
	add_action( 'swpm_addon_settings_save', 'swpm_auto_username_settings_save' );

	if ( ! swpm_auto_username_enabled() ) {
		return;
	}

	// Registration form: a placeholder username (the real one is set when the form is submitted) and the row hidden.
	add_filter( 'swpm_registration_form_set_username', 'swpm_auto_username_placeholder' );
	add_filter( 'swpm_registration_form_username_tr_attributes', 'swpm_auto_username_hide_row' );

	// Profile form: the username row hidden.
	add_filter( 'swpm_edit_profile_form_username_tr_attributes', 'swpm_auto_username_hide_row' );

	// Login form and login widget: members log in with their email address.
	add_filter( 'swpm_login_form_set_username_label', 'swpm_auto_username_login_label' );
	add_filter( 'swpm_login_widget_logged_in_username', 'swpm_auto_username_logged_in_name' );
	add_filter( 'swpm_mini_login_output_when_logged_in', 'swpm_auto_username_mini_login_output' );

	// On submit: always set the username on the server (a cached form could hold a username that is taken since).
	add_action( 'swpm_front_end_registration_form_submitted', 'swpm_auto_username_set_on_submit' );

	// AJAX username check: the front-end registration form marks its requests, and the check then says "available"
	// (the field is hidden and replaced on submit). The admin forms do not send the marker, so they keep the real check.
	add_filter( 'swpm_registration_form_validation_query_args', 'swpm_auto_username_mark_ajax_requests' );
	add_filter( 'swpm_validate_user_name_ajax_override', 'swpm_auto_username_ajax_check', 10, 2 );
}

function swpm_auto_username_enabled() {
	return (bool) SwpmSettings::get_instance()->get_value( 'swpm-addon-auto-username-enable' );
}

function swpm_auto_username_settings_ui() {
	$enabled = swpm_auto_username_enabled();
	?>
	<h3><?php _e( 'Auto Username Addon Settings', 'simple-membership' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Generate the Username Automatically', 'simple-membership' ); ?></th>
				<td>
					<input type="checkbox" name="swpm-addon-auto-username-enable" value="1" <?php checked( $enabled ); ?> />
					<p class="description"><?php _e( 'Hides the username field on the registration and profile forms of Simple Membership. A unique username is generated when the member registers (for example "member-k7q2x9mf"), and the login form asks for the email address (all members can log in with their email address). Existing members no longer see their username on their profile. The default emails still show the username ({user_name}): edit them if you do not want to show it.', 'simple-membership' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

function swpm_auto_username_settings_save() {
	$settings = SwpmSettings::get_instance();
	$settings->set_value( 'swpm-addon-auto-username-enable', empty( $_POST['swpm-addon-auto-username-enable'] ) ? '' : '1' );
	$settings->save();
}

function swpm_auto_username_placeholder( $user_name ) {
	return empty( $user_name ) ? swpm_auto_username_generate() : $user_name;
}

function swpm_auto_username_hide_row( $attributes ) {
	return $attributes . ' style="display:none"';
}

function swpm_auto_username_login_label( $label ) {
	return __( 'Email', 'simple-membership' );
}

function swpm_auto_username_logged_in_name( $user_name ) {
	$email = SwpmAuth::get_instance()->get( 'email' );
	return empty( $email ) ? $user_name : esc_html( $email );
}

// The [swpm_mini_login] shortcode: show the email address instead of the username.
function swpm_auto_username_mini_login_output( $output ) {
	$auth  = SwpmAuth::get_instance();
	$email = $auth->get( 'email' );
	if ( empty( $email ) ) {
		return $output;
	}
	$username_span = '<span class="swpm_mini_login_username">' . esc_attr( $auth->get( 'user_name' ) ) . '</span>';
	return str_replace( $username_span, '<span class="swpm_mini_login_username">' . esc_html( $email ) . '</span>', $output );
}

function swpm_auto_username_set_on_submit() {
	$form_data = array();
	foreach ( array( 'email', 'first_name', 'last_name' ) as $field ) {
		$form_data[ $field ] = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
	}
	$form_data['membership_level'] = SwpmForm::get_membership_level_from_request();

	// The email already belongs to a WordPress user: use that user's login, so Simple Membership handles it as usual
	// (by default it refuses the registration and asks to contact the site admin; sites using the
	// "swpm_allow_existing_wp_user_registration" filter link the new member to that WordPress user).
	$wp_user = empty( $form_data['email'] ) ? false : get_user_by( 'email', $form_data['email'] );
	if ( $wp_user ) {
		$_POST['user_name'] = $wp_user->user_login;
		return;
	}
	$_POST['user_name'] = swpm_auto_username_generate( $form_data );
}

function swpm_auto_username_mark_ajax_requests( $query_args ) {
	$query_args['swpm_auto_username'] = 1;
	return $query_args;
}

function swpm_auto_username_ajax_check( $result, $field_value ) {
	return empty( $_GET['swpm_auto_username'] ) ? $result : true;
}

/**
 * Generates a unique username: by default "member-" followed by 8 random lowercase letters and digits.
 *
 * The format can be changed with the "swpm_auto_generated_username" filter, which receives the default username and,
 * when the registration form is submitted, the form data (email, first_name, last_name, membership_level; empty when
 * the form is only displayed). A filtered value is only used if it is a valid username that is not taken yet.
 */
function swpm_auto_username_generate( $form_data = array() ) {
	for ( $i = 0; $i < 10; $i++ ) {
		$default   = swpm_auto_username_random( 8 );
		$user_name = apply_filters( 'swpm_auto_generated_username', $default, $form_data );
		if ( swpm_auto_username_is_available( $user_name ) ) {
			return $user_name;
		}
		// The filtered value cannot be used (invalid or taken): use the default one if it is free.
		if ( $user_name !== $default && swpm_auto_username_is_available( $default ) ) {
			return $default;
		}
	}
	// Very unlikely: use a longer random username (limited number of tries; if all fail, the registration form
	// shows its usual "Username already exists" error).
	for ( $i = 0; $i < 10; $i++ ) {
		$user_name = swpm_auto_username_random( 16 );
		if ( swpm_auto_username_is_available( $user_name ) ) {
			return $user_name;
		}
	}
	SwpmLog::log_simple_debug( 'Auto Username addon: could not generate an available username.', false );
	return $user_name;
}

function swpm_auto_username_random( $length ) {
	$chars  = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$random = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$random .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
	}
	return 'member-' . $random;
}

function swpm_auto_username_is_available( $user_name ) {
	// 4 to 50 characters: WordPress also uses the username as user_nicename (50 characters at most).
	return is_string( $user_name ) && strlen( $user_name ) >= 4 && strlen( $user_name ) <= 50
		&& SwpmMemberUtils::is_valid_user_name( $user_name )
		&& ! SwpmMemberUtils::get_user_by_user_name( $user_name ) && ! username_exists( $user_name );
}
