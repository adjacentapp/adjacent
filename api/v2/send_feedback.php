<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = mysqli_real_escape_string(	$db, $data->email	);
	@$raw_msg = $data->message;
	@$message = mysqli_real_escape_string(	$db, $data->message	);

	// Save to database
 	$query =	"INSERT INTO feedback " .
 				"(email, message) " .
 				"VALUES (" .
 					"'" . $email . "'" .
 				", " .
 					"'" . $message . "'" .
 				")";
 	$res = mysqli_query($db, $query);

 	// Close connection
	mysqli_close($db);


	// Send reset email
	$recipient = 'salulos@gmail.com';
	$headers = "From: " . $email . "\r\n" .
		"CC: aliana@umich.edu";

	// mail($recipient, 'Adjacent Feedback', $raw_msg, $headers);

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
	$request['Source'] = $email;
	$request['Destination']['ToAddresses'] = array('adjacentapp@gmail.com','salulos@gmail.com');
	$request['Message']['Subject']['Data'] = 'User feedback';
	$request['Message']['Body']['Text']['Data'] = $raw_msg;

	try {
	     $result = $client->sendEmail($request);
	     $messageId = $result->get('MessageId');
	     echo("Email sent! Message ID: $messageId"."\n");

	} catch (Exception $e) {
	     echo("The email was not sent. Error message: ");
	     echo($e->getMessage()."\n");
	}
?>