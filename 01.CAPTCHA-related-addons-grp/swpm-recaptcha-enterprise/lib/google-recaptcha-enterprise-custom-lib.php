<?php

function swpm_validate_recaptcha_enterprise( $project_id, $site_key, $token, $api_key, $action ) {

	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// IP from shared internet
		$user_ip_address = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// IP passed from proxy
		$user_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		// IP address from remote address
		$user_ip_address = $_SERVER['REMOTE_ADDR'];
	}

	$json_payload = json_encode( array(
		'event' => array(
			"token"          => $token,
			"siteKey"        => $site_key,
			"userAgent"      => $user_agent,
			"userIpAddress"  => $user_ip_address,
			"expectedAction" => $action
			// "ja3"            => "JA3",
		)
	) );

	//echo $json_payload;

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, "https://recaptchaenterprise.googleapis.com/v1/projects/" . $project_id . "/assessments?key=" . $api_key );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json' ) );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_payload );

	// Receive server response ...
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$server_output = curl_exec( $ch );

	curl_close( $ch );

	//echo "<pre>";
	$decoded = json_decode( $server_output );

	// SwpmLog::log_array_data_to_debug( print_r( $decoded, true ), true ); //Debug purpose only

	//print_r($decoded);
	//die();
	if ( isset($decoded->tokenProperties->valid) && $decoded->tokenProperties->valid == "true" ) {
		//echo "valid";
		return true;
	} else {
		//echo "not valid";
		return false;
	}

}