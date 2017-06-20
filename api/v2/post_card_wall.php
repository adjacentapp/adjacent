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
	@$photo_url = $data->photo_url ? "'" . $data->photo_url . "'" : 'null';
	@$prompt_id = $data->prompt_id ? mysqli_real_escape_string($db, $data->prompt_id) : 'null';

	// Query database
 	$query =	"INSERT INTO card_walls " .
 				"(user_id, card_id, message, response_to, member, photo_url, prompt_id) " .
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
 					$photo_url .
 				", " .
 					$prompt_id .
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
	
	// get team members
	$members_to_notify = array();
	$query =	"SELECT * FROM collaborations" .
 				" WHERE card_id = " . $card_id .
 				" AND user_id != " . $user_id .
 				" AND accepted = 1";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$members_to_notify[] = $row['user_id'];

 	// get followers
 	$followers_to_notify = array();
 		$all_members = $members_to_notify;
 		$all_members[] = $user_id;	// exclude poster, and ensure array isn't empty
	$query =	"SELECT * FROM bookmarks" .
				" WHERE card_id = " . $card_id .
				" AND user_id NOT IN (" . implode($all_members, ", ") . " )" .
				" AND card_active = 1" .
				" AND active = 1";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$followers_to_notify[] = $row['user_id'];

 	// combine arrays
 	$all_to_notify = array_merge($members_to_notify, $followers_to_notify);

 	// get response_to user
 	$response_to_user_id = 0;
 	if($response_to !== 'null'){
 		$query =	"SELECT * FROM card_walls" .
					" WHERE id = " . $response_to .
					" LIMIT 1";
		$res = mysqli_query($db, $query);
	 	while($row = mysqli_fetch_assoc($res))
	 		if($row['user_id'] != $user_id){
	 			$response_to_user_id = $row['user_id'];
	 			// add to array
	 			$all_to_notify[] = $response_to_user_id;
	 		}
	}

	// Create receipts
 	for($i=0; $i<count($all_to_notify); $i++){

 		// Check for silenced card or thread
 		$query =	"SELECT * FROM silencers" .
 					" WHERE user_id = " . $all_to_notify[$i] .
 					" AND card_id = " . $card_id;
 		if($response_to !== 'null')
 		$query .=	" OR post_id = " . $response_to;
 		$res = mysqli_query($db, $query);

 		if(mysqli_num_rows($res) > 0){
 			// insert receipt marked as 'read'
 			$query =	"INSERT INTO wall_receipts " .
 						"(user_id, post_id, card_id, response_to, prompt_id, new) " .
 						"VALUES (" .
 							$all_to_notify[$i] . ", " .
 							$post_id . ", " .
 							$card_id . ", " .
 							$response_to . ", " .
 							$prompt_id . ", " .
 							"0" .
 						")";
 			$res = mysqli_query($db, $query);
 			// remove user so that they will not receive push notification
 			unset($all_to_notify[$i]);
 		}
 		else {
 			// insert new receipt
 			$query =	"INSERT INTO wall_receipts " .
 						"(user_id, post_id, card_id, response_to, prompt_id) " .
 						"VALUES (" .
 							$all_to_notify[$i] . ", " .
 							$post_id . ", " .
 							$card_id . ", " .
 							$response_to . ", " .
 							$prompt_id .
 						")";
 			$res = mysqli_query($db, $query);
 		}
 	}

 	//=======================================Push notifications

 	// Create notification
	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
	// $message = $fir_name . ": " . $text;
	$message = $text;

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

	 	// $title = "New post from card you're following";
 		// if($response_to !== 'null' && $row['user_id'] == $response_to_user_id)
 		// 	$title = "New reply to your post";
	 	// else if(in_array($row['user_id'], $members_to_notify))
	 	// 	$title = "New post from collaborations";
	 	$title = $fir_name . " commented on a card you're following";
	 	$url = 'adjacentapp://' . 'activity/' . $card_id . '?' . $post_id;
	 	if(in_array($row['user_id'], $members_to_notify)){
	 		$title = $fir_name . " commented on your card";
	 		$url = 'adjacentapp://' . 'collaboration/' . $card_id . '?' . $post_id;
	 	}
	 	else if($response_to !== 'null' && $row['user_id'] == $response_to_user_id){
 			$title = $fir_name . " replied to your comment";
 		}

	 	push_notification($title, $message, $user_tokens, $badge_count, $url);
	 }
	
 	// Close connection
	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>