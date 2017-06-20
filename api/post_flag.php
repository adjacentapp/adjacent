<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$reporter_user_id = mysqli_real_escape_string(	$db, $data->reporter_user_id );
	@$reported_user_id = mysqli_real_escape_string(	$db, $data->reported_user_id );
	@$card_id = $data->card_id ? mysqli_real_escape_string( $db, $data->card_id ) : 'null';
	@$post_id = $data->post_id ? mysqli_real_escape_string( $db, $data->post_id ) : 'null';
	@$raw_text = $data->text;
	@$text = mysqli_real_escape_string(	$db, $data->text	);

	// Save to database
 	$query =	"INSERT INTO flags " .
 				"(reporter_user_id, reported_user_id, card_id, post_id, text) " .
 				"VALUES (" .
 					$reporter_user_id . "," .
 					$reported_user_id . "," .
 					$card_id . "," .
 					$post_id . "," .
 					"'" . $text . "'" .
 				")";
 	$res = mysqli_query($db, $query);

 	// Close connection
	mysqli_close($db);


	// Send reset email
	require 'vendor/autoload.php';
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
	$request['Destination']['ToAddresses'] = array('adjacentapp@gmail.com','salulos@gmail.com');
	$request['Message']['Subject']['Data'] = 'Flag Report';
	$request['Message']['Body']['Text']['Data'] = $raw_text;

	try {
	     $result = $client->sendEmail($request);
	     $messageId = $result->get('MessageId');
	     echo("Email sent! Message ID: $messageId"."\n");

	} catch (Exception $e) {
	     echo("The email was not sent. Error message: ");
	     echo($e->getMessage()."\n");
	}
?>