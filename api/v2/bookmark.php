<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();
	$tissueTesting = false;
	if($tissueTesting) exit(json_encode( (object)array("message" => "tissueTesting") ));
	
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = $data->card_id ? mysqli_real_escape_string($db, $data->card_id) : null;
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : null;
	@$new_entry = $data->new_entry ? mysqli_real_escape_string($db, $data->new_entry) : false;

	$msg = '';

	$bookmark_id = null;
	$query = 	"SELECT * FROM bookmarks WHERE user_id = {$user_id} AND card_id = {$card_id}";
	$res = mysqli_query($db, $query);
	if($res)
 		while($row = mysqli_fetch_assoc($res))
	 		$bookmark_id = $row['id'];

	if(!$new_entry && $bookmark_id){
		$msg = 'deactivate bookmark';
	 	$query =	"UPDATE bookmarks SET active = 0 WHERE id = {$bookmark_id}";
	 	$res = mysqli_query($db, $query);
	}
	else {
		if($bookmark_id){
			$msg = 'reactivate bookmark';
			$query =	"UPDATE bookmarks SET active = 1 WHERE id = {$bookmark_id}";
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

			// Notify card owner

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
		  	$founder_id;
		  	$query =	"SELECT * FROM cards WHERE id = {$card_id}";
		  	while($row = mysqli_fetch_assoc($res)){
		  		$founder_id = $row['author_id'];
		  		$message = $row['idea'];
		  	}

		  	// Create notification
		  	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
		  	$res = mysqli_query($db, $query);
		  	while($row = mysqli_fetch_assoc($res)){
		  		$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
		  		$badge_count = $row['badge_count'] + 1;
		  	}

		  	// update badge_count
		  	$query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id = {$founder_id}";
		  	$res = mysqli_query($db, $query);

		  	$query =	"SELECT * FROM devices WHERE user_id = {$founder_id}";
		  	$res = mysqli_query($db, $query);
		  	$tokens = array();
		  	if($res)
		  		while($device_row = mysqli_fetch_assoc($res))
			  		$tokens[] = $device_row['token'];	  	

		  	require_once('push_notification.php');
		  	// fix

		 	$title = $fir_name . " is now following your card";
		 	$url = 'adjacentapp://' . 'collaboration/' . $card_id;

		 	push_notification($title, $message, $tokens, $badge_count, $url);

		} // end if (create bookmark)
	}

 	// Close connection
 	if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode( (object)array("message" => $msg) ));
?>