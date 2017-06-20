<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$card_id = mysqli_real_escape_string($db, $data->card_id);
	@$response = mysqli_real_escape_string($db, $data->response);
	$status = $response == 1 ? 'member' : 'denied';

	$query =	"UPDATE collaborations " .
				"SET accepted = " . $response . "," .
				" status = '" . $status . "', " .
				" accepted_time = now()" .
				" WHERE card_id = " . $card_id .
				" AND user_id = " . $user_id;
	$res = mysqli_query($db, $query);


	// Update card timestamp
	$query =	"UPDATE cards " .
				"SET update_time = now() " .
				"WHERE id = " . $card_id;
	$res = mysqli_query($db, $query);
	
	// notify accepted member
	if($response == 1){
		$query =	"INSERT INTO message_receipts " .
 					"(user_id, card_id) " .
 					"VALUES (" .
 						$user_id .
 					", " .
 						$card_id .
 					")";
 		$res = mysqli_query($db, $query);

 		// Push notification for each user
 		$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$fir_name = $row['fir_name'] ? $row['fir_name'] : 'Anonymous';
 		$message = $fir_name . ", your request to connect with this team has been approved.";
 		$query =	"SELECT * FROM devices WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);
 		$tokens = array();
 		while($row = mysqli_fetch_assoc($res))
 			$tokens[] = $row['token'];
 		
 		// update badge_count
 		$query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);

 		// get badge count
 		// $badge_count = 0;
 		// $query =	"SELECT * FROM message_receipts WHERE user_id = " . $user_id;
 		// $res = mysqli_query($db, $query);
 		// while($row = mysqli_fetch_assoc($res))
 		// 	$badge_count++;
 		// $query =	"SELECT * FROM wall_receipts WHERE user_id = " . $user_id;
 		// $res = mysqli_query($db, $query);
 		// while($row = mysqli_fetch_assoc($res))
 		// 	$badge_count++;
 		// $query =	"SELECT * FROM update_receipts WHERE new = 1 AND user_id = " . $user_id;
 		// $res = mysqli_query($db, $query);
 		// while($row = mysqli_fetch_assoc($res))
 		// 	$badge_count++;

 		// get badge count
 		$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$badge_count = $row['badge_count'];

 		$url = 'adjacentapp://' . 'message_group/' . $card_id;

 		require_once('push_notification.php');
 		push_notification("Welcome to the team", $message, $tokens, $badge_count, $url);
	}

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit($query);
?>