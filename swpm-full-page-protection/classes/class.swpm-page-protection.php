<?php

class SWPM_FPP_Protection {

    public function __construct() {

        if (class_exists('SimpleWpMembership')) {
            add_action('wp', array(&$this, 'initialize'));
        }
    }

    public function initialize() {

        $emp_options = get_option('swpm_fpp_addon_settings');
        $prot_alt_post_enabled = isset($emp_options['prot_alt_post_enabled']) ? $emp_options['prot_alt_post_enabled'] : '';
        $prot_alt_page_enabled = isset($emp_options['prot_alt_page_enabled']) ? $emp_options['prot_alt_page_enabled'] : '';
        $prot_alt_cpt_enabled = isset($emp_options['prot_alt_cpt_enabled']) ? $emp_options['prot_alt_cpt_enabled'] : '';

        $lockdown_specific_page_url = isset($emp_options['lockdown_specific_page_url']) ? $emp_options['lockdown_specific_page_url'] : '';
        $lockdown_sidewide = isset($emp_options['lockdown_sidewide']) ? $emp_options['lockdown_sidewide'] : '';

        if (!empty($prot_alt_post_enabled)) {
            $this->check_post_protection();
        }
        if (!empty($prot_alt_page_enabled)) {
            $this->check_page_protection();
        }
        if (!empty($prot_alt_cpt_enabled)) {
            $this->check_custom_post();
        }

        if(!empty($lockdown_sidewide)){
            $this->check_sitewide_lockdown();
        } else if (!empty($lockdown_specific_page_url)){ //Check if specific URL lockdown is enabled.
            $this->check_specific_url_lockdown();
        }
    }

    public function check_sitewide_lockdown(){
        //Lock down all urls for anonymous users (doesn't apply to logged-in members) excluding wp system pages.

        $swpm_auth = SwpmAuth::get_instance();
        if ($swpm_auth->is_logged_in() && !$swpm_auth->is_expired_account()) {
            //Member is logged-in and account is "not expired". So this feture doesn't apply.
            return;
        }

        if (!class_exists('SwpmAccessControl')) {
            return; // Core swpm plugin is missing.
        }

        $apply_protection = false;

        if (!SwpmAccessControl::is_current_url_a_system_page()) {
            // First check if auto redirection to specific url is enabled or not.
            $emp_options = get_option('swpm_fpp_addon_settings');
            $prot_auto_redirection = isset($emp_options['prot_auto_redirection']) ? sanitize_text_field($emp_options['prot_auto_redirection']): '';
            $specific_page_redirect_url = isset($emp_options['prot_auto_redirection_to_specific_page_url'])? sanitize_url($emp_options['prot_auto_redirection_to_specific_page_url']): '';

            if ($prot_auto_redirection == 'specific-page' && !empty($specific_page_redirect_url)){
                // Check if the currect page url matches with the provided url. Dont apply protection if matches.
                $apply_protection = ! SwpmMiscUtils::compare_url_without_http($specific_page_redirect_url, SwpmMiscUtils::get_current_page_url());
            } else {
                $apply_protection = true;
            }
        }

        if ( $apply_protection ){
            if ( $swpm_auth->is_expired_account() ){
                //An expired members. Show the restricted message (which will exit after the message).
                swpm_fpp_show_content_restricted_msg();
            }

            //Protect the URL from anonymous users.
            swpm_fpp_show_not_logged_msg();
        }
    }

    public function check_specific_url_lockdown(){
        //Lock down specified URLs for anonymous users (doesn't apply to logged-in members).

        $swpm_auth = SwpmAuth::get_instance();
        if ($swpm_auth->is_logged_in() && !$swpm_auth->is_expired_account()) {
            //Member is logged-in and account is "not expired". So this feture doesn't apply.
            return;
        }

        //User is not logged-in. Lets check if this URL should be protected or not.
        $apply_url_protection = false;

        $emp_options = get_option('swpm_fpp_addon_settings');
        $urls_to_lockdown = isset($emp_options['urls_to_lockdown'])? $emp_options['urls_to_lockdown']: '';
        $url_patterns_to_lockdown = isset($emp_options['url_patterns_to_lockdown'])? $emp_options['url_patterns_to_lockdown']: '';
        if ( empty($urls_to_lockdown) && empty($url_patterns_to_lockdown) ){
            //Nothing to lockdown. Bail out.
            return;
        }

        //Get the current URL and trim it.
        //NOTE: We always do rtrim(strtolower(trim($url)), "/") to make sure we are comparing the URLs in a consistent way.
        $current_url = SwpmMiscUtils::get_current_page_url();
        $current_url = strtolower($current_url);
        $trimmed_cur_url = rtrim($current_url, "/");

        /* Check Specific URLs (full URL match) */
        $included_urls = $urls_to_lockdown;
        $included_urls = explode(',', trim($included_urls));
        foreach ($included_urls as $url) {
            $url = trim($url);
            $url = strtolower($url);
            $url = rtrim($url, "/");

            //Do exact match
            if (strcmp($trimmed_cur_url, $url) == 0){
                //This URL should be locked down
                $apply_url_protection = true;
                break;
            }

        }

        /* Check if any URL patterns (Partial URLs) need to be locked down */
        //Only check the pattern match if a specific URL hasn't matched for lockdown already.
        if( !$apply_url_protection ){
            //Go ahead and check URL pattern match
            if( empty($url_patterns_to_lockdown) ){
                //Nothing to lockdown. Bail out. Have this check here to avoid PHP version related issue.
                return;
            }
            $url_patterns = explode(',', trim($url_patterns_to_lockdown));
            foreach ($url_patterns as $pattern) {
                //NOTE: We always do rtrim(strtolower(trim($url)), "/") to make sure we are comparing the URLs in a consistent way.
                $pattern = trim($pattern);
                $pattern = strtolower($pattern);
                $pattern = rtrim($pattern, "/");

                if (empty($pattern)) {
                    //Empty pattern. Skip this iteration.
                    continue;
                }
                //Do partial match
                if (stripos($trimmed_cur_url, $pattern) !== false) {
                    //Found a match. This URL should be locked down
                    $apply_url_protection = true;
                    break;
                }

            }
        }

        /* Apply the protection if any specific URL or URL pattern has matched. */
        if ($apply_url_protection){

            if ( $swpm_auth->is_expired_account() ){
                //An expired members. Show the restricted message (which will exit after the message).
                swpm_fpp_show_content_restricted_msg();
            }

            //Protect the URL from anonymous users.
            swpm_fpp_show_not_logged_msg();
        }
    }

    public function check_post_protection() {
        //Checks if a singular post is being displayed, which is the case when one of the following returns true: is_single(), is_page() or is_attachment().
        if (!is_singular()) {
            //This is not a single post/page view. So no need to do our check.
            return;
        }

        global $post;
        $post_id = $post->ID;
        if (empty($post_id)) {
            //Lets try to get the post ID using an alternative method
            $current_page_url = SwpmMiscUtils::get_current_page_url();
            $post_id = url_to_postid($current_page_url);
        }

        //Get the full post object
        $post = get_post($post_id);
        if (empty($post)) {
            return true;
        }

        //Check if this user has permission to view this page
        $permission_to_view = true;

        $swpm_protection = SwpmProtection::get_instance();
        $swpm_auth = SwpmAuth::get_instance();
        $permission = SwpmPermission::get_instance($swpm_auth->get('membership_level'));

        //First, check if this is a protected post in the first place.
        if ($swpm_protection->is_protected($post_id)) {

            //This is a protected page so lets see if this user has access to this specific page

            if (!$swpm_auth->is_logged_in()) {
                //Member not even logged in so this page needs to be protected and show the login message.
                swpm_fpp_show_not_logged_msg();
            } else {
                //Member is logged in so check user specific permission

                if ($swpm_auth->is_expired_account()) {
                    //Account is expired. No need to check any other condition. Show expiry message and exit.

                    //Check if this is a system page. If not a system page, then show the expired message and exit.
                    if ( !SwpmAccessControl::is_current_url_a_system_page() ){
                        swpm_fpp_show_account_expired_msg();
                    } else {
                        $permission_to_view = true;
                    }
                }

                if ($permission->is_permitted($post_id)) {
                    //Has permission to view
                    $permission_to_view = true;
                } else {
		    $permission_to_view = false;
		}
            }
        } else {
            //This page is not even protected so allow access.
            $permission_to_view = true;
        }

        if (!$permission_to_view) {
            swpm_fpp_show_content_restricted_msg();
        }
    }

    public function check_page_protection() {
        //Checks if a singular post is being displayed, which is the case when one of the following returns true: is_single(), is_page() or is_attachment().
        if (!is_singular()) {
            //This is not a single post/page view. So no need to do our check.
            return;
        }

        global $post;
        $post_id = $post->ID;
        if (empty($post_id)) {
            //Lets try to get the post ID using an alternative method
            $current_page_url = SwpmMiscUtils::get_current_page_url();
            $post_id = url_to_postid($current_page_url);
        }

        //Get the full post object
        $post = get_post($post_id);
        if (empty($post)) {
            return true;
        }

        //Check if this user has permission to view this page
        $permission_to_view = true;

        $swpm_protection = SwpmProtection::get_instance();
        $swpm_auth = SwpmAuth::get_instance();
        $permission = SwpmPermission::get_instance($swpm_auth->get('membership_level'));

        //First, check if this is a protected post in the first place.
        if ($swpm_protection->is_protected($post_id)) {

            //This is a protected page so lets see if this user has access to this specific page

            if (!$swpm_auth->is_logged_in()) {
                //Member not even logged in so this page needs to be protected and show the login message.
                swpm_fpp_show_not_logged_msg();
            } else {
                //Member is logged in so check user specific permission

                if ($swpm_auth->is_expired_account()) {
                    //Account is expired. No need to check any other condition. Show expiry message and exit.

                    //Check if this is a system page. If not a system page, then show the expired message and exit.
                    if ( !SwpmAccessControl::is_current_url_a_system_page() ){
                        swpm_fpp_show_account_expired_msg();
                    } else {
                        $permission_to_view = true;
                    }
                }

                if ($permission->is_permitted($post_id)) {
                    //Has permission to view
                    $permission_to_view = true;
                } else {
		    $permission_to_view = false;
		}
            }
        } else {
            //This page is not even protected so allow access.
            $permission_to_view = true;
        }

        if (!$permission_to_view) {
            swpm_fpp_show_content_restricted_msg();
        }
    }

    private function check_custom_post() {
        //Check if individial post display page.
        if (!is_single()) {
            //This is not a single post (of any post type) view. So no need to do our check.
            return;
        }

        $args = array('public' => true, '_builtin' => false); //By setting '_builtin' to false, we will exclude the built-in WordPress post, page, and attachment.
        $post_types = get_post_types($args); //Lets get all the post types uses in this site.

        global $post;
        $post_id = $post->ID;
        if (empty($post_id)) {
            //Lets try to get the post ID using an alternative method
            $current_page_url = SwpmMiscUtils::get_current_page_url();
            $post_id = url_to_postid($current_page_url);
        }

        if (!in_array(get_post_type($post_id), $post_types)) {
            //This is not a custom post type so bail out.
            return;
        }

        //Check if this user has permission to view this page
        $permission_to_view = true;

        $swpm_protection = SwpmProtection::get_instance();
        $swpm_auth = SwpmAuth::get_instance();
        $permission = SwpmPermission::get_instance($swpm_auth->get('membership_level'));

        //First, check if this is a protected post in the first place.
        if ($swpm_protection->is_protected($post_id)) {

            //This is a protected page so lets see if this user has access to this specific page

            if (!$swpm_auth->is_logged_in()) {
                //Member not even logged in so this page needs to be protected and show the login message.
                swpm_fpp_show_not_logged_msg();
            } else {
                //Member is logged in so check user specific permission

                if ($swpm_auth->is_expired_account()) {
                    //Account is expired. No need to check any other condition. Show expiry message and exit.

                    //Check if this is a system page. If not a system page, then show the expired message and exit.
                    if ( !SwpmAccessControl::is_current_url_a_system_page() ){
                        swpm_fpp_show_account_expired_msg();
                    } else {
                        $permission_to_view = true;
                    }
                }

                if ($permission->is_permitted($post_id)) {
                    //Has permission to view
                    $permission_to_view = true;
                } else {
		    $permission_to_view = false;
		}
            }
        } else {
            //This page is not even protected so allow access.
            $permission_to_view = true;
        }

        if (!$permission_to_view) {
            swpm_fpp_show_content_restricted_msg();
        }
    }
}

function swpm_fpp_get_login_link() {

    $login_url = SwpmSettings::get_instance()->get_value('login-page-url');
    $joinus_url = SwpmSettings::get_instance()->get_value('join-us-page-url');
    if (empty($login_url) || empty($joinus_url)) {
        return '<span style="color:red;">Simple Membership is not configured correctly. The login page or the join us page URL is missing in the settings configuration. '
                . 'Please contact <a href="mailto:' . get_option('admin_email') . '">Admin</a>';
    }

    //Create the login message
    $filtered_login_url = apply_filters('swpm_get_login_link_url', $login_url); //Addons can override the login URL value using this filter.
    $login_msg = '';
    $login_msg .= __('Please', 'simple-membership') . ' <a class="swpm-login-link" href="' . $filtered_login_url . '">' . __('Login', 'simple-membership') . '</a>. ';
    $login_msg .= __('Not a Member?', 'simple-membership') . ' <a href="' . $joinus_url . '">' . __('Join Us', 'simple-membership') . '</a>';

    //Create the full protection message for not logged-in users.
    $not_logged_prot_msg = '';
    $not_logged_prot_msg .= '<div class="swpm_full_page_protection_not_logged_msg">';
    $protection_msg = __( 'You need to login to view this content. ', 'simple-membership' ) . $login_msg;
    $protection_msg = apply_filters( 'swpm_fpp_protected_content_msg', $protection_msg );
    $not_logged_prot_msg .= $protection_msg;
    $not_logged_prot_msg .= '</div>';

    return $not_logged_prot_msg;
}

function swpm_fpp_show_not_logged_msg() {
    $msg = swpm_fpp_get_login_link();
    $msg = apply_filters('swpm_fpp_account_not_logged_msg', $msg);
    $title = "This Content is Restricted";

    $emp_options = get_option('swpm_fpp_addon_settings');

	// First check if auto redirection enabled, redirect if set.
	$prot_auto_redirection = isset($emp_options['prot_auto_redirection']) ? sanitize_text_field($emp_options['prot_auto_redirection']): '';
	if (isset($prot_auto_redirection) && !empty($prot_auto_redirection)){
		// Auto redirection settings is enabled.
		switch ($prot_auto_redirection) {
			case 'login-page':
				// Redirect to the login page if a login page url is set in the settings.
				$login_page_url = SwpmSettings::get_instance()->get_value( 'login-page-url' );
				if ( ! empty( $login_page_url ) ) {
					SwpmMiscUtils::redirect_to_url($login_page_url);
					exit;
				} else {
                    // Login page URL is not set. Show error message.
                    $title = 'Simple Membership Configuration Error';
                    $msg = 'You have selected auto redirect to login page but the login page URL is not set in the Simple Membership settings. Please configure the login page URL in the settings.';
                    wp_die($msg, $title);
                }
				break;

			case 'specific-page':
				// Redirect to a specific page if URL is specified.
				$specific_page_redirect_url = isset($emp_options['prot_auto_redirection_to_specific_page_url'])? sanitize_url($emp_options['prot_auto_redirection_to_specific_page_url']): '';
				if (!empty($specific_page_redirect_url)){
					SwpmMiscUtils::redirect_to_url($specific_page_redirect_url);
					exit;
				} else {
                    // Specific page URL is not set. Show error message.
                    $title = 'Simple Membership Configuration Error';
                    $msg = 'You have selected auto redirect to a specific page but the specific page URL is not set in the settings. Please configure the specific page URL in the settings.';
                    wp_die($msg, $title);
                }
				break;

			default;
				// The option is set to 'No Redirect' or the setting was never set (fresh install), skip redirection.
				break;
		}
	}

    $prot_alt_show_header_footer = isset($emp_options['prot_alt_show_header_footer'])? $emp_options['prot_alt_show_header_footer']: '';

    if(!empty($prot_alt_show_header_footer)){
        //Show site header and footer with the protection message output.
        if(wp_is_block_theme()){
            swpm_fpp_lockdown_fse_template($msg);
        } else {
            swpm_fpp_lockdown_legacy_template($msg);
        }
        exit;
    } else {
        //Just the protection message output.
        wp_die($msg, $title);
    }

}

function swpm_fpp_show_account_expired_msg() {
    //Show account expiry message.
    $title = "This Content is Restricted";

    $text = _e('Your account has expired. ', 'simple-membership') .  SwpmMiscUtils::get_renewal_link();
    $error_msg = '<div class="swpm-post-account-expired-msg swpm-full-page-protection-account-expired-msg">'.$text.'</div>';
    $msg = apply_filters('swpm_account_expired_msg', $error_msg);

    $emp_options = get_option('swpm_fpp_addon_settings');
    $prot_alt_show_header_footer = isset($emp_options['prot_alt_show_header_footer'])? $emp_options['prot_alt_show_header_footer']: '';

    if(!empty($prot_alt_show_header_footer)){
        //Show site header and footer with the protection message output.
        if(wp_is_block_theme()){
            swpm_fpp_lockdown_fse_template($msg);
        } else {
            swpm_fpp_lockdown_legacy_template($msg);
        }
        exit;
    } else {
        //Just the protection message output.
        wp_die($msg, $title);
    }
}

function swpm_fpp_show_content_restricted_msg() {
    $msg = apply_filters("swpm_fpp_content_restricted_msg", "You do not have permission to view this content.");
    $title = apply_filters("swpm_fpp_content_restricted_title", "This Content is Restricted");

    $emp_options = get_option('swpm_fpp_addon_settings');
    $prot_alt_show_header_footer = isset($emp_options['prot_alt_show_header_footer'])? $emp_options['prot_alt_show_header_footer']: '';

    if(!empty($prot_alt_show_header_footer)){
        //Show site header and footer with the protection message output.
        if(wp_is_block_theme()){
            swpm_fpp_lockdown_fse_template($msg);
        } else {
            swpm_fpp_lockdown_legacy_template($msg);
        }
        exit;
    } else {
        //Just the protection message output.
        wp_die($msg, $title);
    }

}
