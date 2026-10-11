<?php

/**
 * Description of SwpmCustomMessage
 */
class SwpmCustomMessage {

    public function __construct() {
        add_action('swpm_after_main_admin_menu', array(&$this, 'swpm_custom_msg_do_admin_menu'));
        add_action('admin_init', array(&$this, 'admin_init_hook'));
        add_filter('swpm_restricted_post_msg', array(&$this, 'swpm_restricted_post_msg'));
        add_filter('swpm_not_logged_in_post_msg', array(&$this, 'swpm_not_logged_in_post_msg'));
        add_filter('swpm_restricted_comment_msg', array(&$this, 'swpm_restricted_comment_msg'));
        add_filter('swpm_not_logged_in_comment_msg', array(&$this, 'swpm_not_logged_in_comment_msg'));
        add_filter('swpm_restricted_more_tag_msg', array(&$this, 'swpm_restricted_more_tag_msg'));
        add_filter('swpm_not_logged_in_more_tag_msg', array(&$this, 'swpm_not_logged_in_more_tag_msg'));

        add_filter('swpm_registration_success_msg', array(&$this, 'swpm_registration_success_msg'));
        add_filter('swpm_registration_email_activation_msg', array(&$this, 'swpm_registration_email_activation_msg'));

        add_filter('swpm_account_expired_msg', array(&$this, 'swpm_account_expired_msg'));
        add_filter('swpm_account_expired_more_tag_msg', array(&$this, 'swpm_account_expired_msg'));

        //Full page protection addon
	    add_filter('swpm_fpp_protected_content_msg',array(&$this,'swpm_fpp_protected_content_msg'));

        //Older posts protection addon
        add_filter('swpm_restricted_post_msg_older_post',array(&$this,'swpm_older_post_protected_content_msg'));
        add_filter('swpm_restricted_comment_older_post',array(&$this,'swpm_older_post_protected_content_msg'));

        add_filter('swpm_password_reset_success_msg',array(&$this,'swpm_password_reset_success_msg'));
        add_filter('swpm_ty_page_registration_msg_with_link',array(&$this,'swpm_ty_page_registration_msg_with_link'));
        add_filter('swpm_ty_page_registration_msg_no_link',array(&$this,'swpm_ty_page_registration_msg_no_link'));

		add_filter('swpm_mini_login_output_when_logged_in',array(&$this,'swpm_mini_login_output_when_logged_in'));
		add_filter('swpm_mini_login_output_when_not_logged_in',array(&$this,'swpm_mini_login_output_when_not_logged_in'));

        // Partial Protection Addon
        add_filter('swpm_pp_output_when_not_logged_in', array(&$this, 'swpm_pp_output_when_not_logged_in'));
        add_filter('swpm_pp_output_when_no_access', array(&$this, 'swpm_pp_output_when_no_access'));
        add_filter('swpm_pp_output_when_membership_level_restricted', array(&$this, 'swpm_pp_output_when_membership_level_restricted'));
        add_filter('swpm_pp_output_when_account_status_restricted', array(&$this, 'swpm_pp_output_when_account_status_restricted'));
    }

    public function swpm_older_post_protected_content_msg($output) {
        $key = 'swpm_older_post_protected_msg';
        $msg = SwpmCustomMessageSettings::get_instance()->get_value($key);
        if( empty($msg)){
            //Nothing has been customized for this message. Use the default value.
            return $output;
        }

        //Perform shortcode processing on the custom message.
        $msg = do_shortcode($msg);

        global $post;
        if (isset($post->post_date)){
            //Replace the dynamic tag: {post_published_date}
            $formatted_translated_publish_date = SwpmUtils::get_formatted_and_translated_date_according_to_wp_settings($post->post_date);
            $msg = str_replace('{post_published_date}', $formatted_translated_publish_date, $msg);
        }
        return $msg;
    }

    public function swpm_fpp_protected_content_msg($output) {
        return $this->dispatch_message('swpm_fpp_protected_msg', $output);
    }

    public function swpm_registration_email_activation_msg($output){
        return $this->dispatch_message('swpm_registration_email_activation_msg', $output);
    }

    public function swpm_registration_success_msg($output) {
        return $this->dispatch_message('swpm_registration_success_msg', $output);
    }

    public function swpm_account_expired_msg($output) {
        return $this->dispatch_message('swpm_account_expired_msg', $output);
    }

    public function swpm_restricted_post_msg($output) {
        return $this->dispatch_message('swpm_restricted_post_msg', $output);
    }

    public function swpm_not_logged_in_post_msg($output) {
        return $this->dispatch_message('swpm_not_logged_in_post_msg', $output);
    }

    public function swpm_restricted_comment_msg($output) {
        return $this->dispatch_message('swpm_restricted_comment_msg', $output);
    }

    public function swpm_not_logged_in_comment_msg($output) {
        return $this->dispatch_message('swpm_not_logged_in_comment_msg', $output);
    }

    public function swpm_restricted_more_tag_msg($output) {
        return $this->dispatch_message('swpm_restricted_more_tag_msg', $output);
    }

    public function swpm_not_logged_in_more_tag_msg($output) {
        return $this->dispatch_message('swpm_not_logged_in_more_tag_msg', $output);
    }

	public function swpm_password_reset_success_msg($output){
		return $this->dispatch_message('swpm_password_reset_success_msg', $output);
	}

	public function swpm_ty_page_registration_msg_with_link($output){
		return $this->dispatch_message('swpm_ty_page_registration_msg_with_link', $output);
	}

	public function swpm_ty_page_registration_msg_no_link($output){
		return $this->dispatch_message('swpm_ty_page_registration_msg_no_link', $output);
	}

	public function swpm_mini_login_output_when_logged_in($output){
		return $this->dispatch_message('swpm_mini_login_output_when_logged_in', $output);
	}

	public function swpm_mini_login_output_when_not_logged_in($output){
		return $this->dispatch_message('swpm_mini_login_output_when_not_logged_in', $output);
	}

    public function swpm_pp_output_when_not_logged_in($output) {
        return $this->dispatch_message('swpm_pp_output_when_not_logged_in', $output);
    }

    public function swpm_pp_output_when_no_access($output) {
        return $this->dispatch_message('swpm_pp_output_when_no_access', $output);
    }

    public function swpm_pp_output_when_membership_level_restricted($output) {
        return $this->dispatch_message('swpm_pp_output_when_membership_level_restricted', $output);
    }

    public function swpm_pp_output_when_account_status_restricted($output) {
        return $this->dispatch_message('swpm_pp_output_when_account_status_restricted', $output);
    }

    private function dispatch_message($key, $default) {
        $msg = SwpmCustomMessageSettings::get_instance()->get_value($key);
        $msg = $this->replace_dynamic_tags($msg);

        return empty ($msg) ? $default : do_shortcode($msg);
    }

    public function swpm_custom_msg_do_admin_menu($menu_parent_slug) {
        add_submenu_page($menu_parent_slug, __("Custom Message", 'simple-membership'), __("Custom Message", 'simple-membership'), 'manage_options', 'swpm-custom-message', array(&$this, 'custom_message_admin_interface'));
    }

    public function custom_message_admin_interface() {
        $current_tab = SwpmCustomMessageSettings::get_instance()->current_tab;
        include(SWPM_CUSTOM_MSG_PATH . 'views/custom-message-settings.php');
    }

    public function admin_init_hook() {
        SwpmCustomMessageSettings::get_instance()->init_config_hooks();
    }

    /**
     * Replace the dynamic tags in the message.
     * It will first replace the tags that are specific to this addon.
     * Next, it will also replace the standard tags (for a logged-in member) offered by the core plugin.
     */
    
    public static function replace_dynamic_tags($msg){
        if( !class_exists('SwpmSettings') ){
            return 'Error: The Simple Membership plugin is not active.';
        }

        $settings = SwpmSettings::get_instance();
        $registration_page_url = $settings->get_value('registration-page-url');

        //Login page URL (including any additional paramenters that maybe needed based on the setup of this site.
        $login_page_url = $settings->get_value( 'login-page-url' );
        if (function_exists('swpm_alr_append_query_arg_if_applicable')){
            //After login redirection addon is active. Lets see if redirect parameter needs to be added based on that addon's settings.
            $login_page_url = swpm_alr_append_query_arg_if_applicable($login_page_url);
        }

        //Define the replaceable tags
        $tags = array(
                '{login_url}',
                '{registration_url}',
        );

        //Define the values
        $vals = array(
                $login_page_url,
                $registration_page_url,
        );
        //Do tags replacement of this particular addon first. Next, we will also do the standard tags replacement.
        $msg = str_replace( $tags, $vals, $msg );

        //If the member is logged-in, we can do additional dynamic tags replacement with the member's information.
        if (SwpmMemberUtils::is_member_logged_in()){
            $member_id = SwpmMemberUtils::get_logged_in_members_id();
            $additional_args = array();
            $msg = SwpmMiscUtils::replace_dynamic_tags( $msg, $member_id, $additional_args );
        }

        return $msg;
    }
}
