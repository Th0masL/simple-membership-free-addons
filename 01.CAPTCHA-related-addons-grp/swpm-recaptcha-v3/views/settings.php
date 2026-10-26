<?php

$settings = SwpmSettings::get_instance();

$enable_registration_captcha = $settings->get_value('swpm-addon-enable-registration-captcha-v3');
$google_recaptcha_site_key = $settings->get_value('swpm-addon-google-recaptcha-v3-site-key');
$google_recaptcha_secret_key = $settings->get_value('swpm-addon-google-recaptcha-v3-secret-key');
$enable_login_captcha = $settings->get_value('swpm-addon-enable-login-captcha-v3');
$enable_pass_reset_captcha = $settings->get_value('swpm-addon-enable-pass-reset-captcha-v3');

?>
<h3><?php _e("Google reCAPTCHA V3 Addon Settings", "simple-membership")?></h3>
<p><?php _e("Read the", "simple-membership")?> <a href="https://simple-membership-plugin.com/simple-membership-google-recaptcha-v3-integration/" target="_blank"><?php _e("usage documentation", "simple-membership")?></a> <?php _e("to learn how to use the Google reCAPTCHA V3 Addon", "simple-membership")?></p>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row"><?php _e("Google reCAPTCHA v3 Site Key", "simple-membership")?></th>
            <td>
                <input name="swpm-addon-google-recaptcha-v3-site-key" type="text" size="60" value="<?php echo esc_attr($google_recaptcha_site_key); ?>"/>
                <p class="description"><?php _e("The site key for the reCAPTCHA v3 API.", "simple-membership")?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e("Google reCAPTCHA v3 Secret Key", "simple-membership")?></th>
            <td>
                <input name="swpm-addon-google-recaptcha-v3-secret-key" type="text" size="60" value="<?php echo esc_attr($google_recaptcha_secret_key); ?>"/>
                <p class="description"><?php _e("The secret key for the reCAPTCHA v3 API.", "simple-membership")?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e("Enable Google reCAPTCHA v3 on Registration Form", "simple-membership")?></th>
            <td><input type="checkbox" <?php echo esc_attr($enable_registration_captcha); ?> name="swpm-addon-enable-registration-captcha-v3" value="checked='checked'" />
                <p class="description"><?php _e("Enable or disable reCAPTCHA on the registration form.", "simple-membership")?></p>
            </td>
        </tr>
    	<tr>
    	    <th scope="row"><?php _e("Enable Google reCAPTCHA v3 on Login Form", "simple-membership")?></th>
    	    <td><input type="checkbox" <?php echo esc_attr($enable_login_captcha); ?> name="swpm-addon-enable-login-captcha-v3" value="checked='checked'" />
    		<p class="description"><?php _e("Enable or disable reCAPTCHA on the login form. Note: This option is not recommended if you are using the login form in a sidebar widget.", "simple-membership")?></p>
    	    </td>
    	</tr>
	    <?php 
        if ( version_compare( SIMPLE_WP_MEMBERSHIP_VER, '3.4.5', '>' ) ) {//This option requires SWPM v3.4.5
	    ?>
    	<tr>
    	    <th scope="row"><?php _e("Enable Google reCAPTCHA v3 on Password Reset Form", "simple-membership")?></th>
    	    <td><input type="checkbox" <?php echo esc_attr($enable_pass_reset_captcha); ?> name="swpm-addon-enable-pass-reset-captcha-v3" value="checked='checked'" />
    		<p class="description"><?php _e("Enable or disable reCAPTCHA on the password reset form.", "simple-membership")?></p>
    	    </td>
    	</tr>
	    <?php } ?>
    </tbody>
</table>