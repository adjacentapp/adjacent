<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$bm = json_decode($postdata);
	@$user_id = mysqli_real_escape_string($db, $bm->user_id);
	@$card_id = $bm->card_id ? mysqli_real_escape_string($db, $bm->card_id) : false;
	@$response_to = $bm->response_to ? mysqli_real_escape_string($db, $bm->response_to) : false;
	@$update_id = $bm->update_id ? mysqli_real_escape_string($db, $bm->update_id) : false;
	@$post_id = $bm->post_id ? mysqli_real_escape_string($db, $bm->post_id) : false;
	// @$just_id = $bm->just_id ? mysqli_real_escape_string($db, $bm->just_id) : false;
	// @$ignore_id = $bm->ignore_id ? mysqli_real_escape_string($db, $bm->ignore_id) : false;
	@$badge_count = $bm->badge_count ? mysqli_real_escape_string($db, $bm->badge_count) : false;

	// Query database
 	// $query =	"DELETE FROM update_receipts" .
 	$query =	"UPDATE wall_receipts SET new = 0" .
 				" WHERE user_id = " . $user_id;
 	if($update_id)
 		$query .= 	" AND id = " . $update_id;
 	else if($response_to)
 		$query .= 	" AND response_to = " . $response_to;
 	else if($post_id)
 		$query .= 	" AND card_id = " . $card_id .
 					" AND post_id = " . $post_id;
 	else if($card_id != 'all')
 		$query .= 	" AND card_id = " . $card_id;
 		// $query .= 	" AND card_id = " . $card_id .
 		// 			" AND response_to IS NULL";
 	// else
 		// delete all
 					
 	$res = mysqli_query($db, $query);

 	$update_count = mysqli_affected_rows($db);
 	// echo $update_count;

 	// update basge_count
 	$query =	"UPDATE users SET badge_count = badge_count - " . $update_count . " WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);

 	// update badge count
 	if($badge_count !== false){
 		$query =	"SELECT * FROM devices WHERE user_id = " . $user_id;
 		$res = mysqli_query($db, $query);
 		$tokens = array();
 		while($row = mysqli_fetch_assoc($res))
 			$tokens[] = $row['token'];
 		require_once('push_notification.php');
 		// update_badge($tokens, (int)$badge_count - $update_count);
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