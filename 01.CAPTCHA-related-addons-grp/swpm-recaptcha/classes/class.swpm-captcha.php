<?php

/**
 * Description of SwpmCaptcha
 *
 */
class SwpmCaptcha {

    public function __construct() {
	if ( class_exists( 'SimpleWpMembership' ) ) {
	    add_action( 'swpm_addon_settings_section', array( &$this, 'settings_ui' ) );
	    add_action( 'swpm_addon_settings_save', array( &$this, 'settings_save' ) );

	    //Captcha for the registration form
	    add_filter( 'swpm_before_registration_submit_button', array( $this, 'add_recaptcha_code' ) );
	    add_filter( 'swpm_validate_registration_form_submission', array( $this, 'validate_recaptcha_code' ) );

	    //Captcha for the login form
	    add_filter( 'swpm_before_login_form_submit_button', array( $this, 'add_login_form_recaptcha_code' ) );
	    add_filter( 'swpm_validate_login_form_submission', array( $this, 'validate_login_form_recaptcha_code' ) );

	    //Captcha for the password reset form
	    add_filter( 'swpm_before_pass_reset_form_submit_button', array( $this, 'add_pass_reset_form_recaptcha_code' ) );
	    add_filter( 'swpm_validate_pass_reset_form_submission', array( $this, 'validate_pass_reset_form_recaptcha_code' ) );
	}
    }

    public function settings_ui() {
	$settings			 = SwpmSettings::get_instance();
	$enable_captcha			 = $settings->get_value( 'swpm-addon-enable-captcha' );
	$google_recaptcha_site_key	 = $settings->get_value( 'swpm-addon-google-recaptcha-site-key' );
	$google_recaptcha_secret_key	 = $settings->get_value( 'swpm-addon-google-recaptcha-secret-key' );
	$enable_login_captcha		 = $settings->get_value( 'swpm-addon-enable-login-captcha' );
	$enable_pass_reset_captcha	 = $settings->get_value( 'swpm-addon-enable-pass-reset-captcha' );
	$recaptcha_theme		 = $settings->get_value( 'swpm-addon-recaptcha-theme' );
	$recaptcha_size			 = $settings->get_value( 'swpm-addon-recaptcha-size' );
	require_once (SWPM_CAPTCHA_PATH . 'views/settings.php');
    }

    public function settings_save() {
	$message			 = array( 'succeeded' => true, 'message' => '<p>' . BUtils::_( 'Settings updated!' ) . '</p>' );
	SwpmTransfer::get_instance()->set( 'status', $message );
	$enable_captcha			 = filter_input( INPUT_POST, 'swpm-addon-enable-captcha' );
	$site_key			 = filter_input( INPUT_POST, 'swpm-addon-google-recaptcha-site-key' );
	$secret_key			 = filter_input( INPUT_POST, 'swpm-addon-google-recaptcha-secret-key' );
	$enable_login_captcha		 = filter_input( INPUT_POST, 'swpm-addon-enable-login-captcha' );
	$enable_pass_reset_captcha	 = filter_input( INPUT_POST, 'swpm-addon-enable-pass-reset-captcha' );
	$recaptcha_theme		 = filter_input( INPUT_POST, 'swpm-addon-recaptcha-theme' );
	$recaptcha_size			 = filter_input( INPUT_POST, 'swpm-addon-recaptcha-size' );

	$settings = SwpmSettings::get_instance();
	$settings->set_value( 'swpm-addon-enable-captcha', empty( $enable_captcha ) ? "" : $enable_captcha  );
	$settings->set_value( 'swpm-addon-google-recaptcha-site-key', sanitize_text_field( $site_key ) );
	$settings->set_value( 'swpm-addon-google-recaptcha-secret-key', sanitize_text_field( $secret_key ) );
	$settings->set_value( 'swpm-addon-enable-login-captcha', empty( $enable_login_captcha ) ? "" : $enable_login_captcha  );
	$settings->set_value( 'swpm-addon-enable-pass-reset-captcha', empty( $enable_pass_reset_captcha ) ? "" : $enable_pass_reset_captcha  );
	$settings->set_value( 'swpm-addon-recaptcha-theme', empty( $recaptcha_theme ) ? "" : $recaptcha_theme  );
	$settings->set_value( 'swpm-addon-recaptcha-size', empty( $recaptcha_size ) ? "" : $recaptcha_size  );
	$settings->save();
    }

    private function get_recaptcha_html( $siteKey, $theme, $size ) {
	$captcha_css	 = '<style type="text/css">';
	$captcha_css	 .= '@media screen and (max-width: 375px) {
            .swpm_g_captcha{transform:scale(0.77);transform-origin:0;-webkit-transform:scale(0.77);-webkit-transform-origin:0 0;}
        }'; //For small screen devices
	$captcha_css	 .= '</style>';

	$output	 = '<div class="swpm-recaptcha-section" style="margin-bottom: 15px;">';
	$output	 .= $captcha_css; //Add the captcha specific CSS to the output
	$output	 .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
	$output	 .= '<div class="swpm_g_captcha">';
	$output	 .= '<div class="g-recaptcha" data-sitekey="' . $siteKey . '" data-theme="' . $theme . '" data-size="' . $size . '"></div>';
	$output	 .= '</div>';
	$output	 .= '</div>';

	return $output;
    }

    public function add_recaptcha_code( $output ) {
	$settings	 = SwpmSettings::get_instance();
	$enabled	 = $settings->get_value( 'swpm-addon-enable-captcha' );
	if ( empty( $enabled ) ) {
	    return $output;
	}

	$output = $this->get_recaptcha_html( $settings->get_value( 'swpm-addon-google-recaptcha-site-key' ), $settings->get_value( 'swpm-addon-recaptcha-theme' ), $settings->get_value( 'swpm-addon-recaptcha-size' ) );
	return $output;
    }

    public function validate_recaptcha_code( $output ) {

	$settings	 = SwpmSettings::get_instance();
	$enabled	 = $settings->get_value( 'swpm-addon-enable-captcha' );
	if ( empty( $enabled ) ) {
	    return $output;
	}

        SwpmLog::log_simple_debug("[reCAPTCHA Addon] Google Captcha is enabled. Lets validate captcha.", true);
        
	//Since the registration captcha is enabled in settings, it MUST validate the captcha.
	require_once(SWPM_CAPTCHA_PATH . 'libs/autoload.php');
	$secret = $settings->get_value( 'swpm-addon-google-recaptcha-secret-key' );

	//Initialize captcha object            
	$reCaptcha = new \ReCaptcha\ReCaptcha( $secret );

	$captcha_response = isset( $_REQUEST[ "g-recaptcha-response" ] ) ? sanitize_text_field( $_REQUEST[ "g-recaptcha-response" ] ) : '';
	$resp = $reCaptcha->verify( $captcha_response, $_SERVER[ "REMOTE_ADDR" ] );
	if ( $resp->isSuccess() ) {//valid reCAPTCHA response
	    $output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA Addon] Captcha validated successfully!", true);
	} else {
	    $output = 'captcha error';
            SwpmLog::log_simple_debug("[reCAPTCHA Addon] Captcha error!", false);
	}

	return $output;
    }

    public function add_login_form_recaptcha_code( $output ) {
	$settings	 = SwpmSettings::get_instance();
	$enabled	 = $settings->get_value( 'swpm-addon-enable-login-captcha' );
	if ( empty( $enabled ) ) {
	    return $output;
	}

	$output = $this->get_recaptcha_html( $settings->get_value( 'swpm-addon-google-recaptcha-site-key' ), $settings->get_value( 'swpm-addon-recaptcha-theme' ), $settings->get_value( 'swpm-addon-recaptcha-size' ) );
	return $output;
    }

    public function validate_login_form_recaptcha_code( $output ) {

	$settings	 = SwpmSettings::get_instance();
	$enabled	 = $settings->get_value( 'swpm-addon-enable-login-captcha' );
	if ( empty( $enabled ) ) {
	    return $output;
	}

	//Check if the submission is actually from SWPM login form. We don't want to catch the standard WP Login form's submission.
	$swpm_username = apply_filters( 'swpm_user_name', filter_input( INPUT_POST, 'swpm_user_name' ) );
	if ( empty( $swpm_username ) ) {
	    //This is not a submission from SWPM login form.
	    return $output;
	}

	//Since the login captcha is enabled in settings, it MUST validate the captcha.
	require_once(SWPM_CAPTCHA_PATH . 'libs/autoload.php');
	$secret = $settings->get_value( 'swpm-addon-google-recaptcha-secret-key' );

	//Initialize captcha object            
	$reCaptcha = new \ReCaptcha\ReCaptcha( $secret );

	$captcha_response = isset( $_REQUEST[ "g-recaptcha-response" ] ) ? sanitize_text_field( $_REQUEST[ "g-recaptcha-response" ] ) : '';
	$resp = $reCaptcha->verify( $captcha_response, $_SERVER[ "REMOTE_ADDR" ] );
	if ( $resp->isSuccess() ) {//valid reCAPTCHA response
	    $output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA Addon] Login captcha validated successfully!", true);
	} else {
	    $output = 'captcha error';
	}

	return $output;
    }

    public function add_pass_reset_form_recaptcha_code( $output ) {
	$settings	 = SwpmSettings::get_instance();
	$enabled	 = $settings->get_value( 'swpm-addon-enable-pass-reset-captcha' );
	if ( empty( $enabled ) ) {
	    return $output;
	}

	$output = $this->get_recaptcha_html( $settings->get_value( 'swpm-addon-google-recaptcha-site-key' ), $settings->get_value( 'swpm-addon-recaptcha-theme' ), $settings->get_value( 'swpm-addon-recaptcha-size' ) );
	return $output;
    }

    public function validate_pass_reset_form_recaptcha_code( $output ) {

	$settings	 = SwpmSettings::get_instance();
	$enabled	 = $settings->get_value( 'swpm-addon-enable-pass-reset-captcha' );
	if ( empty( $enabled ) ) {
	    return $output;
	}

	//Since the password reset captcha is enabled in settings, it MUST validate the captcha.
	require_once(SWPM_CAPTCHA_PATH . 'libs/autoload.php');
	$secret = $settings->get_value( 'swpm-addon-google-recaptcha-secret-key' );

	//Initialize captcha object            
	$reCaptcha = new \ReCaptcha\ReCaptcha( $secret );

	$captcha_response = isset( $_REQUEST[ "g-recaptcha-response" ] ) ? sanitize_text_field( $_REQUEST[ "g-recaptcha-response" ] ) : '';
	$resp = $reCaptcha->verify( $captcha_response, $_SERVER[ "REMOTE_ADDR" ] );
	if ( $resp->isSuccess() ) {//valid reCAPTCHA response
	    $output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA Addon] Password reset captcha validated successfully!", true);
	} else {
	    $output = 'captcha error';
	}
	return $output;
    }

}
