<html>
<head>
	<meta charset="UTF-8">
	<title>Adjacent - Email Verification</title>
</head>
<body>

<h3>Adjacent Email Verification</h3>

<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('db_connect.php');

	use Aws\Ses\SesClient;

	$msg = '';

	$db = connect_db();
	// Check for arguments
	if(isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('error');
	if(isset($_GET['verification_code']) )
		$verification_code = mysqli_real_escape_string($db, $_GET['verification_code']);
	else
		exit('error');

	// Query database
 	$query =	"SELECT * FROM users" .
 				" WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);

 	// if(!$res)
 	// 	echo mysqli_error();

 	// Check for reset_code match
	while($row = mysqli_fetch_assoc($res)) {
		$email = $row['email'];

		if($row['verification_code'] == $verification_code){
			$query =	"UPDATE users " .
						"SET verified = 1, " .
						"verification_code = '' " .
						"WHERE user_id = " . $user_id;
			$res = mysqli_query($db, $query);
			$msg = "Thank you. " . $row['email'] . " is now verified for your account.";

			// add networks
			$network_providers = ['umich.edu','dev'];
			$provider = explode("@", $row['email'], 2)[1];
			if($row['email'] == 'salulos@gmail.com') $provider = 'dev';
			if($row['email'] == 'selforsaken@gmail.com') $provider = 'dev';
			
			if(in_array($provider, $network_providers)){
				$query =	"INSERT INTO networks " .
							"(verified, user_id, network, email)" .
							" VALUES (" .
								"1, " .
								$user_id . ", " .
								"'" . $provider . "', " .
								"'" . $row['email'] . "'" .
							")";
				$res = mysqli_query($db, $query);
				$msg .= " You have also been automatically granted access to the " . strtoupper($provider) . " network.";
			}
			
		}
		else {
			// Reset verification code
			$verification_code = uniqid('', true);
			$query =	"UPDATE users " .
						"SET verified = 0, " .
						"verification_code = '" . $verification_code . "' " .
						"WHERE user_id = " . $user_id;
			$res = mysqli_query($db, $query);

			// // Send verification email
		 	$base_url = "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/";
		 	$verification_link = $base_url . 'verify_email.php?user_id=' . $user_id . '&verification_code=' . $verification_code;
	 	 	$content = 	"Thank you for joining us on our adventure!\n" .
	 	 			"Our goal is to help you on your own adventure,\n" .
	 	 			"By connecting you to collaborators on similar paths.\n\n" .
	 	 			"Please click the link below to verify your email address and get started:\n\n" .
	 	 			$verification_link . "\n\n" .
	 	 			"Happy collaborating,\n" .
	 	 			"The Adjacent Team";

	 	 	require 'vendor/autoload.php';
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
		 	     // echo("Email sent! Message ID: $messageId"."\n");
		 	     $msg = "We're sorry, an error has occurred. A new verificaiton email has been sent to " . $email;

		 	} catch (Exception $e) {
		 	     // echo("The email was not sent. Error message: ");
		 	     // echo($e->getMessage()."\n");
		 	     $msg = "We're sorry, an error has occurred. Please contact <a href='mailto:adjacentapp@gmail.com'>adjacentapp@gmail.com</a> to report the issue.";
		 	}
		}
	}

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	
 	echo $msg;

?>
	
</body>
</html>