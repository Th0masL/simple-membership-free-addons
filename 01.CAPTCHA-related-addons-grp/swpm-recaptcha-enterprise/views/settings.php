<h3>
    <?php _e('Google reCAPTCHA Enterprise Addon Settings ', 'simple-membership') ?>
</h3>
<p><?php _e('Read the', 'simple-membership') ?> <a href="https://simple-membership-plugin.com/simple-membership-google-recaptcha-enterprise-option/" target="_blank"><?php _e('usage documentation', 'simple-membership') ?></a> <?php _e('to learn how to use the Google reCAPTCHA Enterprise addon', 'simple-membership') ?></p>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <?php _e('Google reCAPTCHA Enterprise Key ID', 'simple-membership') ?>
            </th>
            <td>
                <input name="swpm-addon-google-recaptcha-ent-key-id" type="text" size="60" value="<?php echo $google_recaptcha_key_id; ?>"/>
                <p class="description">
                    <?php _e('The key id for the reCAPTCHA enterprise API.', 'simple-membership') ?>
                </p>
            </td>
        </tr>
		<tr>
            <th scope="row">
                <?php _e('Google reCAPTCHA Enterprise Project ID', 'simple-membership') ?>
            </th>
            <td>
                <input name="swpm-addon-google-recaptcha-ent-project-id" type="text" size="60" value="<?php echo $google_recaptcha_project_id; ?>"/>
                <p class="description">
                    <?php _e('Project Id for Google Cloud.', 'simple-membership') ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
	            <?php _e('Google reCAPTCHA Enterprise API Key.', 'simple-membership') ?>
            </th>
            <td>
                <input name="swpm-addon-google-recaptcha-ent-api-key" type="text" size="60" value="<?php echo $google_recaptcha_api_key; ?>"/>
                <p class="description">
	                <?php _e('The google cloud api key.', 'simple-membership') ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
	            <?php _e('Enable Google reCAPTCHA Enterprise on Registration Form', 'simple-membership') ?>
            </th>
            <td><input type="checkbox" <?php echo $enable_captcha; ?> name="swpm-addon-enable-registration-captcha-ent" value="checked='checked'" />
                <p class="description">
	                <?php _e('Enable/disable recaptcha integration.', 'simple-membership') ?>
                </p>
            </td>
        </tr>
	<?php
	if ( version_compare( SIMPLE_WP_MEMBERSHIP_VER, '3.3.9', '>' ) ) {
	    ?>
    	<tr>
    	    <th scope="row">
		        <?php _e('Enable Google reCAPTCHA Enterprise on Login Form', 'simple-membership') ?>
            </th>
    	    <td><input type="checkbox" <?php echo $enable_login_captcha; ?> name="swpm-addon-enable-login-captcha-ent" value="checked='checked'" />
                <p class="description">
                    <?php _e('Enable/disable recaptcha on login form. This option is not ideal if you are using the login form in a sidebar widget.', 'simple-membership') ?>
                </p>
    	    </td>
    	</tr>
	<?php } ?>
	<?php
	if ( version_compare( SIMPLE_WP_MEMBERSHIP_VER, '3.4.5', '>' ) ) {//This option requires SWPM v3.4.5
	    ?>
    	<tr>
    	    <th scope="row">
		        <?php _e('Enable Google reCAPTCHA Enterprise on Password Reset Form', 'simple-membership') ?>
            </th>
    	    <td><input type="checkbox" <?php echo $enable_pass_reset_captcha; ?> name="swpm-addon-enable-pass-reset-captcha-ent" value="checked='checked'" />
    		<p class="description">
			    <?php _e('Enable/disable recaptcha on password reset form.', 'simple-membership') ?>
            </p>
    	    </td>
    	</tr>
	<?php } ?>
    <tr>
        <th scope="row">
	        <?php _e('Google reCAPTCHA Enterprise Badge Location', 'simple-membership') ?>
        </th>
        <td>
            <select name="swpm-addon-google-recaptcha-ent-badge-location">
                <option value="bottomright" <?php echo $badge_location == 'bottomright' ? ' selected' : '';?>><?php _e('Bottom Right', 'simple-membership') ?></option>
                <option value="bottomleft" <?php echo $badge_location == 'bottomleft' ? ' selected' : '';?>><?php _e('Bottom Left', 'simple-membership') ?></option>
            </select>
            <p class="description">
	            <?php _e('Select where the recaptcha badge should located.', 'simple-membership') ?>
            </p>
        </td>
    </tr>
<!--
    <tr>
	    <th scope="row">reCaptcha Enterprise Theme</th>
	    <td>
		<select name="swpm-addon-recaptcha-ent-theme">
		    <option value="light">Light</option>
		    <option value="dark"<?php /*echo $recaptcha_theme === 'dark' ? ' selected' : ''; */?>>Dark</option>
		</select>
		<p class="description">Select reCaptcha Enterprise theme.</p>
	    </td>
	</tr>
	<tr>
	    <th scope="row">reCaptcha Enterprise Size</th>
	    <td>
		<select name="swpm-addon-recaptcha-ent-size">
		    <option value="normal">Normal</option>
		    <option value="compact"<?php /*echo $recaptcha_size === 'compact' ? ' selected' : ''; */?>>Compact</option>
		</select>
		<p class="description">Select reCaptcha Enterprise size.</p>
	    </td>
	</tr>
-->

    </tbody>
</table>