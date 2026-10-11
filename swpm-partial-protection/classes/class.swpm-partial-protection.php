<?php

/**
 * SwpmPartialProtection class
 */
class SwpmPartialProtection {

	public function __construct() {
		if (class_exists('SimpleWpMembership')) {
			add_shortcode('swpm_protected', array(&$this, 'partial_protect_sc_handler'));
		}
	}

	public function partial_protect_sc_handler($attrs, $contents) {
		$contents = do_shortcode($contents);

		$attrs = shortcode_atts(array(
			'member_id' => '', // Member IDs separated by comma.
			'visible_to' => '', // Either not_logged_in_users_only or logged_in_users_only.
			'account_status' => '', // Account statuses (active, inactive, expired) separated by comma.
			'not_for' => '', // Membership level IDs separated by hyphen or comma.
			'for' => '', // Membership level IDs separated by hyphen or comma.
			'custom_msg' => false,
			'do_not_show_expired_msg' => false,
			'do_not_show_protected_msg' => false,
			'format_protected_msg' => false,
			'show_login_link' => false,
		), $attrs);

		$attrs['do_not_show_protected_msg'] = ($attrs['do_not_show_protected_msg'] == 1 ? true : false);
		$attrs['show_login_link'] = ($attrs['show_login_link'] == 1 ? true : false);

		$sc_output = new SWPM_Partial_Protect_Shortcode_Output($attrs, $contents);
		$sc_output->evaluate_sc_attrs_and_prepare_output();

		return $sc_output->get_final_output();
	}
}
