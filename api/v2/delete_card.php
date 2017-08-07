<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = $data->id ? mysqli_real_escape_string($db, $data->id) : null;

	// Query database
	$query =	"UPDATE cards SET active = 0" .
				" WHERE id = " . $card_id;
	$res = mysqli_query($db, $query);

	// deactivate bookmarks
	$query =	"UPDATE bookmarks SET card_active = 0" .
				" WHERE card_id = " . $card_id;
	$res = mysqli_query($db, $query);

	// deactivate likes
	$query =	"UPDATE likes SET card_active = 0" .
				" WHERE card_id = " . $card_id;
	$res = mysqli_query($db, $query);

 	// $query =	"DELETE FROM cards " .
 	// 			"WHERE id = " . $card_id;
 	// $res = mysqli_query($db, $query);

 	// // Delete collaborations, messages, and bookmarks for this card
 	// $query =	"DELETE FROM collaborations " .
 	// 			"WHERE card_id = " . $card_id;
 	// $res = mysqli_query($db, $query);

 	// $query =	"DELETE FROM messages " .
 	// 			"WHERE card_id = " . $card_id .
 	// $res = mysqli_query($db, $query);

 	// $query =	"DELETE FROM bookmarks " .
 	// 			"WHERE card_id = " . $card_id;
 	// $res = mysqli_query($db, $query);

 	// // Delete wall_posts and all reciepts
 	// $query =	"DELETE FROM card_walls " .
 	// 			"WHERE card_id = " . $card_id;
 	// $res = mysqli_query($db, $query);
 	// $query =	"DELETE FROM updates " .
 	// 			"WHERE card_id = " . $card_id;
 	// $res = mysqli_query($db, $query);
 	// $query =	"DELETE FROM wall_post_likes " .
 	// 			"WHERE card_id = " . $card_id;
 	// $res = mysqli_query($db, $query);
 	$query =	"DELETE FROM update_receipts " .
 				"WHERE card_id = " . $card_id;
 	$res = mysqli_query($db, $query);
 	$query =	"DELETE FROM wall_receipts " .
 				"WHERE card_id = " . $card_id;
 	$res = mysqli_query($db, $query);
 	$query =	"DELETE FROM message_receipts " .
 				"WHERE card_id = " . $card_id;
 	$res = mysqli_query($db, $query);

 	// Close connection
 	if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode( (object)array("deleted_id" => $card_id) ));
?>