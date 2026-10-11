<?php
/**
 * This is used to hold the output content and permission denial messages.
 *
 * It is used to decide what content to show by the shortcode by analyzing its properties.
 */
class SWPM_Partial_Protect_Shortcode_Output{

	public string $content = '';

	public string $message = '';

	public array $sc_attrs;

	public bool $is_visible = false;

	public static bool $style_injected = false;

	public string $visibility_option;

	public array $account_status_options;

	public array $member_ids_options;

	public array $for_options;

	public array $not_for_options;

	public $auth; //SwpmAuth object

	public function __construct(array $sc_attrs, string $content) {
		$this->sc_attrs = $sc_attrs;
		$this->content  = $content;
		$this->auth = SwpmAuth::get_instance();
	}

	public function evaluate_sc_attrs_and_prepare_output() {
		$this->prepare_sc_attrs();
		$this->prepare_sc_output();
	}

	/**
	 * Get the evaluated final shortcode output.
	 * @return string
	 */
	public function get_final_output(){
		if ($this->is_visible){
			return $this->content;
		}else{
			if ($this->sc_attrs['do_not_show_protected_msg']){
				return '';
			}else{
				return $this->get_message();
			}
		}
	}

	/**
	 * Set whether the protected content is visible to the visitor or not.
	 *
	 * @param bool $is_visible
	 */
	public function set_visibility(bool $is_visible){
		$this->is_visible = $is_visible;
	}

	/**
	 * Set content restricted message
	 */
	public function set_message($message){
		$this->message = $message;
	}

	/**
	 * Get formatted content restricted message.
	 */
	public function get_message(){
		return $this->format_restricted_msg($this->message);
	}

	/**
	 * Process the shortcode attributes to a format that can be used by our code.
	 */
	public function prepare_sc_attrs() {
		$attrs = $this->sc_attrs;

		$visibility_option = sanitize_text_field($attrs['visible_to']);

		$account_status_options = ! empty( $attrs['account_status'] ) ? explode( ',', $attrs['account_status'] ) : array();
		$account_status_options = array_map( 'sanitize_text_field', $account_status_options );
		$account_status_options = array_map( 'strtolower', $account_status_options );

		$member_ids_options = ! empty( $attrs['member_id'] ) ? explode( ',', $attrs['member_id'] ) : array();
		$member_ids_options = array_map( 'sanitize_text_field', $member_ids_options );

		if ( ! empty( $attrs['for'] ) ) {
			//Note: To keep it backward compatible, we will check if the separator is a hyphen or a comma.
			//In the past the separator was a hyphen. Going forward we want to use a comma.
			$for_separator = strpos( $attrs['for'], '-' ) ? '-' : ',';
			$for_options   = explode( $for_separator, $attrs['for'] );
			$for_options   = array_map( 'sanitize_text_field', $for_options );
		} else {
			$for_options = array();
		}

		if ( ! empty( $attrs['not_for'] ) ) {
			//Note: To keep it backward compatible, we will check if the separator is a hyphen or a comma.
			//In the past the separator was a hyphen. Going forward we want to use a comma.
			$not_for_separator = strpos( $attrs['not_for'], '-' ) ? '-' : ',';
			$not_for_options   = explode( $not_for_separator, $attrs['not_for'] );
			$not_for_options   = array_map( 'sanitize_text_field', $not_for_options );
		} else {
			$not_for_options = array();
		}

		$this->visibility_option = $visibility_option;
		$this->account_status_options = $account_status_options;
		$this->member_ids_options = $member_ids_options;
		$this->for_options = $for_options;
		$this->not_for_options = $not_for_options;
	}

	/**
	 * Evaluate the shortcode attributes and prepare the output.
	 */
	public function prepare_sc_output() {
		/**
		 * If the visitor is not-logged-in and if visibility set to 'not_logged_in_users_only', then
		 * show the content to anyone who is not logged in!
		 */
		if ( ! $this->auth->is_logged_in() ) {
			// The user is not logged-in. Apply conditions that are applicable to non-logged-in users.

			if ( $this->visibility_option == "not_logged_in_users_only" ) {
				//Condition met. Show this content to the non-logged-in users only.
				$this->set_visibility( true );
			} else {
				$this->set_visibility( false );
				$message = __( "This content is for members only.", "simple-membership" );
				$message = apply_filters('swpm_pp_output_when_not_logged_in', $message);
		
				$this->set_message( $message );
			}
		} 
		
		/**
		 * The member is logged-in, so do the following based on the corresponding constraints:
		 *
		 * "logged_in_users_only"     : Show the content.
		 * "not_logged_in_users_only" : Don't show the content.
		 *
		 * "expired|active|inactive"  : Check if the member's account status matches or not.
		 * "for"                      : Check if the member's membership level matches or not.
		 * "not_for"                  : Check if the member's membership level matches or not.
		 * "member_id"                : Check if the member's ID matches or not.
		 *
		 * NOTE: The member's account status, member ID, and membership level ID can only be retrieved if the member is logged-in.
		 */
		else {
			// The user is logged-in. Apply conditions that are applicable to logged-in users.

			$account_status = $this->auth->get('account_state');

			if ( ! empty( $this->visibility_option ) ) {
				if ( $this->visibility_option == "logged_in_users_only" ) {
					$this->set_visibility( true );
				} else {
					$this->set_visibility( false );
				}

				return;
			}

			// Check if the member's account status matches or not.
			if ( ! $this->has_account_status_permission() ) {
				// Does not satisfy visible_to options, don't show the content.
				$this->set_visibility( false );
				if ( $account_status == 'expired' && $this->sc_attrs['do_not_show_expired_msg'] != '1' ) {
					//Show the renewal message as this account is expired
					$this->set_message( wpautop(SwpmMiscUtils::get_renewal_link()) );
				} else if($account_status == 'expired' && $this->sc_attrs['do_not_show_expired_msg'] == '1') {
					$this->set_message('');
				} else {
					$message = __( "Your account status does not have permission to view this content.", "simple-membership" );
					$message = apply_filters('swpm_pp_output_when_account_status_restricted', $message);
					$this->set_message( $message );
				}

				return;
			}

			// Check if the member's membership level matches or not.
			if ( ! $this->has_for_permission() ) {
				$this->set_visibility( false );
				$message = __( "Your membership level does not have permission to view this content.", "simple-membership" );
				$message = apply_filters('swpm_pp_output_when_membership_level_restricted', $message);
				$this->set_message( $message );

				return;
			}

			// Check if the member's membership level matches or not.
			if ( ! $this->has_not_for_permission() ) {
				$this->set_visibility( false );
				$message = __( "Your membership level does not have permission to view this content.", "simple-membership" );
				$message = apply_filters('swpm_pp_output_when_membership_level_restricted', $message);
				$this->set_message( $message );

				return;
			}

			// Check if the member's ID matches or not.
			if ( ! $this->has_member_id_permission() ) {
				$this->set_visibility( false );
				$message = __( "You do not have permission to view this content.", "simple-membership" );
				$message = apply_filters('swpm_pp_output_when_no_access', $message);
				$this->set_message( $message );

				return;
			}

			// No constraint found! show the content to the visitor.
			$this->set_visibility( true );
		}
	}

	/**
	 * Check if member's account status has permission.
	 *
	 * @return bool TRUE if permission granted, FALSE otherwise.
	 */
	public function has_account_status_permission() {
		$account_status = $this->auth->get('account_state');
		if (empty($this->account_status_options) || in_array($account_status, $this->account_status_options) ){
			return true;
		}

		return false;
	}

	/**
	 * Check if member's membership level has permission.
	 *
	 * @return bool TRUE if permission granted, FALSE otherwise.
	 */
	public function has_for_permission() {
		$membership_level = $this->auth->get('membership_level');
		if (empty($this->for_options) || in_array($membership_level, $this->for_options) ){
			return true;
		}

		return false;
	}

	/**
	 * Check if member's membership level has permission.
	 *
	 * @return bool TRUE if permission granted, FALSE otherwise.
	 */
	public function has_not_for_permission() {
		$membership_level = $this->auth->get('membership_level');
		if (empty($this->not_for_options) || !in_array($membership_level, $this->not_for_options) ){
			return true;
		}

		return false;
	}

	/**
	 * Check if member's ID has permission.
	 *
	 * @return bool TRUE if permission granted, FALSE otherwise.
	 */
	public function has_member_id_permission() {
		$member_id = $this->auth->get( 'member_id' );
		if (empty($this->member_ids_options) || in_array($member_id, $this->member_ids_options) ){
			return true;
		}

		return false;
	}

	/**
	 * Wraps the restricted message with proper formatted HTML as CSS.
	 */
	private function format_restricted_msg(string $msg) {
		if ( !$this->sc_attrs['do_not_show_protected_msg'] ) {
			if ($this->sc_attrs['custom_msg'] !== false) {
				$msg = $this->sc_attrs['custom_msg'];
			}

			$login_link = '';
			if ($this->sc_attrs['show_login_link']) {
				$login_link = '<br />' . SwpmMiscUtils::get_login_link();
			}

			$classes = 'swpm-partial-protection';
			$style = '';
			$icon = '';
			if ($this->sc_attrs['format_protected_msg'] == 1) {
				$classes .= ' swpm-formatted-msg';//Add the formattd msg class to the box.
				$icon = '<div class="swpm-partial-protection-icon"><span class="dashicons dashicons-info"></span></div>';
				//in order to prevent injecting same style on the page multiple times, let's check if we already have injected it
				if (!self::$style_injected) {
					wp_enqueue_style('dashicons'); //Dashicons style
					$style = "\r\n<style>";
					$style .= '.swpm-formatted-msg{display:inline-block;overflow:hidden;position:relative;width:100%;border:1px solid #DDDDDD;border-left:5px solid #EED202;padding:5px;margin:10px 0;}';
					$style .= '.swpm-formatted-msg div.swpm-partial-protection-icon{width:auto;height:100%;position:absolute;top:50%;left:5px;margin-top:-20px;}';
					$style .= '.swpm-formatted-msg span.dashicons{font-size:40px;color:#EED202;height:auto;width:auto;}';
					$style .= '.swpm-formatted-msg span.swpm-partial-protection-text{float:left;margin-left:45px;padding: 5px}';
					$style .= "</style>\r\n";

					self::$style_injected = true;
				}
			}

			$output = '<div class="swpm-partial-protection-msg">';//Wrap it in a div for the block themes.
			$output .= '<div class="' . $classes . '">' . $icon . '<span class="swpm-partial-protection-text">' . $msg . $login_link . '</span></div>' . $style;
			$output .= '</div>';

			return $output;
		}

		return '';
	}
}