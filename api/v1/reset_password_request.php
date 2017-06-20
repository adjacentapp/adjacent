<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = $data->email;
	@$reset_code = $data->reset_code;

	// Check for existing account
	$db = connect_db();
 	$query =	"SELECT * FROM users " .
 				"WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
 	$res = mysqli_query($db, $query);
	// if (mysqli_num_rows($res) == 0)
	$user = array();
	while($row = mysqli_fetch_assoc($res))
		$user[] = $row;
 	if( count($user) == 0)
 		exit('user_not_found');

 	// Delete existing reset request
 	$query =	"DELETE FROM reset_password_requests " .
 				"WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
 	$res = mysqli_query($db, $query);


	// Query database
 	$query =	"INSERT INTO reset_password_requests " .
 				"(email, reset_code) " .
 				"VALUES (" .
 					"'" . mysqli_real_escape_string($db, $email) . "'" .
 				", " .
 					"'" . $reset_code . "'" .
 				")";
 	$res = mysqli_query($db, $query);

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);


	// Send reset email
	$base_url = "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/";
	$reset_link = $base_url . 'reset_password.php/?email=' . $email . '&reset_code=' . $reset_code;
	$msg = 	"A password reset for your Adjacent account has been requested.\n" .
			"If you did not make this request, you can safely disregard this email.\n\n" .
			"If you would like to reset your password, you can do so by following the link below:\n" .
			$reset_link . "\n" .
			"(This link will expire after 2 hours)";
	$headers = "From: 'Adjacent App' <noreply@adjacentapp.com>\r\n";

	// mail($email, 'Adjacent password reset', $msg, $headers);
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
	$request['Message']['Subject']['Data'] = 'Adjacent password reset';
	$request['Message']['Body']['Text']['Data'] = $msg;

	try {
	     $result = $client->sendEmail($request);
	     $messageId = $result->get('MessageId');
	     echo("Email sent! Message ID: $messageId"."\n");

	} catch (Exception $e) {
	     echo("The email was not sent. Error message: ");
	     echo($e->getMessage()."\n");
	}
?>