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

	// send verification code
	$network_providers = ['umich.edu','dev'];
	$verification_code = uniqid('', true);
	$provider = explode("@", $email, 2)[1];
	if($email == 'salulos@gmail.com') $provider = 'dev';

	if(in_array($provider, $network_providers)){
		
		// check for existing
		$query =	"SELECT * FROM networks " .
					"WHERE network = '" . $provider . "'" .
					" AND " .
					"user_id = " . $user_id;
		$res = mysqli_query($db, $query);
		if(mysqli_num_rows($res) > 0){
			while($row = mysqli_fetch_assoc($res)) {
				// if unverified, update
				if(!$row['verified']){
					$query =	"UPDATE networks" .
								" SET" .
									" verification_code = '" . $verification_code . "'" .
								" WHERE" .
									" user_id = " . $user_id .
									" AND network = '" . $provider . "'";
					$res = mysqli_query($db, $query);
				}
				else{
					// if verified, return message
					exit('already_verified');
				}
			}
		}
		else {
			// create new entry
			$query =	"INSERT INTO networks " .
						"(user_id, network, verification_code, email) " .
						"VALUES (" .
							$user_id . ", " .
							"'" . $provider . "', " .
							"'" . $verification_code . "', " .
							"'" . $email . "'" .
						")";
			$res = mysqli_query($db, $query);
		}
	}
	else
		exit('network_not_found');

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);

	// Send verification email
	$base_url = "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/";
 	$verification_link = $base_url . 'verify_network.php?user_id=' . $user_id . '&network=' . $provider . '&verification_code=' . $verification_code;
 	// $verification_link = 'http://www.salsaia.com/aether/server/verify_network.php/?user_id=' . $user_id . '&network=' . $provider . '&verification_code=' . $verification_code;
 	$content = 	"Thank you for joing the " . strtoupper($provider) . " network!\n" .
 			"By becoming a part of this network,\n" .
 			"you will gain access to quality cards and collaborators,\n" .
 			"right from your local community!\n\n" .
 			"Please click the link below to finish:\n\n" .
 			$verification_link . "\n\n" .
 			"Happy collaborating,\n" .
 			"The Adjacent Team";
 	$headers = "From: 'Adjacent App' <noreply@adjacentapp.com>\r\n";

 	// mail($email, 'Adjacent network verification', $content, $headers);

	// exit('email_sent');
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
	$request['Message']['Subject']['Data'] = 'Adjacent network verification';
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