<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	if( isset($_GET['user_id']) ) {
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	} else {
		exit('no user_id');
	}
	$just_count = isset($_GET['just_count']) ? true : false;
	$limit = isset($_GET['limit']) ? (int)mysqli_real_escape_string($db, $_GET['limit']) : 20;
	$offset = isset($_GET['offset']) ? (int)mysqli_real_escape_string($db, $_GET['offset']) : 0;

	// Query database
 	$query =	"SELECT * FROM update_receipts" .
 				" WHERE user_id = " . $user_id .
 				" ORDER BY time DESC" .
 				" LIMIT " . $limit .
 				" OFFSET " . $offset;
 	$res = mysqli_query($db, $query);
 	if(!$res) exit('no res');

	// Get updates' post_id's
	$root_ids = array();
	$response_ids = array();
	$responses = array();
	$new_ids = array();
	$timestamps = array();
	$updates = array();
	$update_ids = array();
	$new_values = array();

	while($row = mysqli_fetch_assoc($res)){
		$updates[] = array();
		$timestamps[] = $row['time'];
		$update_ids[] = $row['id'];
		$new_values[] = $row['new'];
		if($row['response_to']){
			$response_ids[] = $row['response_to'];
			$responses[] = $row['post_id'];
			$root_ids[] = $row['response_to'];
			if($row['new'])
				$new_ids[] = $row['response_to'];
		}
		else{
			$root_ids[] = $row['post_id'];
			if($row['new'])
				$new_ids[] = $row['post_id'];
		}
	}

	if($just_count){
 		echo count($new_ids);
		// Close connection
	 	mysqli_free_result($res);
	 	mysqli_close($db);
		exit();
	}



 	$query =	"SELECT * FROM card_walls" .
 				" WHERE id IN ( " . implode($root_ids, ", ") . " )" .
 				" ORDER BY timestamp ASC";
 				// " AND response_to IS NULL" .
 	$res = mysqli_query($db, $query);
 	$update_count = mysqli_num_rows($res);
 	if(!$res) exit('no res');


 	//=====================create target updates array
 	$card_ids = array();
 	// $updates = array();
 	// // fill updates with empty arrays
 	// for($i=0; $i<$update_count-1; $i++)
 	// 	$updates[] = array();

	// keep track for unique response threads, or card threads
	$filed_responses = array();
	$filed_cards = array();
	$counter = 0;

	while($row = mysqli_fetch_assoc($res)){

		// limit updates to limit_count in request
		if($counter > $limit)
			break;

		$row['card'] = array();
		$row['likes'] = array();
		$row['responses'] = array();

		// check if new
		// if(in_array($row['id'],$new_ids)) $row['new'] = true;

		// check for specific preview
		$row['preview_id'] = null;
		if(in_array($row['id'],$response_ids)){
			foreach($response_ids as $key => $r)
				if($r == $row['id'])
					$row['preview_id'] = $responses[$key];
		}

		$card_ids[] = $row['card_id'];

		// place into array in original receipt.time order des
		foreach($root_ids as $key => $r)
			if($r == $row['id']){
				$row['timestamp'] = $timestamps[$key];
				$row['update_id'] = $update_ids[$key];
				$row['new'] = $new_values[$key];
				$updates[$key] = $row;
				break;
			}

		$counter++;
	}

	// condense $updates to remove empty values
	foreach($updates as $key => $u) {
        if(empty($u))
            unset($updates[$key]);
	}


	// Get post's total like count
 	$query =	"SELECT * FROM wall_post_likes" .
 				" WHERE post_id IN ( " . implode($root_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
 	while($like = mysqli_fetch_assoc($res))
 		foreach($updates as $key => $u)
 			if($u['id'] == $like['post_id'])
 				$updates[$key]['likes'][] = $like['user_id'];

 	// Get post's total response count
 	$query =	"SELECT * FROM card_walls" .
 				" WHERE response_to IN ( " . implode($root_ids, ", ") . " )" .
				" ORDER BY timestamp ASC";
	$res = mysqli_query($db, $query);
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($updates as $key => $u){
 			if($u['id'] == $msg['response_to'])
 				$updates[$key]['responses'][] = $msg;
 			// check for the preview_id stored up above
 			if($u['preview_id'] == $msg['id'])
 				$updates[$key]['preview'] = $msg;
 		}

 	// Get cards for each update
 	$query =	"SELECT * FROM cards" .
 				" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
	while($card = mysqli_fetch_assoc($res))
		foreach($updates as $key => $u)
			if($u['card_id'] == $card['id'])
				$updates[$key]['card'] = $card;

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);
	exit(json_encode($updates, JSON_PRETTY_PRINT));

?>