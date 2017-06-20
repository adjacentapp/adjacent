<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = $data->card_id ? mysqli_real_escape_string($db, $data->card_id) : null;
	@$user_id = mysqli_real_escape_string($db, $data->user_id);

	// Query database
 	$query =	"UPDATE collaborations " .
 				"SET accepted = 0" .
 				" WHERE card_id = " . $card_id .
 				" AND user_id = " . $user_id;
 	$res = mysqli_query($db, $query);

 	// Delete unread message receipts
 	$query =	"DELETE FROM message_receipts " .
 				"WHERE user_id = " . $user_id .
 				" AND card_id = " . $card_id;
 	$res = mysqli_query($db, $query);
 	// Delete unread wall receipts
 	$query =	"DELETE FROM wall_receipts " .
 				"WHERE user_id = " . $user_id .
 				" AND card_id = " . $card_id;
 	$res = mysqli_query($db, $query);

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>