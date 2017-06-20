<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = mysqli_real_escape_string(	$db, $data->email	);
	@$user_id = mysqli_real_escape_string(	$db, $data->user_id	);

	// Exit if already verified
 	$query =	"SELECT * FROM users WHERE verified = 1 AND user_id = " . $user_id;
 	$res = mysqli_query($db, $query);
 	if(mysqli_num_rows($res) > 0)
 		exit('already_verified');

	// Reset verification code
	$verification_code = uniqid('', true);
	$query =	"UPDATE users " .
				"SET verified = 0, " .
				"verification_code = '" . $verification_code . "' " .
				"WHERE user_id = " . $user_id;
	$res = mysqli_query($db, $query);

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);

	// Send verification email
	$base_url = "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/";
 	$verification_link = $base_url . 'verify_email.php?user_id=' . $user_id . '&verification_code=' . $verification_code;
 	$content = 	"Thank you for joining us on our adventure!\n" .
 			"Our goal is to help you on your own adventure,\n" .
 			"By connecting you to collaborators on similar paths.\n\n" .
 			"Please click the link below to verify your email address and get started:\n\n" .
 			$verification_link . "\n\n" .
 			"Happy collaborating,\n" .
 			"The Adjacent Team";

 	// $from = 'adjacentapp@gmail.com';
 	// $headers = "MIME-Version: 1.0\r\n"; 
 	// $headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
 	// $headers = "Reply-To: The Adjacent Team <$from>\r\n";
 	// $headers .= "Return-Path: The Adjacent Team <$from>\r\n";
 	// $headers .= "From: The Adjacent Team <$from>\r\n";
 	// $headers .= "Organization: Adjacent\r\n";
 	// $headers .= "MIME-Version: 1.0\r\n";
 	// $headers .= "Content-type: text/plain; charset=utf-8\r\n";
 	// $headers .= "X-Mailer: PHP/" . PHP_VERSION;

 	// if( mail($email, 'Adjacent email verification', $content, $headers) )
 	// 	exit('email_sent');
 	// else
 	// 	exit('email_fail');

 	require '../vendor/autoload.php';
 	use Aws\Ses\SesClient;
 	if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJPXKHZFBD2GJIDEQ');
 	if (!defined('awsSecretKey')) define('awsSecretKey', '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');
 	$client = SesClient::factory(array(
 	    'version'=> 'latest',     
 	    'region' => 'us-east-1',
 	    'key'    => awsAccessKey,
 	    'secret' => awsSecretKey
 	));
 	$request = array();
 	$request['Source'] = 'adjacentapp@gmail.com';
 	$request['Destination']['ToAddresses'] = array($email);
 	$request['Message']['Subject']['Data'] = 'Adjacent email verification';
 	$request['Message']['Body']['Text']['Data'] = $content;

 	try {
 	     $result = $client->sendEmail($request);
 	     $messageId = $result->get('MessageId');
 	     echo("Email sent! Message ID: $messageId"."\n");

 	} catch (Exception $e) {
 	     echo("The email was not sent. Error message: ");
 	     echo($e->getMessage()."\n");
 	}
	
?>