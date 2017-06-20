<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : null;
	@$text = $data->text ? mysqli_real_escape_string($db, $data->text) : null;
	@$card_id = $data->card_id ? mysqli_real_escape_string($db, $data->card_id) : 0;
	@$response_to = $data->response_to ? mysqli_real_escape_string($db, $data->response_to) : 'null';

	// Query database
 	$query =	"INSERT INTO updates " .
 				"(user_id, text, card_id, response_to) " .
 				"VALUES (" .
 					$user_id .
 				", " .
 					"'" . $text . "'" .
 				", " .
 					$card_id .
 				", " .
 					$response_to .
 				")";

 	$res = mysqli_query($db, $query);

 	$update_id = mysqli_insert_id($db);

 	// Post receipts
 		$users_to_notify = array();
 		
 		// team member id's
 		$query =	"SELECT * FROM collaborations" .
 	 				" WHERE card_id = " . $card_id .
 	 				" AND user_id != " . $user_id;
 		$res = mysqli_query($db, $query);
 	 	while($row = mysqli_fetch_assoc($res))
 	 		$users_to_notify[] = $row['user_id'];

 	 	// followers id's
	 	$query =	"SELECT * FROM bookmarks" .
	 				" WHERE card_id = " . $card_id .
	 				" AND user_id NOT IN (" . implode($users_to_notify, ", ") . " )" .
	 				" AND card_active = 1" .
	 				" AND active = 1";
 		$res = mysqli_query($db, $query);
 	 	while($row = mysqli_fetch_assoc($res))
 	 		$users_to_notify[] = $row['user_id'];

 	// Create receipt for each user
 	for($i=0; $i<count($users_to_notify); $i++){
 		$query =	"INSERT INTO update_receipts " .
 					"(user_id, update_id) " .
 					"VALUES (" .
 						$users_to_notify[$i] .
 					", " .
 						$update_id .
 					")";
 		$res = mysqli_query($db, $query);
 	}

 	// Push notification for each user
 	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
 	$message = $fir_name . ": " . $text;
 	$query =	"SELECT * FROM devices WHERE user_id IN (" . implode($users_to_notify, ", ") . " )";
 	$res = mysqli_query($db, $query);
 	$tokens = array();
 	while($device_row = mysqli_fetch_assoc($res))
 		$tokens[] = $device_row['token'];

 	// update badge_count
 	$query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id IN (" . implode($users_to_notify, ", ") . " ) AND user_id != " . $user_id;
 	$res = mysqli_query($db, $query);

 	require_once('push_notification.php');

 	$query =	"SELECT * FROM users WHERE user_id IN (" . implode($users_to_notify, ", ") . " ) AND user_id != " . $user_id;
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res)){
 		$badge_count = $row['badge_count'];
 		$user_tokens = array();
 		foreach($tokens as $key => $token)
 			if($token['user_id'] == $row['user_id'])
 				$user_tokens[] = $token['token'];

 		push_notification("New update", $message, $user_tokens, $badge_count);
 	}
 	
 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>