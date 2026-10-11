<?php

class SwpmCustomMessageSettings {

    private static $_this;
    private $settings;
    public $current_tab;

    private function __construct() {
        $this->settings = (array) get_option('swpm-custom-message-settings');
    }

    public function init_config_hooks() {
        if (is_admin()) { // for frontend just load settings but dont try to render settings page.
            $tab = filter_input(INPUT_GET, 'tab');
            $tab = empty($tab) ? filter_input(INPUT_POST, 'tab') : $tab;
            $tab = intval($tab);
            $this->current_tab = empty($tab) ? 1 : $tab;
            add_action('swpm-custom-message-tab', array(&$this, 'draw_tabs'));
            $method = 'tab_' . $this->current_tab;
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

    private function tab_1() {
        register_setting('swpm-custom-message-tab-1', 'swpm-custom-message-settings', array(&$this, 'sanitize_tab_1'));

        add_settings_section('swpm-documentation', __('Plugin fsdafsaDocumentation','simple-membership'), array(&$this, 'swpm_documentation_callback'), 'swpm-custom-message-settings');

        add_settings_section('pages-settings', __('Custom Message Settings','simple-membership'), array(&$this, 'swpm_cm_general_settings_callback'), 'swpm-custom-message-settings');

        add_settings_field('swpm_restricted_post_msg', __('Restricted Post','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_restricted_post_msg',
            'message' => 'Members who do not have access to this post/page content will see this message.'));
        add_settings_field('swpm_not_logged_in_post_msg', __('Restricted Post (Not Logged-in)','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_not_logged_in_post_msg',
            'message' => 'Non logged in users will see this message on your protected posts/pages. If you customize this message then the "Enable Redirection to the Last Page" feature of the after login redirection addon won\'t work.'));
        add_settings_field('swpm_restricted_comment_msg', __('Restricted Comment','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_restricted_comment_msg',
            'message' => 'Members who do not have access to protected comments will see this message.'));
        add_settings_field('swpm_not_logged_in_comment_msg', __('Restricted Comment (Not Logged-in)', 'simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_not_logged_in_comment_msg',
            'message' => 'Non logged in users will see this message on protected comments.'));
        add_settings_field('swpm_restricted_more_tag_msg', __('Restricted More Tag','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_restricted_more_tag_msg',
            'message' => 'This message is shown on more tag protected posts (to members who do not have access to the post).'));
        add_settings_field('swpm_not_logged_in_more_tag_msg', __('Restricted More Tag (Not Logged-in)','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_not_logged_in_more_tag_msg',
            'message' => 'Non logged in users will see this message on more tag protected posts.'));

        add_settings_field('swpm_registration_success_msg', __('Registration Successful','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_registration_success_msg',
            'message' => 'This message gets displayed to the users after they submit the registration form.'));
        add_settings_field('swpm_registration_email_activation_msg', __('Email Activation','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_registration_email_activation_msg',
            'message' => 'This message gets displayed to the users after they submit the registration form with the email activation feature enabled.'));

        add_settings_field('swpm_account_expired_msg', __('Account Expired','simple-membership'), array(&$this, 'textfield_long_callback'), 'swpm-custom-message-settings', 'pages-settings', array('item' => 'swpm_account_expired_msg',
            'message' => 'This message gets shown to members with expired accounts.'));

	    add_settings_field(
                'swpm_password_reset_success_msg',
                __('Password Reset Success','simple-membership'),
                array(&$this, 'textfield_long_callback'),
                'swpm-custom-message-settings',
                'pages-settings',
                array(
                        'item' => 'swpm_password_reset_success_msg',
                        'message' => 'This message gets shown to members when password reset by link option is used and the operation is successful.'
                ));

	    add_settings_field(
		    'swpm_ty_page_registration_msg_with_link',
		    __('Thank You Page Registration Message with Link', 'simple-membership'),
		    array(&$this, 'textfield_long_callback'),
		    'swpm-custom-message-settings',
		    'pages-settings',
		    array(
			    'item' => 'swpm_ty_page_registration_msg_with_link',
			    'message' => 'This message is displayed on <a href="https://simple-membership-plugin.com/paid-registration-from-the-thank-you-page/" target="_blank">the thank you page</a> after a successful payment (it includes the unique registration link).'
		    ));

	    add_settings_field(
		    'swpm_ty_page_registration_msg_no_link',
		    __('Thank You Page Registration Message without Link', 'simple-membership'),
		    array(&$this, 'textfield_long_callback'),
		    'swpm-custom-message-settings',
		    'pages-settings',
		    array(
			    'item' => 'swpm_ty_page_registration_msg_no_link',
			    'message' => 'This message is displayed on the thank you page after a successful payment (without the unique registration link).'
		    ));

	    add_settings_field(
		    'swpm_mini_login_output_when_logged_in',
		    __('Mini/Compact Login Output', 'simple-membership'),
		    array(&$this, 'textfield_long_callback'),
		    'swpm-custom-message-settings',
		    'pages-settings',
		    array(
			    'item' => 'swpm_mini_login_output_when_logged_in',
			    'message' => 'This message will appear on the <a href="https://simple-membership-plugin.com/adding-mini-login-widget-sidebar-header-footer/" target="_blank">mini/compact login widget</a> when the user is logged-in.'
		    ));

	    add_settings_field(
		    'swpm_mini_login_output_when_not_logged_in',
		    __('Mini/Compact Login Output (Not Logged-in)', 'simple-membership'),
		    array(&$this, 'textfield_long_callback'),
		    'swpm-custom-message-settings',
		    'pages-settings',
		    array(
			    'item' => 'swpm_mini_login_output_when_not_logged_in',
			    'message' => 'This message will appear on the <a href="https://simple-membership-plugin.com/adding-mini-login-widget-sidebar-header-footer/" target="_blank">mini/compact login widget</a> when the user is not logged-in.'
		    ));

        /**
         * Partial Protection Addon related settings
         */
        add_settings_section('partial-protection-settings', __('Partial Protection Addon Related','simple-membership'), array(&$this, 'swpm_partial_protection_settings_callback'), 'swpm-custom-message-settings');

        add_settings_field(
            'swpm_pp_output_when_not_logged_in',
            __('Partially Protected - Not Logged In', 'simple-membership'),
            array(&$this, 'textfield_long_callback'),
            'swpm-custom-message-settings',
            'partial-protection-settings',
            array(
                'item' => 'swpm_pp_output_when_not_logged_in',
                'message' => "Shown when a visitor isn't logged in."
            ));

        add_settings_field(
            'swpm_pp_output_when_no_access',
            __('Partially Protected - Member No Access', 'simple-membership'),
            array(&$this, 'textfield_long_callback'),
            'swpm-custom-message-settings',
            'partial-protection-settings',
            array(
                'item' => 'swpm_pp_output_when_no_access',
                'message' => "Shown when a logged-in member doesn't have access at all."
            ));

        add_settings_field(
            'swpm_pp_output_when_membership_level_restricted',
            __('Partially Protected - Membership Level Restricted', 'simple-membership'),
            array(&$this, 'textfield_long_callback'),
            'swpm-custom-message-settings',
            'partial-protection-settings',
            array(
                'item' => 'swpm_pp_output_when_membership_level_restricted',
                'message' => "Shown when their current level doesn't allow access."
            ));

        add_settings_field(
            'swpm_pp_output_when_account_status_restricted',
            __('Partially Protected - Account Status Restricted', 'simple-membership'),
            array(&$this, 'textfield_long_callback'),
            'swpm-custom-message-settings',
            'partial-protection-settings',
            array(
                'item' => 'swpm_pp_output_when_account_status_restricted',
                'message' => "Shown when account status, e.g. inactive/expired, blocks access"
            ));


        if ( defined( 'SWPM_FPP_PROTECTION_VER' ) ) {
            //Full Page Protection addon is enabled. Let's check if its version is >=1.2
            $msg = '';
            if ( version_compare( SWPM_FPP_PROTECTION_VER, '1.2', '<' ) ) {
            $msg = sprintf( '<br><span style="color:red;">Attention: Full Page Protection version 1.2+ required for this to work. You have version %s installed. Please update.</span>', SWPM_FPP_PROTECTION_VER );
            }
            add_settings_field( 'swpm_fpp_protected_msg', __( 'Full Page Protection Message', 'simple-membership' ), array( &$this, 'textfield_long_callback' ), 'swpm-custom-message-settings', 'pages-settings', array( 'item' => 'swpm_fpp_protected_msg',
            'message'	 => 'This message gets shown by Full Page Protection addon.'.$msg ) );
        }

        if ( defined( 'SWPM_OLDER_POST_VER' ) ) {
            //Older posts protection addon is enabled.
            $msg = '';
            add_settings_field( 'swpm_older_post_protected_msg', __( 'Older Post Protection Message', 'simple-membership' ), array( &$this, 'textfield_long_callback' ), 'swpm-custom-message-settings', 'pages-settings', array( 'item' => 'swpm_older_post_protected_msg',
            'message' => 'This message will be shown to protected older posts. You can use the {post_published_date} tag in this message to output the post published date value in the message.') );
        }

    }

    public static function get_instance() {
        self::$_this = empty(self::$_this) ? new SwpmCustomMessageSettings() : self::$_this;
        return self::$_this;
    }

    public function checkbox_callback($args) {
        $item = $args['item'];
        $msg = isset($args['message']) ? $args['message'] : '';
        $is = esc_attr($this->get_value($item));
        echo "<input type='checkbox' $is name='swpm-custom-message-settings[" . $item . "]' value=\"checked='checked'\" />";
        echo '<p class="description">' . $msg . '</p>';
    }

    public function textarea_callback($args) {
        $item = $args['item'];
        $msg = isset($args['message']) ? $args['message'] : '';
        $text = esc_attr($this->get_value($item));
        echo "<textarea name='swpm-custom-message-settings[" . $item . "]'  rows='6' cols='60' >" . $text . "</textarea>";
        echo '<p class="description">' . $msg . '</p>';
    }

    public function textfield_small_callback($args) {
        $item = $args['item'];
        $msg = isset($args['message']) ? $args['message'] : '';
        $text = esc_attr($this->get_value($item));
        echo "<input type='text' name='swpm-custom-message-settings[" . $item . "]'  size='5' value='" . $text . "' />";
        echo '<p class="description">' . $msg . '</p>';
    }

    public function textfield_callback($args) {
        $item = $args['item'];
        $msg = isset($args['message']) ? $args['message'] : '';
        $text = esc_attr($this->get_value($item));
        echo "<input type='text' name='swpm-custom-message-settings[" . $item . "]'  size='50' value='" . $text . "' />";
        echo '<p class="description">' . $msg . '</p>';
    }

    public function textfield_long_callback($args) {
        $item = $args['item'];
        $msg = isset($args['message']) ? $args['message'] : '';
        $text = esc_attr($this->get_value($item));
        echo "<input type='text' name='swpm-custom-message-settings[" . $item . "]'  size='100' value='" . $text . "' />";
        echo '<p class="description">' . $msg . '</p>';
    }

    public function swpm_documentation_callback() {
        ?>
        <div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">
            <p>Please visit the
                <a target="_blank" href="https://simple-membership-plugin.com/simple-membership-custom-messages-addon/">custom messages addon page</a>
                to read setup and configuration documentation.
            </p>
        </div>
        <?php
    }

    public function swpm_cm_general_settings_callback() {
        echo '<p>Core plugin message will only be overwritten if you specify a value in any of the following fields.<p>';
    }

    public function swpm_partial_protection_settings_callback() {
        echo '<p>Partial Protection Addon message will only be overwritten if you specify a value in any of the following fields.<p>';
    }

    public function sanitize_tab_1($input) {
        if (empty($this->settings)) {
            $this->settings = (array) get_option('swpm-custom-message-settings');
        }
        $output = $this->settings;

        $output['swpm_restricted_post_msg'] = ($input['swpm_restricted_post_msg']);
        $output['swpm_not_logged_in_post_msg'] = ($input['swpm_not_logged_in_post_msg']);
        $output['swpm_restricted_comment_msg'] = ($input['swpm_restricted_comment_msg']);
        $output['swpm_not_logged_in_comment_msg'] = ($input['swpm_not_logged_in_comment_msg']);
        $output['swpm_restricted_more_tag_msg'] = ($input['swpm_restricted_more_tag_msg']);
        $output['swpm_not_logged_in_more_tag_msg'] = ($input['swpm_not_logged_in_more_tag_msg']);
        $output['swpm_registration_success_msg'] = ($input['swpm_registration_success_msg']);
        $output['swpm_registration_email_activation_msg'] = ($input['swpm_registration_email_activation_msg']);
        $output['swpm_account_expired_msg'] = ($input['swpm_account_expired_msg']);
        $output['swpm_fpp_protected_msg'] = isset($input['swpm_fpp_protected_msg']) ? ($input['swpm_fpp_protected_msg']) : '';
        $output['swpm_older_post_protected_msg'] = isset($input['swpm_older_post_protected_msg']) ? ($input['swpm_older_post_protected_msg']) : '';

        $output['swpm_password_reset_success_msg'] = isset($input['swpm_password_reset_success_msg']) ? ($input['swpm_password_reset_success_msg']) : '';
        $output['swpm_ty_page_registration_msg_with_link'] = isset($input['swpm_ty_page_registration_msg_with_link']) ? ($input['swpm_ty_page_registration_msg_with_link']) : '';
        $output['swpm_ty_page_registration_msg_no_link'] = isset($input['swpm_ty_page_registration_msg_no_link']) ? ($input['swpm_ty_page_registration_msg_no_link']) : '';

        $output['swpm_mini_login_output_when_logged_in'] = isset($input['swpm_mini_login_output_when_logged_in']) ? ($input['swpm_mini_login_output_when_logged_in']) : '';
        $output['swpm_mini_login_output_when_not_logged_in'] = isset($input['swpm_mini_login_output_when_not_logged_in']) ? ($input['swpm_mini_login_output_when_not_logged_in']) : '';
    
        /**
         * Partial Protection Addon related settings
         */
        $output['swpm_pp_output_when_not_logged_in'] = isset($input['swpm_pp_output_when_not_logged_in']) ? ($input['swpm_pp_output_when_not_logged_in']) : '';
        $output['swpm_pp_output_when_no_access'] = isset($input['swpm_pp_output_when_no_access']) ? ($input['swpm_pp_output_when_no_access']) : '';
        $output['swpm_pp_output_when_membership_level_restricted'] = isset($input['swpm_pp_output_when_membership_level_restricted']) ? ($input['swpm_pp_output_when_membership_level_restricted']) : '';
        $output['swpm_pp_output_when_account_status_restricted'] = isset($input['swpm_pp_output_when_account_status_restricted']) ? ($input['swpm_pp_output_when_account_status_restricted']) : '';

        return $output;
    }

    public function get_value($key, $default = "") {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        return $default;
    }

    public function set_value($key, $value) {
        $this->settings[$key] = $value;
        return $this;
    }

    public function save() {
        update_option('swpm-custom-message-settings', $this->settings);
    }

    public function draw_tabs() {
        $current = $this->current_tab;
        ?>
        <h3 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo ($current == 1) ? 'nav-tab-active' : ''; ?>" href="admin.php?page=swpm-custom-message">General Settings</a>
        </h3>
        <?php
    }

}
