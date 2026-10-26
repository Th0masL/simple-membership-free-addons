<?php

namespace Cloudflare;

class Turnstile {

	private $secret;

	public $last_error_code = '';

	public function __construct( $secret ) {
		$this->secret = $secret;
	}

	public function verify( $token ) {

		// Retrieve the Turnstile response token
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

		// Validate the token by calling the "/siteverify" API endpoint.
		$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

		$data = array(
			'secret'   => $this->secret,
			'response' => $token,
			'remoteip' => $ip
		);

		try {
			$result = wp_remote_post(
				$url,
				array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => json_encode( $data ),
			));

			if (is_wp_error($result)) {
				\SwpmLog::log_simple_debug( "[Cloudflare Turnstile Addon] ". $result->get_error_message(), false );

				return false;
			}

			$response_body = wp_remote_retrieve_body( $result );
			$result_array = json_decode( $response_body, true );
			
			if ( ! empty( $result_array['error-codes'] ) ) {
				// Verification failed
				foreach ( $result_array['error-codes'] as $code ) {
					\SwpmLog::log_simple_debug( "[Cloudflare Turnstile Addon] " . $this->error_msg( $code ), false );
				}
				
				$this->last_error_code = $code;

				return false;
			}

			// Verification successful
			\SwpmLog::log_simple_debug( "[Cloudflare Turnstile Addon] Turnstile captcha validated successfully!", true );

			return true;

		} catch ( \Exception $e){
			wp_die( $e->getMessage() );
		}

	}

	public function error_msg( $code ) {
		switch ( $code ) {
			case 'missing-input-secret':
				return __( 'The secret parameter was not passed.', 'simple-membership' );
			case 'invalid-input-secret':
				return __( 'The secret parameter was invalid, did not exist, or is a testing secret key with a non-testing response.', 'simple-membership' );
			case 'bad-request':
				return __( 'The request was rejected because it was malformed.', 'simple-membership' );
			case 'missing-input-response':
				return __( 'Captcha validation failed.', 'simple-membership' );
			case 'invalid-input-response':
				return __( 'The captcha token is invalid or has expired.', 'simple-membership' );
			case 'timeout-or-duplicate':
				return __( 'The captcha token has already been validated before. Please reload the browser window and try again.', 'simple-membership' );
			case 'internal-error':
				return __( 'An internal error happened while validating captcha. Please reload the browser window and try again.', 'simple-membership' );
			default:
				return '';
		}
	}

	public function get_last_error_code(){
		return $this->last_error_code;
	}

	public function get_last_error_msg(){
		return $this->error_msg($this->get_last_error_code());
	}
}