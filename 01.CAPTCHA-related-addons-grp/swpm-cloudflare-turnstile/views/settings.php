<?php

$settings = SwpmSettings::get_instance();

$enable_registration_cft = $settings->get_value( 'swpm-addon-enable-registration-cft' );
$cft_site_key            = $settings->get_value( 'swpm-addon-cft-site-key' );
$cft_secret_key          = $settings->get_value( 'swpm-addon-cft-secret-key' );
$enable_login_cft        = $settings->get_value( 'swpm-addon-enable-login-cft' );
$enable_pass_reset_cft   = $settings->get_value( 'swpm-addon-enable-pass-reset-cft' );
$cft_load_scripts_global = $settings->get_value( 'swpm-addon-cft-load-scripts-globally' );

$cft_secret_key_masked = !empty($cft_secret_key) ? str_repeat('•', 20) : '';

?>
<h3><?php _e( "Cloudflare Turnstile Addon Settings", "simple-membership" ) ?></h3>
<p><?php _e( "Read the", "simple-membership" ) ?> <a
            href="https://simple-membership-plugin.com/simple-membership-cloudflare-turnstile-integration/"
            target="_blank"><?php _e( "usage documentation", "simple-membership" ) ?></a> <?php _e( "to learn how to use the Cloudflare Turnstile Addon", "simple-membership" ) ?>
</p>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><?php _e( "Cloudflare Turnstile Site Key", "simple-membership" ) ?></th>
        <td>
            <input name="swpm-addon-cft-site-key" type="text" size="60"
                   value="<?php echo esc_attr( $cft_site_key ); ?>"/>
            <p class="description"><?php _e( "The site key for the Cloudflare Turnstile API.", "simple-membership" ) ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e( "Cloudflare Turnstile Secret Key", "simple-membership" ) ?></th>
        <td>
            <input name="swpm-addon-cft-secret-key" type="text" size="60" value="" placeholder="<?php echo esc_attr( $cft_secret_key_masked ); ?>"/>
            <p class="description"><?php _e( "The secret key for the Cloudflare Turnstile API.", "simple-membership" ) ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e( "Enable Cloudflare Turnstile on Registration Form", "simple-membership" ) ?></th>
        <td><input type="checkbox" <?php echo esc_attr( $enable_registration_cft ); ?>
                   name="swpm-addon-enable-registration-cft" value="checked='checked'"/>
            <p class="description"><?php _e( "Enable or disable Cloudflare Turnstile on the registration form.", "simple-membership" ) ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e( "Enable Cloudflare Turnstile on Login Form", "simple-membership" ) ?></th>
        <td><input type="checkbox" <?php echo esc_attr( $enable_login_cft ); ?>
                   name="swpm-addon-enable-login-cft" value="checked='checked'"/>
            <p class="description"><?php _e( "Enable or disable Cloudflare Turnstile on the login form. Note: This option is not recommended if you are using the login form in a sidebar widget.", "simple-membership" ) ?></p>
        </td>
    </tr>
	<?php
	if ( version_compare( SIMPLE_WP_MEMBERSHIP_VER, '3.4.5', '>' ) ) {//This option requires SWPM v3.4.5
		?>
        <tr>
            <th scope="row"><?php _e( "Enable Cloudflare Turnstile on Password Reset Form", "simple-membership" ) ?></th>
            <td><input type="checkbox" <?php echo esc_attr( $enable_pass_reset_cft ); ?>
                       name="swpm-addon-enable-pass-reset-cft" value="checked='checked'"/>
                <p class="description"><?php _e( "Enable or disable Cloudflare Turnstile on the password reset form.", "simple-membership" ) ?></p>
            </td>
        </tr>
	<?php } ?>
    <tr>
        <th scope="row"><?php _e( "Load Turnstile Scripts on All Pages", "simple-membership" ) ?></th>
        <td><input type="checkbox" <?php echo esc_attr( $cft_load_scripts_global ); ?>
                   name="swpm-addon-cft-load-scripts-globally" value="checked='checked'"/>
            <p class="description"><?php _e( "Enable this if your theme loads pages via AJAX and the Turnstile widget only appears after refreshing the page. Leave it disabled otherwise.", "simple-membership" ) ?></p>
        </td>
    </tr>
    </tbody>
</table>