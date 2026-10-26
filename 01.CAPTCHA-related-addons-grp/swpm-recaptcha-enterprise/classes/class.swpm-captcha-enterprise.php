<?php

/**
 * Description of SwpmCaptcha
 *
 */
class SwpmCaptcha {

	private string $api_key;
	private string $project_id;
	private string $site_key;
	private string $badge_location;

	public function __construct() {
		if ( class_exists( 'SimpleWpMembership' ) ) {
			add_action( 'swpm_addon_settings_section', array( &$this, 'settings_ui' ) );
			add_action( 'swpm_addon_settings_save', array( &$this, 'settings_save' ) );

			//Captcha for the registration form
			add_filter( 'swpm_before_registration_submit_button', array( $this, 'add_reg_form_recaptcha_code' ) );
			add_filter( 'swpm_validate_registration_form_submission', array(
				$this,
				'validate_reg_form_recaptcha_code'
			) );

			//Captcha for the login form
			add_filter( 'swpm_before_login_form_submit_button', array( $this, 'add_login_form_recaptcha_code' ) );
			add_filter( 'swpm_validate_login_form_submission', array( $this, 'validate_login_form_recaptcha_code' ) );

			//Captcha for the password reset form
			add_filter( 'swpm_before_pass_reset_form_submit_button', array(
				$this,
				'add_pass_reset_form_recaptcha_code'
			) );
			add_filter( 'swpm_validate_pass_reset_form_submission', array(
				$this,
				'validate_pass_reset_form_recaptcha_code'
			) );
		}

		$this->site_key   = SwpmSettings::get_instance()->get_value( 'swpm-addon-google-recaptcha-ent-key-id' );
		$this->project_id = SwpmSettings::get_instance()->get_value( 'swpm-addon-google-recaptcha-ent-project-id' );
		$this->api_key    = SwpmSettings::get_instance()->get_value( 'swpm-addon-google-recaptcha-ent-api-key' );
		$this->badge_location = SwpmSettings::get_instance()->get_value( 'swpm-addon-google-recaptcha-ent-badge-location' );
	}

	public function settings_ui() {
		$settings                    = SwpmSettings::get_instance();
		$enable_captcha              = $settings->get_value( 'swpm-addon-enable-registration-captcha-ent' );
		$google_recaptcha_key_id     = $settings->get_value( 'swpm-addon-google-recaptcha-ent-key-id' );
		$google_recaptcha_project_id = $settings->get_value( 'swpm-addon-google-recaptcha-ent-project-id' );
		$google_recaptcha_api_key    = $settings->get_value( 'swpm-addon-google-recaptcha-ent-api-key' );
        $badge_location              = $settings->get_value( 'swpm-addon-google-recaptcha-ent-badge-location' );
		$enable_login_captcha        = $settings->get_value( 'swpm-addon-enable-login-captcha-ent' );
		$enable_pass_reset_captcha   = $settings->get_value( 'swpm-addon-enable-pass-reset-captcha-ent' );
		// $recaptcha_theme             = $settings->get_value( 'swpm-addon-recaptcha-ent-theme' );
		// $recaptcha_size              = $settings->get_value( 'swpm-addon-recaptcha-ent-size' );
		require_once( SWPM_CAPTCHA_PATH . 'views/settings.php' );
	}

	public function settings_save() {
		$message = array(
			'succeeded' => true,
			'message'   => '<p>' . __( 'Settings updated!', 'simple-membership' ) . '</p>'
		);
		SwpmTransfer::get_instance()->set( 'status', $message );
		$site_key                    = isset( $_POST['swpm-addon-google-recaptcha-ent-key-id'] ) ? $_POST['swpm-addon-google-recaptcha-ent-key-id'] : '';
		$project_id                  = isset( $_POST['swpm-addon-google-recaptcha-ent-project-id'] ) ? $_POST['swpm-addon-google-recaptcha-ent-project-id'] : '';
		$api_key                     = isset( $_POST['swpm-addon-google-recaptcha-ent-api-key'] ) ? $_POST['swpm-addon-google-recaptcha-ent-api-key'] : '';
		$badge_location              = isset( $_POST['swpm-addon-google-recaptcha-ent-badge-location'] ) ? $_POST['swpm-addon-google-recaptcha-ent-badge-location'] : 'bottomright';
		$enable_login_captcha        = isset( $_POST['swpm-addon-enable-login-captcha-ent'] ) ? $_POST['swpm-addon-enable-login-captcha-ent'] : '';
		$enable_pass_reset_captcha   = isset( $_POST['swpm-addon-enable-pass-reset-captcha-ent'] ) ? $_POST['swpm-addon-enable-pass-reset-captcha-ent'] : '';
		$enable_registration_captcha = isset( $_POST['swpm-addon-enable-registration-captcha-ent'] ) ? $_POST['swpm-addon-enable-registration-captcha-ent'] : '';
		// $recaptcha_theme             = filter_input( INPUT_POST, 'swpm-addon-recaptcha-ent-theme' );
		// $recaptcha_size              = filter_input( INPUT_POST, 'swpm-addon-recaptcha-ent-size' );

		$settings = SwpmSettings::get_instance();
		$settings->set_value( 'swpm-addon-google-recaptcha-ent-key-id', sanitize_text_field( $site_key ) );
		$settings->set_value( 'swpm-addon-google-recaptcha-ent-api-key', sanitize_text_field( $api_key ) );
		$settings->set_value( 'swpm-addon-google-recaptcha-ent-project-id', sanitize_text_field( $project_id ) );
		$settings->set_value( 'swpm-addon-google-recaptcha-ent-badge-location', sanitize_text_field( $badge_location ) );
		$settings->set_value( 'swpm-addon-enable-registration-captcha-ent', empty( $enable_registration_captcha ) ? "" : $enable_registration_captcha );
		$settings->set_value( 'swpm-addon-enable-login-captcha-ent', empty( $enable_login_captcha ) ? "" : $enable_login_captcha );
		$settings->set_value( 'swpm-addon-enable-pass-reset-captcha-ent', empty( $enable_pass_reset_captcha ) ? "" : $enable_pass_reset_captcha );
		// $settings->set_value( 'swpm-addon-recaptcha-ent-theme', empty( $recaptcha_theme ) ? "" : $recaptcha_theme );
		// $settings->set_value( 'swpm-addon-recaptcha-ent-size', empty( $recaptcha_size ) ? "" : $recaptcha_size );
		$settings->save();
	}

	private function get_recaptcha_html( $action ) {
		$action = ! empty( $action ) ? $action : 'submit';

		ob_start();
		?>
        <script src="https://www.google.com/recaptcha/enterprise.js?render=<?php echo esc_attr( $this->site_key ) ?>&onload=onGoogleCaptchaEntScriptLoaded&badge=<?php echo esc_attr( $this->badge_location ) ?>"></script>

        <input type="hidden" class="g-recaptcha-response" name="g-recaptcha-response"/>

        <script>
            function onGoogleCaptchaEntScriptLoaded() {
                grecaptcha.enterprise.ready(function () {
                    const captchaResponseInput = document.querySelector(".g-recaptcha-response");
                    grecaptcha.enterprise.execute(
                        "<?php echo esc_attr( $this->site_key ) ?>",
                        {
                            action: "<?php echo esc_attr( $action ); ?>"
                        }
                    )
                        .then(function (token) {
                            // Add your logic to submit to your backend server here.
                            captchaResponseInput.value = token
                            // console.log(token) // Debug purpose only.
                        });
                });
            }
        </script>

		<?php
		$output = ob_get_clean();

		return $output;
	}

	public function add_reg_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-registration-captcha-ent' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		$output = $this->get_recaptcha_html( 'swpmRegistration' );

		return $output;
	}

	public function validate_reg_form_recaptcha_code( $output ) {

		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-registration-captcha-ent' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		require_once SWPM_CAPTCHA_PATH . "lib/google-recaptcha-enterprise-custom-lib.php";

		$token = isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : '';

		if ( swpm_validate_recaptcha_enterprise(
			$this->project_id,
			$this->site_key,
			$token,
			$this->api_key,
			'swpmRegistration'
		) ) {
			$output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA Enterprise Addon] Captcha validated successfully!", true);
		} else {
			$output = 'captcha error';
            SwpmLog::log_simple_debug("[reCAPTCHA Enterprise Addon] Captcha error!", true);
		}


		return $output;
	}

	public function add_login_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-login-captcha-ent' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		$output = $this->get_recaptcha_html( 'swpmLogin' );

		return $output;
	}

	public function validate_login_form_recaptcha_code( $output ) {

		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-login-captcha-ent' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		//Check if the submission is actually from SWPM login form. We don't want to catch the standard WP Login form's submission.

        $swpm_username = apply_filters( 'swpm_user_name', filter_input( INPUT_POST, 'swpm_user_name' ) );
        if ( empty( $swpm_username ) ) {
            //This is not a submission from SWPM login form.
            return $output;
        }

		require_once SWPM_CAPTCHA_PATH . "lib/google-recaptcha-enterprise-custom-lib.php";

        $token = isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : '';

		if ( swpm_validate_recaptcha_enterprise(
			$this->project_id,
			$this->site_key,
			$token,
			$this->api_key,
			'swpmLogin'
		) ) {
			$output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA Enterprise Addon] Captcha validated successfully!", true);
		} else {
			$output = 'captcha error';
            SwpmLog::log_simple_debug("[reCAPTCHA Enterprise Addon] Captcha error!", true);
		}

		return $output;
	}

	public function add_pass_reset_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-pass-reset-captcha-ent' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		$output = $this->get_recaptcha_html( 'swpmPassReset' );

		return $output;
	}

	public function validate_pass_reset_form_recaptcha_code( $output ) {

		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-pass-reset-captcha-ent' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		require_once SWPM_CAPTCHA_PATH . "lib/google-recaptcha-enterprise-custom-lib.php";

		$token = isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : '';

		if ( swpm_validate_recaptcha_enterprise(
			$this->project_id,
			$this->site_key,
			$token,
			$this->api_key,
			'swpmPassReset'
		) ) {
			$output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA Enterprise Addon] Captcha validated successfully!", true);
		} else {
			$output = 'captcha error';
            SwpmLog::log_simple_debug("[reCAPTCHA Enterprise Addon] Captcha error!", true);
		}

		return $output;
	}

}
