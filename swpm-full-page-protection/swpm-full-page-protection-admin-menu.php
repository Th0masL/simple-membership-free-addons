<?php

add_action('swpm_after_main_admin_menu', 'swpm_fpp_do_admin_menu');

function swpm_fpp_do_admin_menu($menu_parent_slug) {
    $permission = 'manage_options';
    if (defined('SWPM_MANAGEMENT_PERMISSION')){
        $permission = SWPM_MANAGEMENT_PERMISSION;
    }
    add_submenu_page($menu_parent_slug, __("Full Page Protection", 'simple-membership'), __("Full Page Protection", 'simple-membership'), $permission, 'swpm-fpp', 'swpm_fpp_settings_page');

}

function swpm_fpp_settings_page() {
    wp_enqueue_script('swpm_fpp_admin_script', SWPM_FPP_PROTECTION_URL . '/assets/js/swpm_fpp_admin.js', array(), SWPM_FPP_PROTECTION_VER, true);
    wp_enqueue_style( 'swpm_fpp_common_style', SWPM_FPP_PROTECTION_URL . '/assets/css/swpm_fpp_common.css', array(), SWPM_FPP_PROTECTION_VER, 'all' );

    echo '<div class="wrap">';
    echo '<h2>Full Page Protection AddOn Settings</h2>';

    echo '<div id="poststuff"><div id="post-body">';

    if (isset($_POST['swpm_fpp_settings_update'])) {
        // Check if the nonce is valid.
        check_admin_referer('swpm_fpp_settings_update_nonce_action');

        $options = array(
            'prot_alt_post_enabled' => isset($_POST["prot_alt_post_enabled"]) ? '1' : '',
            'prot_alt_page_enabled' => isset($_POST["prot_alt_page_enabled"]) ? '1' : '',
            'prot_alt_cpt_enabled' => isset($_POST["prot_alt_cpt_enabled"]) ? '1' : '',
            'lockdown_specific_page_url' => isset($_POST["lockdown_specific_page_url"]) ? '1' : '',
            'urls_to_lockdown' => isset($_POST["urls_to_lockdown"]) ? sanitize_text_field($_POST["urls_to_lockdown"]) : '',
            'url_patterns_to_lockdown' => isset($_POST["url_patterns_to_lockdown"]) ? sanitize_text_field($_POST["url_patterns_to_lockdown"]) : '',
            'lockdown_sidewide' => isset($_POST["lockdown_sidewide"]) ? '1' : '',
            'prot_alt_show_header_footer' => isset($_POST["prot_alt_show_header_footer"]) ? '1' : '',

            'prot_auto_redirection' => isset($_POST["prot_auto_redirection"]) ? sanitize_text_field($_POST["prot_auto_redirection"]) : '',
            'prot_auto_redirection_to_specific_page_url' => isset($_POST["prot_auto_redirection_to_specific_page_url"]) ? sanitize_url($_POST["prot_auto_redirection_to_specific_page_url"]) : '',
        );
        update_option('swpm_fpp_addon_settings', $options); //store the results in WP options table

        echo '<div id="message" class="updated fade"><p>';
        echo 'Settings updated!';
        echo '</p></div>';
    }

    $emp_options = get_option('swpm_fpp_addon_settings');
    $prot_alt_post_enabled = isset($emp_options['prot_alt_post_enabled'])? $emp_options['prot_alt_post_enabled']: '';
    $prot_alt_page_enabled = isset($emp_options['prot_alt_page_enabled'])? $emp_options['prot_alt_page_enabled']: '';
    $prot_alt_cpt_enabled = isset($emp_options['prot_alt_cpt_enabled'])? $emp_options['prot_alt_cpt_enabled']: '';

    $lockdown_specific_page_url = isset($emp_options['lockdown_specific_page_url'])? $emp_options['lockdown_specific_page_url']: '';
    $urls_to_lockdown = isset($emp_options['urls_to_lockdown'])? $emp_options['urls_to_lockdown']: '';
    $url_patterns_to_lockdown = isset($emp_options['url_patterns_to_lockdown'])? $emp_options['url_patterns_to_lockdown']: '';
    $lockdown_sidewide = isset($emp_options['lockdown_sidewide'])? $emp_options['lockdown_sidewide']: '';

    $prot_alt_show_header_footer = isset($emp_options['prot_alt_show_header_footer'])? $emp_options['prot_alt_show_header_footer']: '';

    // Auto redirection radio settings
	$prot_auto_redirection = isset($emp_options['prot_auto_redirection']) ? sanitize_text_field($emp_options['prot_auto_redirection']) : '';
    if( empty ($prot_auto_redirection) ){
        // Set default value to 'no-redirect' if the value is empty.
        $prot_auto_redirection = 'no-redirect';
    }
    // Specific page URL for redirection
	$prot_auto_redirection_to_specific_page_url = isset($emp_options['prot_auto_redirection_to_specific_page_url']) ? sanitize_url($emp_options['prot_auto_redirection_to_specific_page_url']) : '';
    ?>

    <p class="swpm-grey-box">
        Read the <a href="https://simple-membership-plugin.com/full-page-protection-addon-simple-membership/" target="_blank">usage documentation</a> to learn how to use the full page protection addon.
    </p>

    <form method="post" action="">

        <div class="postbox">
            <h3 class="hndle"><label for="title">Protection Preferences</label></h3>
            <div class="inside">

                <table class="form-table">

                    <tr valign="top"><td width="25%" align="left">
                            Handle Post Protection
                        </td><td align="left">
                            <input name="prot_alt_post_enabled" type="checkbox"<?php if ($prot_alt_post_enabled != '') echo ' checked="checked"'; ?> value="1"/>
                            <p class="description">Check this if you want to allow this addon to apply the full page protection to your posts.</p>
                        </td>
                    </tr>
                    <tr valign="top"><td width="25%" align="left">
                            Handle Page Protection
                        </td><td align="left">
                            <input name="prot_alt_page_enabled" type="checkbox"<?php if ($prot_alt_page_enabled != '') echo ' checked="checked"'; ?> value="1"/>
                            <p class="description">Check this if you want to allow this addon to apply the full page protection to your pages.</p>
                        </td>
                    </tr>
                    <tr valign="top"><td width="25%" align="left">
                            Handle Custom Post Type Protection
                        </td><td align="left">
                            <input name="prot_alt_cpt_enabled" type="checkbox"<?php if ($prot_alt_cpt_enabled != '') echo ' checked="checked"'; ?> value="1"/>
                            <p class="description">Check this if you want to allow this addon to apply the full page protection to your custom post type posts.</p>
                        </td>
                    </tr>

                </table>
            </div>
        </div>

        <div class="postbox">
            <h3 class="hndle"><label for="title">Site-wide/Specific Page URL Lockdown</label></h3>
            <div class="inside">

                <table class="form-table">
                    <tbody id="lockdown_specific_url_settings_fields" class="<?php echo !empty($lockdown_sidewide) ? 'disabled' : ''; ?>">
                        <tr valign="top">
                            <td width="25%" align="left">
                                Lockdown Specific Page URLs
                            </td>
                            <td align="left">
                                <input name="lockdown_specific_page_url" type="checkbox"<?php if ($lockdown_specific_page_url != '') echo ' checked="checked"'; ?> value="1"/>
                                <p class="description">Check this if you want to lockdown/protect some specific URLs from anonymous users. This is useful if you want to protect some URLs that are not a normal post or page.</p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <td width="25%" align="left">
                                URLs to Lockdown
                            </td>
                            <td align="left">
                                <textarea name="urls_to_lockdown" id="urls_to_lockdown" cols="83" rows="3"><?php echo $urls_to_lockdown; ?></textarea>
                                <p class="description">Add the URLs (separated by comma) that will be protected from anonymous users.</p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <td width="25%" align="left">
                                URL Patterns to Lockdown
                            </td>
                            <td align="left">
                                <textarea name="url_patterns_to_lockdown" id="url_patterns_to_lockdown" cols="83" rows="3"><?php echo $url_patterns_to_lockdown; ?></textarea>
                                <p class="description">Add the URL patterns (separated by comma) that will be protected. Example: a value of /products/ in this field will protect both http://example.com/products/one and http://example.com/products/two URLs. Be careful when you use this feature so you don't accidentally protect URLs un-intentionally.</p>
                            </td>
                        </tr>
                    </tbody>
                    <tbody>
                        <!-- Horizontal line row -->
                        <tr>
                            <td colspan="2"><hr></td>
                        </tr>
                        <tr>
                            <td width="25%" align="left">
                                <?php esc_html_e(__('Enable Site-wide Full Page Protection', 'simple-membership')) ?>
                            </td>
                            <td align="left">
                                <input name="lockdown_sidewide" id="lockdown_sidewide_checkbox" type="checkbox" <?php echo $lockdown_sidewide != '' ? 'checked="checked"' : '' ?> value="1"/>
                                <p class="description"><?php esc_html_e('Enable this to lock down all pages of the site, except essential system pages such as the login, registration, password reset, and join-us pages. If you have enabled auto redirection to a specific page, the redirect URL will also be excluded from the site-wide protection.', 'simple-membership') ?></p>
                            </td>
                        </tr>
                    </tbody>                    
                </table>
            </div>
        </div>

        <!-- Auto redirection settings-->
        <div class="postbox">
            <h3 class="hndle"><label><?php _e('Redirection Settings', 'simple-membership') ?></label></h3>
            <div class="inside">
                <p class="description">
                    <?php _e("When a visitor lands on a restricted page, the plugin typically displays a 'content is restricted' message. You can use this setting to auto redirect the visitor to the login page or any other page you specify.", "simple-membership") ?>
                </p>
                <table class="form-table">
                    <tr>
                        <td width="25%" style="vertical-align: top;"><?php _e('Auto Redirect Option', 'simple-membership') ?></td>
                        <td>
                            <div>
                                <input type="radio" name="prot_auto_redirection" id="prot_auto_redirection_no_redirection" value="no-redirect" <?php echo $prot_auto_redirection == 'no-redirect' ? 'checked="checked"' : '' ?>>
                                <label for="prot_auto_redirection_no_redirection"><?php _e('No redirect', 'simple-membership')?></label><br>
                                <input type="radio" name="prot_auto_redirection" id="prot_auto_redirection_to_login_page" value="login-page" <?php echo $prot_auto_redirection == 'login-page' ? 'checked="checked"' : '' ?>>
                                <label for="prot_auto_redirection_to_login_page"><?php _e('Redirect to the login page', 'simple-membership')?></label><br>
                                <input type="radio" name="prot_auto_redirection" id="prot_auto_redirection_to_specific_page" value="specific-page" <?php echo $prot_auto_redirection == 'specific-page' ? 'checked="checked"' : '' ?>>
                                <label for="prot_auto_redirection_to_specific_page"><?php _e('Redirect to a specific page', 'simple-membership')?></label>
                            </div>
                            <div style="margin-top: 12px; display: <?php echo $prot_auto_redirection == 'specific-page' ? 'block' : 'none'; ?>;" id="prot_auto_redirection_to_specific_page_url_wrap">
                                <input type="url" name="prot_auto_redirection_to_specific_page_url" id="prot_auto_redirection_to_specific_page_url" size="100"  value="<?php echo esc_url($prot_auto_redirection_to_specific_page_url); ?>">
                                <p class="description"><?php _e('Enter the URL of the page where you want to redirect the visitor.', 'simple-membership') ?></p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h3 class="hndle"><label for="title">Other Settings</label></h3>
            <div class="inside">
                <table class="form-table">
                    <tr valign="top"><td width="25%" align="left">
                            Show Site Header and Footer (Experimental)
                        </td><td align="left">
                            <input name="prot_alt_show_header_footer" type="checkbox"<?php if ($prot_alt_show_header_footer != '') echo ' checked="checked"'; ?> value="1"/>
                            <p class="description">
                                <?php _e("This feature may not work on all themes. Enable this if you want to show the site header and footer with the protection message. Your theme must support WordPress's standard get_header() and get_footer() functions for this option to work.", "simple-membership") ?>
                            </p>
                        </td>
                    </tr>

                </table>
            </div>
        </div>
        
        <?php wp_nonce_field('swpm_fpp_settings_update_nonce_action') ?>

        <div class="submit">
            <input type="submit" class="button-primary" name="swpm_fpp_settings_update" value="Update" />
        </div>
    </form>
    <?php
    echo '</div></div>';
    echo '</div>';
}