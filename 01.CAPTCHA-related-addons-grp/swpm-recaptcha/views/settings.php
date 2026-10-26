<h3>Google reCAPTCHA Addon Settings </h3>
<p>Read the <a href="https://simple-membership-plugin.com/simple-membership-and-google-recaptcha-integration/" target="_blank">usage documentation</a> to learn how to use the Google reCAPTCHA addon</p>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">Enable Google reCAPTCHA</th>
            <td><input type="checkbox" <?php echo $enable_captcha; ?> name="swpm-addon-enable-captcha" value="checked='checked'" />
                <p class="description">Enable/disable recaptcha integration.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Google reCAPTCHA Site Key</th>
            <td>
                <input name="swpm-addon-google-recaptcha-site-key" type="text" size="60" value="<?php echo $google_recaptcha_site_key; ?>"/>
                <p class="description">The site key for the reCAPTCHA API.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Google reCAPTCHA Secret Key</th>
            <td>
                <input name="swpm-addon-google-recaptcha-secret-key" type="text" size="60" value="<?php echo $google_recaptcha_secret_key; ?>"/>
                <p class="description">The secret key for the reCAPTCHA API.</p>
            </td>
        </tr>
	<?php
	if ( version_compare( SIMPLE_WP_MEMBERSHIP_VER, '3.3.9', '>' ) ) {
	    ?>
    	<tr>
    	    <th scope="row">Enable Google reCAPTCHA on Login Form</th>
    	    <td><input type="checkbox" <?php echo $enable_login_captcha; ?> name="swpm-addon-enable-login-captcha" value="checked='checked'" />
    		<p class="description">Enable/disable recaptcha on login form. This option is not ideal if you are using the login form in a sidebar widget.</p>
    	    </td>
    	</tr>
	<?php } ?>   
	<?php
	if ( version_compare( SIMPLE_WP_MEMBERSHIP_VER, '3.4.5', '>' ) ) {//This option requires SWPM v3.4.5
	    ?>
    	<tr>
    	    <th scope="row">Enable Google reCAPTCHA on Password Reset Form</th>
    	    <td><input type="checkbox" <?php echo $enable_pass_reset_captcha; ?> name="swpm-addon-enable-pass-reset-captcha" value="checked='checked'" />
    		<p class="description">Enable/disable recaptcha on password reset form.</p>
    	    </td>
    	</tr>
	<?php } ?>
	<tr>
	    <th scope="row">reCaptcha Theme</th>
	    <td>
		<select name="swpm-addon-recaptcha-theme">
		    <option value="light">Light</option>
		    <option value="dark"<?php echo $recaptcha_theme === 'dark' ? ' selected' : ''; ?>>Dark</option>
		</select>
		<p class="description">Select reCaptcha theme.</p>
	    </td>
	</tr>
	<tr>
	    <th scope="row">reCaptcha Size</th>
	    <td>
		<select name="swpm-addon-recaptcha-size">
		    <option value="normal">Normal</option>
		    <option value="compact"<?php echo $recaptcha_size === 'compact' ? ' selected' : ''; ?>>Compact</option>
		</select>
		<p class="description">Select reCaptcha size.</p>
	    </td>
	</tr>

    </tbody>
</table>