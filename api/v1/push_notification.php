<?php

error_reporting(0); //RISKY!!!

$gcm_key = 'AAAAZM73TOo:APA91bFsn2VE9Vj7YR4c5cyxB4ekv7HjdW5PCXquKaGoJoMmZL1L9x6V90qjo4MhMC5sGnr33sbuUyAoLhlNKDyFB3vM_SlCcCf_-0Vzi8EtUcUTTSer6tbYGOGBx8hLIaLMrWf7-nVCRuMkYjPJDq3Mnk0ts0rqbg';
$apns_pass = 'TheBeautifulPossible2016';

function push_notification($title, $message, $tokens, $badge_count=null, $url=null){
	global $gcm_key, $apns_pass;

	$message = strlen($message) > 120 ? substr($message,0,120) . "..." : $message;
	$message = stripslashes($message);


	//----------iOS
	for($i=0; $i<count($tokens); $i++){
		$deviceToken = $tokens[$i];
		$badge = $badge_count;
		$sound = 'default';

		// if(count($deviceToken) <= 64){
		// there should be a 'device_type' field saved in the 'device' table...

		// Construct the notification payload
		$body = array();
		$body['aps'] = array('alert' => $title . ': ' . $message);

		if ($badge_count)
		    $body['aps']['badge'] = (int)$badge_count;
		if ($sound)
		    $body['aps']['sound'] = $sound;

		if($url)
			$body['url'] = $url;

		// End of Configurable Items //
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl','local_cert', 'apns_cert.pem');

		// assume the private key passphase was removed.
		stream_context_set_option($ctx, 'ssl', 'passphrase', $apns_pass);
		// $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 120, STREAM_CLIENT_CONNECT, $ctx);
		$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 120, STREAM_CLIENT_CONNECT, $ctx);

		$payload = json_encode($body);
		$msg = chr(0).pack('n',32).pack('H*', str_replace(' ', '', $deviceToken)).pack('n',strlen($payload)).$payload;
		// print "" . $payload . "\n";
		fwrite($fp, $msg);
		fclose($fp);
	}


	//----------Android CURL
	$msg = array(
		'message' 	=> $message,
		'title'		=> $title,
		// 'subtitle'	=> 'This is a subtitle. subtitle',
		// 'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
		'vibrate'	=> 1,
		'sound'		=> 1,
		'largeIcon'	=> 'large_icon',
		'smallIcon'	=> 'small_icon'
	);
	if($url)
		$msg['url'] = $url;

	$fields = array(
		'registration_ids' 	=> $tokens,
		'data'			=> $msg
	);
	$headers = array(
		'Authorization: key=' . $gcm_key,
		'Content-Type: application/json'
	);
	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	$result = curl_exec($ch );
	curl_close( $ch );
	// echo $result;
	// return $result;
}

function update_badge($tokens, $badge_count){
	global $gcm_key, $apns_pass;

	//----------iOS
	for($i=0; $i<count($tokens); $i++){
		$deviceToken = $tokens[$i];

		// Construct the notification payload
		$body = array();
		$body['aps'] = array('content_available' => 1);

		$body['aps']['badge'] = (int)$badge_count;

		// End of Configurable Items //
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl','local_cert', 'apns_cert.pem');

		// assume the private key passphase was removed.
		stream_context_set_option($ctx, 'ssl', 'passphrase', $apns_pass);
		$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 120, STREAM_CLIENT_CONNECT, $ctx);

		$payload = json_encode($body);
		$msg = chr(0).pack('n',32).pack('H*', str_replace(' ', '', $deviceToken)).pack('n',strlen($payload)).$payload;
		// print "" . $payload . "\n";
		fwrite($fp, $msg);
		fclose($fp);
	}


	//----------Android CURL
	// $msg = array(
	// 	'message' 	=> $message,
	// 	'title'		=> $title,
	// 	// 'subtitle'	=> 'This is a subtitle. subtitle',
	// 	// 'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
	// 	'vibrate'	=> 1,
	// 	'sound'		=> 1,
	// 	'largeIcon'	=> 'large_icon',
	// 	'smallIcon'	=> 'small_icon'
	// );
	// $fields = array(
	// 	'registration_ids' 	=> $tokens,
	// 	'data'			=> $msg
	// );
	// $headers = array(
	// 	'Authorization: key=' . $gcm_key,
	// 	'Content-Type: application/json'
	// );
	// $ch = curl_init();
	// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
	// curl_setopt( $ch,CURLOPT_POST, true );
	// curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	// curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	// curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	// curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	// $result = curl_exec($ch );
	// curl_close( $ch );
	// echo $result;
	// return $result;
}

?>