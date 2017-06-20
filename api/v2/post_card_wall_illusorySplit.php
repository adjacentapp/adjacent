<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = mysqli_real_escape_string($db, $data->card_id);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$text = mysqli_real_escape_string($db, $data->text);
	@$response_to = $data->response_to ? mysqli_real_escape_string($db, $data->response_to) : 'null';
	@$member = $data->member ? true : 'null';
	@$photo_url = $data->photo_url ? $data->photo_url : 'null';

	// Query database
 	$query =	"INSERT INTO card_walls " .
 				"(user_id, card_id, message, response_to, member, photo_url) " .
 				"VALUES (" .
					$user_id .
				", " .
 					$card_id .
 				", " .
 					"'" . $text . "'" .
 				", " .
 					$response_to .
 				", " .
 					$member .
 				", " .
 					"'" . $photo_url . "'" .
 				")";
 	$res = mysqli_query($db, $query);

 	$post_id = mysqli_insert_id($db);
 	echo $post_id;


 	// Update card timestamp
 	$query =	"UPDATE cards " .
 				"SET update_time = now() " .
 				"WHERE id = " . $card_id;
 	$res = mysqli_query($db, $query);


 	//=======================================Post receipts
	
	// alert all team members except poster
	$members_to_notify = array();
	$query =	"SELECT * FROM collaborations" .
 				" WHERE card_id = " . $card_id .
 				" AND user_id != " . $user_id .
 				" AND accepted = 1";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$members_to_notify[] = $row['user_id'];

 	// Create receipt for teammates
 	for($i=0; $i<count($members_to_notify); $i++){
 		$query =	"INSERT INTO wall_receipts " .
 					"(user_id, post_id, card_id) " .
 					"VALUES (" .
 						$members_to_notify[$i] . ", " .
 						$post_id . ", " .
 						$card_id .
 					")";
 		$res = mysqli_query($db, $query);
 	}

 	// Avoid notifiying poster, and ensure array isn't empty
 	$members_to_notify[] = $user_id;

 	// if a response, alert response_to poster
 	if($response_to !== 'null'){
 		$response_to_user_id = 0;
 		$query =	"SELECT * FROM card_walls" .
					" WHERE id = " . $response_to .
					" LIMIT 1";
		$res = mysqli_query($db, $query);
	 	while($row = mysqli_fetch_assoc($res))
	 		$response_to_user_id = $row['user_id'];

	 	// Don't alert members here, even if in response to
	 	if(!in_array($response_to_user_id, $members_to_notify)){
		 	// Create response receipt for response_to poster
		 	$query =	"INSERT INTO update_receipts " .
						"(user_id, post_id, card_id, response_to) " .
						"VALUES (" .
							$response_to_user_id . ", " .
							$post_id . ", " .
							$card_id . ", " .
							$response_to .
						")";
			$res = mysqli_query($db, $query);
		}
 	}

 	// if from a member, alert all followers
 	$users_to_notify = array();
 	if($member === true){
		$query =	"SELECT * FROM bookmarks" .
					" WHERE card_id = " . $card_id .
					" AND user_id NOT IN (" . implode($members_to_notify, ", ") . " )" .
					" AND card_active = 1" .
					" AND active = 1";
		$res = mysqli_query($db, $query);
	 	while($row = mysqli_fetch_assoc($res))
	 		$users_to_notify[] = $row['user_id'];

 	 	// Create receipt for each user
 	 	for($i=0; $i<count($users_to_notify); $i++){
 	 		if($users_to_notify[$i] != $user_id){
 		 		$query =	"INSERT INTO update_receipts " .
 		 					"(user_id, post_id, card_id) " .
 		 					"VALUES (" .
 		 						$users_to_notify[$i] . ", " .
 		 						$post_id . ", " .
 		 						$card_id .
 		 					")";
 		 		$res = mysqli_query($db, $query);
 		 	}
 	 	}
	 }

	 // Push notification for each user
	 // $query =	"SELECT * FROM users WHERE user_id = " . $user_id;
	 // $res = mysqli_query($db, $query);
	 // while($row = mysqli_fetch_assoc($res))
	 // 	$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
	 // $message = $fir_name . ": " . $text;

	 // $all_to_notify = array_merge($members_to_notify, $users_to_notify);

	 // // update basge_count
	 // $query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id IN (" . implode($all_to_notify, ", ") . " ) AND user_id != " . $user_id;
	 // $res = mysqli_query($db, $query);

	 // $query =	"SELECT * FROM devices WHERE user_id IN (" . implode($all_to_notify, ", ") . " ) AND user_id != " . $user_id;
	 // $res = mysqli_query($db, $query);
	 // $tokens = array();
	 // while($device_row = mysqli_fetch_assoc($res))
	 // 	$tokens[] = $device_row['token'];

	 // require_once('push_notification.php');
	 // push_notification("New wall post", $message, $tokens);

	 // Push notification for each user
	 $query =	"SELECT * FROM users WHERE user_id = " . $user_id;
	 $res = mysqli_query($db, $query);
	 while($row = mysqli_fetch_assoc($res))
	 	$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
	 $message = $fir_name . ": " . $text;

	 $all_to_notify = array_merge($members_to_notify, $users_to_notify);

	 // update badge_count
	 $query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id IN (" . implode($all_to_notify, ", ") . " ) AND user_id != " . $user_id;
	 $res = mysqli_query($db, $query);

	 $query =	"SELECT * FROM devices WHERE user_id IN (" . implode($all_to_notify, ", ") . " ) AND user_id != " . $user_id;
	 $res = mysqli_query($db, $query);
	 $tokens = array();
	 while($row = mysqli_fetch_assoc($res))
	 	$tokens[] = $row;


	 require_once('push_notification.php');

	 $query =	"SELECT * FROM users WHERE user_id IN (" . implode($all_to_notify, ", ") . " ) AND user_id != " . $user_id;
	 $res = mysqli_query($db, $query);
	 while($row = mysqli_fetch_assoc($res)){
	 	$badge_count = $row['badge_count'];
	 	$user_tokens = array();
	 	foreach($tokens as $key => $token)
	 		if($token['user_id'] == $row['user_id'])
	 			$user_tokens[] = $token['token'];

	 	push_notification("New wall post", $message, $user_tokens, $badge_count);
	 }

	 // require_once('push_notification.php');
	 // push_notification("New wall post", $message, $tokens);
	
 	// Close connection
	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>