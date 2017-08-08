<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$conversation_id = mysqli_real_escape_string($db, $data->conversation_id);
	@$new_conversation = $data->conversation_id ? false : true;
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$other_id = mysqli_real_escape_string($db, $data->other_id);
	@$text = mysqli_real_escape_string($db, $data->text);

	// Create new conversation if none exists
	if($new_conversation){
	 	$query =	"INSERT INTO conversations " .
	 				"(author_id, other_id, card_id, message_time) " .
	 				"VALUES (" .
	 					$user_id .
	 				", " .
	 					$other_id .
	 				", " .
	 					0 .
	 				", " .
	 					"now()" .
	 				")";
	 	$res = mysqli_query($db, $query);
	 	$conversation_id = mysqli_insert_id($db);
	 }

	 echo $conversation_id;
 	
 	// Insert new message
 	$query =	"INSERT INTO messages " .
 				"(conversation_id, user_id, text) " .
 				"VALUES (" .
 					$conversation_id .
 				", " .
 					$user_id .
 				", " .
 					"'" . $text . "'" .
 				")";
 	$res = mysqli_query($db, $query);
 	$message_id = mysqli_insert_id($db);

 	// Update conversation timestamp
 	if(!$new_conversation){
	 	$query =	"UPDATE conversations " .
	 				"SET message_time = now() " .
	 				"WHERE conversation_id = " . $conversation_id;
	 	$res = mysqli_query($db, $query);
	 }


	 // Check if user is blocked
	$query =	"SELECT * FROM silencers " .
				"WHERE user_id = " . $other_id .
				" AND other_id = " . $user_id;
	$res = mysqli_query($db, $query);
	if(mysqli_num_rows($res)>0){
		// Close connection
		mysqli_free_result($res);
		mysqli_close($db);
		exit();
	}


  	// Post receipts
	$query =	"INSERT INTO message_receipts " .
				"(user_id, conversation_id, message_id) " .
				"VALUES (" .
					$other_id .
				", " .
					$conversation_id .
				", " .
					$message_id .
				")";
	$res = mysqli_query($db, $query);

	// Push notification
	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
	// $message = $fir_name . ": " . $text;
	$message = $text;
	$query =	"SELECT * FROM devices WHERE user_id = " . $other_id;
	$res = mysqli_query($db, $query);
	$tokens = array();
	while($row = mysqli_fetch_assoc($res))
		$tokens[] = $row['token'];

	// update badge_count
	$query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id = " . $other_id;
	$res = mysqli_query($db, $query);

	// get badge count
	$query =	"SELECT * FROM users WHERE user_id = " . $other_id;
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$badge_count = $row['badge_count'];
	
	// get badge count
	// $badge_count = 0;
	// $query =	"SELECT * FROM message_receipts WHERE user_id = " . $other_id;
	// $res = mysqli_query($db, $query);
	// while($row = mysqli_fetch_assoc($res))
	// 	$badge_count++;
	// $query =	"SELECT * FROM wall_receipts WHERE user_id = " . $other_id;
	// $res = mysqli_query($db, $query);
	// while($row = mysqli_fetch_assoc($res))
	// 	$badge_count++;
	// $query =	"SELECT * FROM update_receipts WHERE new = 1 AND user_id = " . $other_id;
	// $res = mysqli_query($db, $query);
	// while($row = mysqli_fetch_assoc($res))
	// 	$badge_count++;

	$url = 'adjacentapp://' . 'message_direct/' . $user_id . '?' . $fir_name;

	require_once('push_notification.php');
	push_notification('Message from ' . $fir_name, $message, $tokens, $badge_count, $url);

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>