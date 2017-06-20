<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');
	$db = connect_db();
	$tissueTesting = true;
	if($tissueTesting) exit(json_encode( (object)array("message" => "tissueTesting") ));
	
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = $data->card_id ? mysqli_real_escape_string($db, $data->card_id) : null;
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : null;
	@$new_entry = $data->new_entry ? mysqli_real_escape_string($db, $data->new_entry) : false;

	$msg = '';

	$bookmark_id = null;
	$query = 	"SELECT * FROM bookmarks " .
				"WHERE user_id = " . $user_id .
				" AND card_id = " . $card_id;
	$res = mysqli_query($db, $query);
	if($res)
 		while($row = mysqli_fetch_assoc($res))
	 		$bookmark_id = $row['id'];

	if(!$new_entry && $bookmark_id){
		$msg = 'deactivate bookmark';
	 	$query =	"UPDATE bookmarks SET active = 0 WHERE id = " . $bookmark_id;
	 	$res = mysqli_query($db, $query);
	}
	else {
		if($bookmark_id){
			$msg = 'reactivate bookmark';
			$query =	"UPDATE bookmarks SET active = 1 WHERE id = " . $bookmark_id;
			$res = mysqli_query($db, $query);	
		}
		else {
			$msg = 'create bookmark';
			$query =	"INSERT INTO bookmarks " .
						"(card_id, user_id) " .
						"VALUES (" .
							$card_id . ", " .
							$user_id .
						")";
			$res = mysqli_query($db, $query);
		}

		// Create alert for team members

	 	// Check if previous alert should be updated
	 	if(!$bookmark_id){
			$last_alert_id = null;
			$alert_count = 1;
			$repeat = false;
			$query =	"SELECT * FROM messages" .
		 				" WHERE card_id = " . $card_id .
		 				" AND response_to = 0" .
		 				" ORDER BY time DESC" .
		 				" LIMIT 1";
			$res = mysqli_query($db, $query);
			if($res)
				while($row = mysqli_fetch_assoc($res)){
			 		if($row['alert'] == '2'){
			 			if($row['user_id'] == $user_id) // prevent back-to-back repeats
			 				$repeat = true;
			 			else {
			 				$last_alert_id = $row['id'];
			 				$alert_count += (int)(explode(' ', $row['text'])[0]);
			 			}
			 		}
			 	}

		 	// Create/update notification messages
		 	//$message_id = null;
		 	if($last_alert_id){
			 	$query =	"UPDATE messages SET " .
			 					"text = '" . (string)$alert_count . " new followers' " .
			 				"WHERE id = " . $last_alert_id;
			 				// update timestamp?
			 	$res = mysqli_query($db, $query);
			 	// $message_id = mysqli_insert_id($db);
			 }
			 else if(!$repeat){ 
			 	$query =	"INSERT INTO messages " .
			 				"(user_id, text, card_id, alert, response_to) " .
			 				"VALUES (" .
			 					$user_id .
			 				", " .
			 					"'" . (string)$alert_count . " new follower'" .
			 				", " .
			 					$card_id .
			 				", " .
			 					"2" .
			 				", " .
			 					"0" .
			 				")";
			 	$res = mysqli_query($db, $query);
			 }
		}

	  	// Create array of users to notify
	 	$users_to_notify = array();
	 	$author_id = 0;
	 	$query =	"SELECT * FROM collaborations" .
	  				" WHERE card_id = " . $card_id .
	  				" AND accepted = 1";
	 	$res = mysqli_query($db, $query);
	 	if($res)
		  	while($row = mysqli_fetch_assoc($res)){
		  		$users_to_notify[] = $row['user_id'];
		  		if($row['status'] == 'owner')
		  			$author_id = $row['user_id'];
		  	}

		 // Create notification receipts
		 // if($message_id)
	 	// 	for($i=0; $i<count($users_to_notify); $i++){
		 // 		$query =	"INSERT INTO message_receipts " .
		 // 					"(user_id, card_id, message_id) " .
		 // 					"VALUES (" .
		 // 						$users_to_notify[$i] .
		 // 					", " .
		 // 						$card_id .
		 // 					", " .
		 // 						$message_id .
		 // 					")";
		 // 		$res = mysqli_query($db, $query);
		 // 	}

	  	//=======================================Push notifications
		if(!$bookmark_id){
		  	// Create notification
		  	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
		  	$res = mysqli_query($db, $query);
		  	while($row = mysqli_fetch_assoc($res))
		  		$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';

			$query =	"SELECT * FROM cards WHERE id = " . $card_id;
			$res = mysqli_query($db, $query);
			if($res)
				while($row = mysqli_fetch_assoc($res))
					$message = $row['idea'];

		  	// update badge_count
		  	$query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id IN (" . implode($users_to_notify, ", ") . " ) AND user_id != " . $user_id;
		  	$res = mysqli_query($db, $query);

		  	$query =	"SELECT * FROM devices WHERE user_id IN (" . implode($users_to_notify, ", ") . " ) AND user_id != " . $user_id;
		  	$res = mysqli_query($db, $query);
		  	$tokens = array();
		  	// while($device_row = mysqli_fetch_assoc($res))
		  		// $tokens[] = $device_row['token'];
		  	if($res)
		  		while($row = mysqli_fetch_assoc($res))
		 			$tokens[] = $row;

		  	require_once('push_notification.php');

		  	$query =	"SELECT * FROM users WHERE user_id IN (" . implode($users_to_notify, ", ") . " ) AND user_id != " . $user_id;
		  	$res = mysqli_query($db, $query);
		  	if($res)
			  	while($row = mysqli_fetch_assoc($res)){
			  		$badge_count = $row['badge_count'];
			  		$user_tokens = array();
			  		foreach($tokens as $key => $token)
			  			if($token['user_id'] == $row['user_id'])
			  				$user_tokens[] = $token['token'];

  			 	$title = $fir_name . " is now following your card";
  			 	$url = 'adjacentapp://' . 'collaboration/' . $card_id;

  			 	push_notification($title, $message, $user_tokens, $badge_count, $url);
		  	}
		 }
	}

 	// Close connection
 	if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode( (object)array("message" => $msg) ));
?>