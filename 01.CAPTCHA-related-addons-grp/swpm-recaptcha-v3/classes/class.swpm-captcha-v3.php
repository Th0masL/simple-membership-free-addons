<?php

class SwpmCaptchaV3
{

    public function __construct()
    {
        if (class_exists('SimpleWpMembership')) {
            // Add code for settings page.
            add_action('swpm_addon_settings_section', array(&$this, 'settings_ui'));
            add_action('swpm_addon_settings_save', array(&$this, 'settings_save'));

            // Captcha for the registration form.
            add_filter('swpm_before_registration_submit_button', array($this, 'add_reg_form_recaptcha_code'));
            add_filter('swpm_validate_registration_form_submission', array($this, 'validate_reg_form_recaptcha_code'));

            // Captcha for the login form.
            add_filter('swpm_before_login_form_submit_button', array($this, 'add_login_form_recaptcha_code'));
            add_filter('swpm_validate_login_form_submission', array($this, 'validate_login_form_recaptcha_code'));

            // Captcha for the password reset form.
            add_filter('swpm_before_pass_reset_form_submit_button', array($this, 'add_pass_reset_form_recaptcha_code'));
            add_filter('swpm_validate_pass_reset_form_submission', array($this, 'validate_pass_reset_form_recaptcha_code'));
        }
    }

    /**
     * Renders the settings page content in 'SWPM Addon Settings' page.
     *
     * @return void
     */
    public function settings_ui()
    {
        require_once SWPM_CAPTCHA_V3_PATH . 'views/settings.php';
    }

    /**
     * Handle settings page form submission.
     *
     * @return void
     */
    public function settings_save()
    {
        $message = array(
            'succeeded' => true,
            'message' => '<p>' . __('reCAPTCHA Settings Updated!', 'simple-membership') . '</p>'
        );

        SwpmTransfer::get_instance()->set('status', $message);

        $enable_registration_captcha = filter_input(INPUT_POST, 'swpm-addon-enable-registration-captcha-v3');
        $site_key = filter_input(INPUT_POST, 'swpm-addon-google-recaptcha-v3-site-key');
        $secret_key = filter_input(INPUT_POST, 'swpm-addon-google-recaptcha-v3-secret-key');
        $enable_login_captcha = filter_input(INPUT_POST, 'swpm-addon-enable-login-captcha-v3');
        $enable_pass_reset_captcha = filter_input(INPUT_POST, 'swpm-addon-enable-pass-reset-captcha-v3');

        $settings = SwpmSettings::get_instance();

        $settings->set_value('swpm-addon-enable-registration-captcha-v3', empty($enable_registration_captcha) ? "" : $enable_registration_captcha);
        $settings->set_value('swpm-addon-google-recaptcha-v3-site-key', sanitize_text_field($site_key));
        $settings->set_value('swpm-addon-google-recaptcha-v3-secret-key', sanitize_text_field($secret_key));
        $settings->set_value('swpm-addon-enable-login-captcha-v3', empty($enable_login_captcha) ? "" : $enable_login_captcha);
        $settings->set_value('swpm-addon-enable-pass-reset-captcha-v3', empty($enable_pass_reset_captcha) ? "" : $enable_pass_reset_captcha);
        $settings->save();
    }

    /**
     * Generates the necessary html for Google reCaptcha V3
     *
     * @param $siteKey string Google reCaptcha Site Key.
     * @param $action string The context for captcha validation.
     *
     * @return string HTML Content
     */
    private function get_recaptcha_html($siteKey, $action)
    {
        $action = !empty($action) ? $action : 'submit';

        // Apply filter for custom marker if needed.
        // This can be used to add custom markers for the reCaptcha script.
        $script_custom_marker = apply_filters('swpm_recaptcha_v3_script_custom_marker', '');

        ob_start();
        ?>
        <script <?php echo $script_custom_marker; ?> src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($siteKey) ?>&onload=onGoogleCaptchaV3ScriptLoaded"></script>

        <input type="hidden" class="g-recaptcha-response" name="g-recaptcha-response" />

        <script <?php echo $script_custom_marker; ?>>
            function onGoogleCaptchaV3ScriptLoaded (){
                grecaptcha.ready(function() {
                    const captchaResponseInput = document.querySelector(".g-recaptcha-response");
                    grecaptcha.execute(
                        "<?php echo esc_attr($siteKey) ?>",
                        {
                            action: "<?php echo esc_attr($action); ?>"
                        }
                    )
                    .then(function(token) {
                        // Add your logic to submit to your backend server here.
                        captchaResponseInput.value = token
                        console.log(token) // Debug purpose only.
                    });
                });
            }
        </script>

        <?php
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Generates the necessary reCaptcha html for registration form
     *
     * @param $output string Value passed by filter hook.
     *
     * @return string Google reCaptcha V3 HTML as string.
     */
    public function add_reg_form_recaptcha_code($output)
    {
        $settings = SwpmSettings::get_instance();
        $enabled = $settings->get_value('swpm-addon-enable-registration-captcha-v3');
        if (empty($enabled)) {
            return $output;
        }

        $output = $this->get_recaptcha_html(
            $settings->get_value('swpm-addon-google-recaptcha-v3-site-key'),
            'registration'
        );
        return $output;
    }

    /**
     * Validates the Google reCaptcha for registration form.
     *
     * @param $output
     *
     * @return string Validation message.
     */
    public function validate_reg_form_recaptcha_code($output)
    {
        $settings = SwpmSettings::get_instance();
        $enabled = $settings->get_value('swpm-addon-enable-registration-captcha-v3');
        if (empty($enabled)) {
            return $output;
        }

        SwpmLog::log_simple_debug("[reCAPTCHA V3 Addon] Google Captcha is enabled. Lets validate captcha.", true);

        //Since the registration captcha is enabled in settings, it MUST validate the captcha.
        require_once SWPM_CAPTCHA_V3_PATH . 'libs/autoload.php';
        $secret = $settings->get_value('swpm-addon-google-recaptcha-v3-secret-key');

        if (empty($secret)){
            wp_die(__("Secret key for Google reCaptcha V3 is not configured.", 'simple-membership'));
        }

        //Initialize captcha object
        $reCaptcha = new \ReCaptcha\ReCaptcha($secret);

	    $token = isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : '';

        $resp = $reCaptcha->verify($token, $_SERVER[ "REMOTE_ADDR" ]);

        if ($resp->isSuccess()) { //valid reCAPTCHA response
            $output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA V3 Addon] Captcha validated successfully!", true);
        } else {
            $output = 'captcha error';
            SwpmLog::log_simple_debug("[reCAPTCHA V3 Addon] Captcha error!", false);
        }

        return $output;
    }

    /**
     * Generates the necessary reCaptcha html for login form
     *
     * @param $output string Value passed by filter hook.
     *
     * @return string Google reCaptcha V3 HTML as string.
     */
    public function add_login_form_recaptcha_code($output)
    {
        $settings = SwpmSettings::get_instance();
        $enabled = $settings->get_value('swpm-addon-enable-login-captcha-v3');
        if (empty($enabled)) {
            return $output;
        }

        $output = $this->get_recaptcha_html(
            $settings->get_value('swpm-addon-google-recaptcha-v3-site-key'),
            'login'
        );
        return $output;
    }

    /**
     * Validates the Google reCaptcha for login form.
     *
     * @param $output.
     *
     * @return string Validation message.
     */
    public function validate_login_form_recaptcha_code($output)
    {
        $settings = SwpmSettings::get_instance();
        $enabled = $settings->get_value('swpm-addon-enable-login-captcha-v3');
        if (empty($enabled)) {
            return $output;
        }

        //Check if the submission is actually from SWPM login form. We don't want to catch the standard WP Login form's submission.
        $swpm_username = apply_filters('swpm_user_name', filter_input(INPUT_POST, 'swpm_user_name'));
        if (empty($swpm_username)) {
            //This is not a submission from SWPM login form.
            return $output;
        }

        //Since the login captcha is enabled in settings, it MUST validate the captcha.
        require_once SWPM_CAPTCHA_V3_PATH . 'libs/autoload.php';
        $secret = $settings->get_value('swpm-addon-google-recaptcha-v3-secret-key');

        if (empty($secret)){
            wp_die(__("Secret key for Google reCaptcha V3 is not configured.", 'simple-membership'));
        }

        //Initialize captcha object
        $reCaptcha = new \ReCaptcha\ReCaptcha($secret);

	    $token = isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : '';

        $resp = $reCaptcha->verify($token, $_SERVER[ "REMOTE_ADDR" ]);
        if ($resp->isSuccess()) { //valid reCAPTCHA response
            $output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA V3 Addon] Login captcha validated successfully!", true);
        } else {
            $output = 'captcha error';
        }

        return $output;
    }

    /**
     * Generates the necessary reCaptcha html for password reset form
     *
     * @param $output string Value passed by filter hook.
     *
     * @return string Google reCaptcha V3 HTML as string.
     */
    public function add_pass_reset_form_recaptcha_code($output)
    {
        $settings = SwpmSettings::get_instance();
        $enabled = $settings->get_value('swpm-addon-enable-pass-reset-captcha-v3');
        if (empty($enabled)) {
            return $output;
        }

        $output = $this->get_recaptcha_html(
            $settings->get_value('swpm-addon-google-recaptcha-v3-site-key'),
            'password_reset'
        );
        return $output;
    }

    /**
     * Validates the Google reCaptcha for password reset form.
     *
     * @param $output.
     *
     * @return string Validation message.
     */
    public function validate_pass_reset_form_recaptcha_code($output)
    {
        $settings = SwpmSettings::get_instance();
        $enabled = $settings->get_value('swpm-addon-enable-pass-reset-captcha-v3');
        if (empty($enabled)) {
            return $output;
        }

        // Since the password reset captcha is enabled in settings, it MUST validate the captcha.
        require_once SWPM_CAPTCHA_V3_PATH . 'libs/autoload.php';
        $secret = $settings->get_value('swpm-addon-google-recaptcha-v3-secret-key');

        if (empty($secret)){
            wp_die(__("Secret key for Google reCaptcha V3 is not configured.", 'simple-membership'));
        }

        // Initialize captcha object.
        $reCaptcha = new \ReCaptcha\ReCaptcha($secret);

	    $token = isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : '';

        $resp = $reCaptcha->verify($token, $_SERVER[ "REMOTE_ADDR" ]);
        if ($resp->isSuccess()) { //valid reCAPTCHA response
            $output = '';
            SwpmLog::log_simple_debug("[reCAPTCHA V3 Addon] Password reset captcha validated successfully!", true);
        } else {
            $output = 'captcha error';
        }
        return $output;
    }
}
