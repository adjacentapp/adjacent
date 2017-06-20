<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : null;
	@$card_id = $data->card_id ? mysqli_real_escape_string($db, $data->card_id) : null;
	@$conversation_id = $data->conversation_id ? mysqli_real_escape_string($db, $data->conversation_id) : null;
	@$badge_count = $data->badge_count ? mysqli_real_escape_string($db, $data->badge_count) : false;

	// Query database
 	$query =	"DELETE FROM message_receipts " .
 				"WHERE user_id = " . $user_id;
 	if($card_id){
 		if($card_id=='all')
 			$query .= " AND card_id IS NOT NULL";
 		else
 			$query .= " AND card_id = " . $card_id;
 	}
 	else if($conversation_id){
 		if($conversation_id=='all')
 			$query .= " AND conversation_id IS NOT NULL";
 		else
 			$query .= " AND conversation_id = " . $conversation_id;
 	}

 	$res = mysqli_query($db, $query);

 	$delete_count = mysqli_affected_rows($db);
 	// echo $delete_count;

 	// update basge_count
 	$query =	"UPDATE users SET badge_count = badge_count - " . $delete_count . " WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);

 	// update badge count
 	if($badge_count !== false){
 		$query =	"SELECT * FROM devices WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);
 		$tokens = array();
 		while($row = mysqli_fetch_assoc($res))
 			$tokens[] = $row['token'];
 		require_once('push_notification.php');
 		// update_badge($tokens, (int)$badge_count - $delete_count);
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
 		$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$badge_count = $row['badge_count'];
 		update_badge($tokens, $badge_count);
 		echo $badge_count;
 	}
 	
 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>