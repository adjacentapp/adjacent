<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');
	$db = connect_db();

	require '../vendor/autoload.php';
 	use Aws\Ses\SesClient;

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$post_email = mysqli_real_escape_string(	$db, $data->email	);

	function send_email($email){
		global $db;

		// Exit if already verified
	 	$query =	"SELECT * FROM users WHERE email = '{$email}'";
	 	$res = mysqli_query($db, $query);
	 	
	 	if(mysqli_num_rows($res) > 0)
	 		return;
	 		// return('mail_not_found');
	 	
	 	while($row = mysqli_fetch_assoc($res)){
	 		if($res['verified'] == 1 || $res['verified'] == '1')
	 			return; //('already_verified');
	 		else
	 			$user_id = $res['user_id'];
	 	}


 		// Reset verification code
 		$verification_code = uniqid('', true);
 		$query =	"UPDATE users " .
 					"SET verified = 0, " .
 					"verification_code = '" . $verification_code . "' " .
 					"WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);

 	 	// Close connection
 	 	if($res) mysqli_free_result($res);
 		mysqli_close($db);
return;
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

 	 	// require '../vendor/autoload.php';
 	 	// use Aws\Ses\SesClient;
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
	}

	if($post_email)
		exit( send_email( $post_email ) );
	
?>