<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	if( isset($_GET['user_id']) ) {
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	} else {
		exit('no user_id');
	}
	$just_count = isset($_GET['just_count']) ? true : false;

	// get collaboration card_ids
	$card_ids = array();
	$query =	"SELECT * FROM collaborations" .
 				" WHERE user_id = " . $user_id .
 				" AND accepted = 1";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$card_ids[] = $row['card_id'];

 	// get receipts from collaborations
 	$root_ids = array();
 	if(count($card_ids)){
	 	$query =	"SELECT * FROM wall_receipts " .
	 				"WHERE user_id = " . $user_id .
	 				" AND new = 1" .
	 				" AND card_id IN (" . implode($card_ids, ", ") . " )";
	 	$res = mysqli_query($db, $query);
	 	if($res)
			while($row = mysqli_fetch_assoc($res))
				$root_ids[] = $row['post_id'];
	}

	if($just_count){
 		// $update_receipt_count = count(mysqli_fetch_assoc($res));
 		echo count($root_ids);
		// Close connection
	 	if($res) mysqli_free_result($res);
	 	mysqli_close($db);
		exit();
	}

	
 	$updates = array();
	if(count($root_ids)){
	 	$query =	"SELECT * FROM card_walls" .
	 				" WHERE id IN ( " . implode($root_ids, ", ") . " )" .
	 				" ORDER BY timestamp ASC";
	 				// " AND response_to IS NULL" .
	 	$res = mysqli_query($db, $query);
	 	if(!$res) exit(json_encode(array()));

	 	// $update_ids = array();
	 	// $card_ids = array();
	 	while($row = mysqli_fetch_assoc($res)){
	 		// $row['card'] = array();
	 		// $row['likes'] = array();
	 		// $row['responses'] = array();
	 		// $update_ids[] = $row['id'];
	 		// $card_ids[] = $row['card_id'];
	 		$updates[] = $row;
	 	}
	 }


	// // Get post's likes
 // 	$query =	"SELECT * FROM wall_post_likes" .
 // 				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )";
	// $res = mysqli_query($db, $query);

 // 	while($like = mysqli_fetch_assoc($res))
 // 		foreach($updates as $key => $u)
 // 			if($u['id'] == $like['post_id'])
 // 				$updates[$key]['likes'][] = $like['user_id'];


 // 	// Get post's responses
 // 	$query =	"SELECT * FROM card_walls" .
 // 				" WHERE response_to IN ( " . implode($update_ids, ", ") . " )" .
	// 			" ORDER BY timestamp ASC";
	// $res = mysqli_query($db, $query);

 // 	while($msg = mysqli_fetch_assoc($res))
 // 		foreach($updates as $key => $u)
 // 			if($u['id'] == $msg['response_to'])
 // 				$updates[$key]['responses'][] = $msg;


 // 	// Dont think this is necessary, since the card_id gets the job done
 // 	// Get cards for each update
 // 	$query =	"SELECT * FROM cards" .
 // 				" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	// $res = mysqli_query($db, $query);

	// while($card = mysqli_fetch_assoc($res))
	// 	foreach($updates as $key => $m)
	// 		if($m['card_id'] == $card['id'])
	// 			$updates[$key]['card'] = $card;

	// Close connection
 	if($res) mysqli_free_result($res);
 	mysqli_close($db);
	exit(json_encode($updates));

?>