<?php

class SwpmCloudflareTurnstile {

	/**
	 * Keeps track of whether the script data has already been localized in the current request.
	 *
	 * @var bool
	 */
	private $scripts_localized = false;

	public function __construct() {
		add_action( 'init', array( $this, 'init_time_tasks' ) );

		if ( class_exists( 'SimpleWpMembership' ) ) {
			// Add code for settings page.
			add_action( 'swpm_addon_settings_section', array( &$this, 'settings_ui' ) );
			add_action( 'swpm_addon_settings_save', array( &$this, 'settings_save' ) );

			// Load the turnstile scripts on all pages (useful for themes that load the pages via AJAX).
			add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_scripts_globally' ) );

			// Captcha for the registration form.
			add_filter( 'swpm_before_registration_submit_button', array( $this, 'add_reg_form_recaptcha_code' ) );
			add_filter( 'swpm_validate_registration_form_submission', array(
				$this,
				'validate_reg_form_recaptcha_code'
			) );

			// Captcha for the login form.
			add_filter( 'swpm_before_login_form_submit_button', array( $this, 'add_login_form_recaptcha_code' ) );
			add_filter( 'swpm_validate_login_form_submission', array( $this, 'validate_login_form_recaptcha_code' ) );

			// Captcha for the password reset form.
			add_filter( 'swpm_before_pass_reset_form_submit_button', array(
				$this,
				'add_pass_reset_form_recaptcha_code'
			) );
			add_filter( 'swpm_validate_pass_reset_form_submission', array(
				$this,
				'validate_pass_reset_form_recaptcha_code'
			) );
		}
	}

	public function init_time_tasks() {
		wp_register_script( 'cloudflare-turnstile-script', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null );

		wp_register_script( 'swpm-cft-script', SWPM_CFT_URL . '/assets/js/public.js', array( 'cloudflare-turnstile-script' ), SWPM_CFT_V3_VER, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );

		wp_register_style( 'swpm-cft-style', SWPM_CFT_URL . '/assets/css/public.css', null, SWPM_CFT_V3_VER );
	}

	/**
	 * Renders the settings page content in 'SWPM Addon Settings' page.
	 *
	 * @return void
	 */
	public function settings_ui() {
		require_once SWPM_CFT_PATH . 'views/settings.php';
	}

	/**
	 * Handle settings page form submission.
	 *
	 * @return void
	 */
	public function settings_save() {
		$message = array(
			'succeeded' => true,
			'message'   => '<p>' . __( 'Cloudflare Turnstile Settings Updated!', 'simple-membership' ) . '</p>'
		);

		SwpmTransfer::get_instance()->set( 'status', $message );

		$enable_registration_captcha = filter_input( INPUT_POST, 'swpm-addon-enable-registration-cft' );
		$site_key                    = filter_input( INPUT_POST, 'swpm-addon-cft-site-key' );
		$secret_key                  = filter_input( INPUT_POST, 'swpm-addon-cft-secret-key' );
		$enable_login_captcha        = filter_input( INPUT_POST, 'swpm-addon-enable-login-cft' );
		$enable_pass_reset_captcha   = filter_input( INPUT_POST, 'swpm-addon-enable-pass-reset-cft' );
		$load_scripts_globally       = filter_input( INPUT_POST, 'swpm-addon-cft-load-scripts-globally' );

		$settings = SwpmSettings::get_instance();

		$settings->set_value( 'swpm-addon-enable-registration-cft', empty( $enable_registration_captcha ) ? "" : $enable_registration_captcha );
		$settings->set_value( 'swpm-addon-cft-site-key', sanitize_text_field( $site_key ) );

        if ( strlen($secret_key) > 0 ){
            // Only update if there is a value in the secret input field, putting empty space will update the value.
		    $settings->set_value( 'swpm-addon-cft-secret-key', sanitize_text_field( $secret_key ) );
        }

        $settings->set_value( 'swpm-addon-enable-login-cft', empty( $enable_login_captcha ) ? "" : $enable_login_captcha );
		$settings->set_value( 'swpm-addon-enable-pass-reset-cft', empty( $enable_pass_reset_captcha ) ? "" : $enable_pass_reset_captcha );
		$settings->set_value( 'swpm-addon-cft-load-scripts-globally', empty( $load_scripts_globally ) ? "" : $load_scripts_globally );

		$settings->save();
	}

	/**
	 * Retrieves the configuration error message of the turnstile.
	 *
	 * @return string The error message.
	 */
	private function get_config_error_msg() {
		return __( 'Error rendering cloudflare turnstile. Re-check your turnstile configurations.', 'simple-membership' );
	}

	/**
	 * Enqueues the turnstile script and style. The script data is only localized once per request.
	 *
	 * @return void
	 */
	private function enqueue_turnstile_assets() {
		if ( ! $this->scripts_localized ) {
			wp_localize_script( 'swpm-cft-script', 'swpm_cft_messages', array(
				'config_error' => $this->get_config_error_msg(),
			) );
			$this->scripts_localized = true;
		}

		wp_enqueue_script( 'swpm-cft-script' );
		wp_enqueue_style( 'swpm-cft-style' );
	}

	/**
	 * Enqueues the turnstile script and style on all pages (if that option is enabled in the settings).
	 * Some themes load the pages via AJAX. In that case the scripts need to be present on every page so
	 * the widget can be rendered when the form is loaded into the page.
	 *
	 * @return void
	 */
	public function maybe_enqueue_scripts_globally() {
		$settings = SwpmSettings::get_instance();

		$load_scripts_globally = $settings->get_value( 'swpm-addon-cft-load-scripts-globally' );
		if ( empty( $load_scripts_globally ) ) {
			return;
		}

		$siteKey = $settings->get_value( 'swpm-addon-cft-site-key' );
		if ( empty( trim( strval( $siteKey ) ) ) ) {
			// Nothing can be rendered without a site key.
			return;
		}

		$this->enqueue_turnstile_assets();
	}

	/**
	 * Generates the necessary html for Cloudflare Turnstile
	 *
	 * @param $action  string The context for captcha validation.
	 *
	 * @return string HTML Content
	 */
	private function get_turnstile_html( $action ) {
		$settings = SwpmSettings::get_instance();
		$siteKey  = $settings->get_value( 'swpm-addon-cft-site-key' );
		$secret   = $settings->get_value( 'swpm-addon-cft-secret-key' );

		$this->enqueue_turnstile_assets();

		if ( empty( trim( strval( $siteKey ) ) ) || empty( trim( strval( $secret ) ) ) ) {
			// Return the error message so the filter can output it in the correct place.
			return '<div class="swpm-cft-error-msg">' . esc_html( $this->get_config_error_msg() ) . '</div>';
		}

		$action = ! empty( $action ) ? $action : 'submit';

		ob_start();
		?>
        <div
            class="cf-turnstile swpm-cf-turnstile"
            id="<?php echo esc_attr( uniqid( "swpm-cf-turnstile-" ) ); ?>"
            data-sitekey="<?php echo esc_attr( $siteKey ); ?>"
            data-action="<?php echo esc_attr( $action ); ?>"
            data-callback="onSwpmCFTLoadCB"
            data-error-callback="onSwpmCFTLoadECB"
            data-response-field-name="swpm-cf-turnstile-response"
        ></div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Generates the necessary turnstile html for registration form
	 *
	 * @param $output string Value passed by filter hook.
	 *
	 * @return string Cloudflare Turnstile HTML as string.
	 */
	public function add_reg_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-registration-cft' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		$output = $this->get_turnstile_html( 'registration' );

		return $output;
	}

	/**
	 * Validates the Cloudflare Turnstile for registration form.
	 *
	 * @param $output
	 *
	 * @return string Validation message.
	 */
	public function validate_reg_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-registration-cft' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		// Since the registration captcha is enabled in settings, it MUST validate the captcha.
		$secret = $settings->get_value( 'swpm-addon-cft-secret-key' );

		if ( empty( $secret ) ) {
			wp_die( __( "Secret key for Cloudflare Turnstile is not configured.", 'simple-membership' ) );
		}

		// Initialize Turnstile object
		$turnstile = new \Cloudflare\Turnstile( $secret );

		$token = isset( $_POST["swpm-cf-turnstile-response"] ) && ! empty( $_POST["swpm-cf-turnstile-response"] ) ? $_POST["swpm-cf-turnstile-response"] : '';

		if ( $turnstile->verify( $token ) ) {
			//valid response
			$output = '';
		} else {
			$output = array(
				'succeeded' => false,
				'message' => $turnstile->get_last_error_msg(),
			);
		}

		return $output;
	}

	/**
	 * Generates the necessary turnstile html for login form
	 *
	 * @param $output string Value passed by filter hook.
	 *
	 * @return string Cloudflare Turnstile HTML as string.
	 */
	public function add_login_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-login-cft' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		$output = $this->get_turnstile_html( 'login' );

		return $output;
	}

	/**
	 * Validates the Cloudflare Turnstile for login form.
	 *
	 * @param $output .
	 *
	 * @return string Validation message.
	 */
	public function validate_login_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-login-cft' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		//Check if the submission is actually from SWPM login form. We don't want to catch the standard WP Login form's submission.
		$swpm_username = apply_filters( 'swpm_user_name', filter_input( INPUT_POST, 'swpm_user_name' ) );
		if ( empty( $swpm_username ) ) {
			//This is not a submission from SWPM login form.
			return $output;
		}

		// Since the login captcha is enabled in settings, it MUST validate the captcha.
		$secret = $settings->get_value( 'swpm-addon-cft-secret-key' );

		if ( empty( $secret ) ) {
			wp_die( __( "Secret key for Cloudflare Turnstile is not configured.", 'simple-membership' ) );
		}

		// Initialize Turnstile object
		$turnstile = new \Cloudflare\Turnstile( $secret );

		$token = isset( $_POST["swpm-cf-turnstile-response"] ) && ! empty( $_POST["swpm-cf-turnstile-response"] ) ? $_POST["swpm-cf-turnstile-response"] : '';

		if ( $turnstile->verify( $token ) ) {
			//valid response
			$output = '';
		} else {
			$output = array(
				'succeeded' => false,
				'message' => $turnstile->get_last_error_msg(),
			);
		}

		return $output;
	}

	/**
	 * Generates the necessary turnstile html for password reset form
	 *
	 * @param $output string Value passed by filter hook.
	 *
	 * @return string Cloudflare Turnstile HTML as string.
	 */
	public function add_pass_reset_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-pass-reset-cft' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		$output = $this->get_turnstile_html( 'password_reset' );

		return $output;
	}

	/**
	 * Validates the Cloudflare Turnstile for password reset form.
	 *
	 * @param $output .
	 *
	 * @return string Validation message.
	 */
	public function validate_pass_reset_form_recaptcha_code( $output ) {
		$settings = SwpmSettings::get_instance();
		$enabled  = $settings->get_value( 'swpm-addon-enable-pass-reset-cft' );
		if ( empty( $enabled ) ) {
			return $output;
		}

		// Since the password reset captcha is enabled in settings, it MUST validate the captcha.
		$secret = $settings->get_value( 'swpm-addon-cft-secret-key' );

		if ( empty( $secret ) ) {
			wp_die( __( "Secret key for Cloudflare Turnstile is not configured.", 'simple-membership' ) );
		}

		// Initialize Turnstile object
		$turnstile = new \Cloudflare\Turnstile( $secret );

		$token = isset( $_POST["swpm-cf-turnstile-response"] ) && ! empty( $_POST["swpm-cf-turnstile-response"] ) ? $_POST["swpm-cf-turnstile-response"] : '';

		if ( $turnstile->verify( $token ) ) {
			//valid response
			$output = '';
		} else {
			$output = array(
				'succeeded' => false,
				'message' => $turnstile->get_last_error_msg(),
			);
		}

		return $output;
	}
}
