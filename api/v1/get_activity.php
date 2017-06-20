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
	$limit = isset($_GET['limit']) ? (int)mysqli_real_escape_string($db, $_GET['limit']) : 20;
	$offset = isset($_GET['offset']) ? (int)mysqli_real_escape_string($db, $_GET['offset']) : 0;

	// exclude collaboration card_ids
	$card_ids = array();
	$query =	"SELECT * FROM collaborations" .
 				" WHERE user_id = " . $user_id .
 				" AND accepted = 1";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$card_ids[] = $row['card_id'];

	// get receipts
 	$query =	"SELECT * FROM wall_receipts" .
 				" WHERE user_id = " . $user_id .
 				" AND card_id NOT IN (" . implode($card_ids, ", ") . " )" .
 				" ORDER BY time DESC" .
 				" LIMIT " . $limit .
 				" OFFSET " . $offset;
 	$res = mysqli_query($db, $query);

 	$updates = array();
 	// create filler update if none found
 	if(!$res){
 		$rachel_id = "9";
 		$card_id = "33";
 		$last_message;
 		$last_tiemstamp;
 		$last_user;
 		$last_message_id;
 		$card = array();
 		$likes = array();
 		$responses = array();

 		$query =	"SELECT * FROM card_walls" .
 					" WHERE card_id = " . $card_id .
 					" ORDER BY timestamp ASC" .
 					" LIMIT 1";
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res)){
 			$last_message = $row['message'];
 			$last_timestamp = $row['timestamp'];
 			$last_user = $row['user_id'];
 			$last_message_id = $row['id'];
 		}
 		$query =	"SELECT * FROM cards WHERE id = " . $card_id;
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$card = $row;
 		$query =	"SELECT * FROM likes WHERE card_id = " . $card_id;
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$likes[] = $row['user_id'];
 		$query =	"SELECT * FROM card_walls WHERE card_id = " . $card_id;
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$responses[] = $row['user_id'];

 		$updates[] = array(
 			"id" => "0",
 			"card_id" => $card_id,
 			"message" => $last_message,
 			"preview" => array("message" => $last_message),
 			"timestamp" => $last_timestamp,
 			"user_id" => $rachel_id,
 			"response_to" => null,
 			"member" => null,
 			"photo_url" => null,
 			"card" => $card,
 			"likes" => $likes,
 			"responses" => $responses,
 			"preview_id" => $last_message_id,
 			"update_id" => "0",
 			"new" => "0",
 			"object" => $card,
			"content" => $card['idea']
 		);
 		exit(json_encode($updates, JSON_PRETTY_PRINT));
 	}

	// Get updates' post_id's
	$root_ids = array();
	$prompt_ids = array();
	$response_ids = array();
	$responses = array();
	$new_ids = array();
	$timestamps = array();
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
		else if($row['post_id']){
			$root_ids[] = $row['post_id'];
			if($row['new'])
				$new_ids[] = $row['post_id'];
		}
		else if($row['prompt_id']){
			$prompt_ids[] = $row['prompt_id'];
			if($row['new'])
				$new_ids[] = $row['prompt_id'];	
		}
	}

	if($just_count){
 		echo count($new_ids);
		// Close connection
	 	mysqli_free_result($res);
	 	mysqli_close($db);
		exit();
	}


	$activity = array();

 	$query =	"SELECT * FROM card_walls" .
 				" WHERE id IN ( " . implode($root_ids, ", ") . " )" .
 				" ORDER BY timestamp ASC";
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$activity[] = $row;

 	if(count($prompt_ids)){
	 	$query =	"SELECT * FROM prompts" .
	 				" WHERE id IN ( " . implode($prompt_ids, ", ") . " )" .
	 				" ORDER BY created_at ASC";
	 	$res = mysqli_query($db, $query);
	 	while($row = mysqli_fetch_assoc($res)){
	 		$row['timestamp'] = $row['created_at'];
	 		$row['prompt_id'] = $row['id'];
	 		$activity[] = $row;
	 	}
	 }

 	// sort $activity by timestamp field
 	function compare($a, $b){
		if($a['timestamp'] == $b['timestamp']) return 0;
		return ($a['timestamp'] < $b['timestamp']) ? 1 : -1;
	}
	usort( $activity, 'compare');


 	if(!count($activity)) exit(json_encode($updates, JSON_PRETTY_PRINT));


 	//=====================create target updates array
 	$card_ids = array();
	$counter = 0;

	// while($row = mysqli_fetch_assoc($res)){
	foreach($activity as $key => $row){

		// limit updates to limit_count in request
		if($counter > $limit)
			break;

		$activity[$key]['card'] = array();
		$activity[$key]['likes'] = array();
		$activity[$key]['liked'] = false;
		$activity[$key]['responses'] = array();
		$activity[$key]['iterations'] = array();

		// check for specific preview
		$activity[$key]['preview_id'] = null;
		if(in_array($row['id'],$response_ids)){
			foreach($response_ids as $j => $r)
				if($r == $row['id'] && !$activity[$key]['preview_id'])
					$activity[$key]['preview_id'] = $response_ids[$j];
		}

		// give wallposts same formatting as prompts
		// if(!array_key_exists('title', $row))
		// 	$activity[$key]['title'] = '';
		// if(!array_key_exists('text', $row))
		// 	$activity[$key]['text'] = $row['message'];

		$card_ids[] = $row['card_id'];

		// place into array in original receipt.time order des
		// foreach($root_ids as $i => $r)
		// 	if($r == $row['id']){
		// 		$activity[$key]['timestamp'] = $timestamps[$i];
		// 		$activity[$key]['update_id'] = $update_ids[$i];
		// 		$activity[$key]['new'] = $new_values[$i];
		// 		$updates[$i] = $activity[$key];
		// 		break;
		// 	}

		$counter++;
	}

	$updates = $activity;

	// condense $updates to remove empty values
	foreach($updates as $key => $u) {
        if(empty($u))
            unset($updates[$key]);
	}

 	// get card's likes for regular wall posts
 	$query = 	"SELECT * FROM likes " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND card_active = 1" .
				" AND active = 1";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		foreach($updates as $key => $update)
			// if(!array_key_exists('prompt_id', $update))
				if($update['card_id'] == $row['card_id']){
					$updates[$key]['likes'][] = $row['user_id'];
					if($row['user_id'] == $user_id)
						$updates[$key]['liked'] = true;
				}
 	// get prompts' likes for prompt posts OR new prompt updates
 	$query = 	"SELECT * FROM prompt_likes " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND card_active = 1" .
				" AND active = 1";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		foreach($updates as $key => $update)
			if(array_key_exists('prompt_id', $update))
				if($update['prompt_id'] == $row['prompt_id']){
					$updates[$key]['likes'][] = $row['user_id'];
					if($row['user_id'] == $user_id)
						$updates[$key]['liked'] = true;
				}

 	// get comments for regular wall posts
	$query = 	"SELECT * FROM card_walls " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND prompt_id IS NULL";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		foreach($updates as $key => $update)
			if(!array_key_exists('prompt_id', $update))
				if($update['card_id'] == $row['card_id'])
					$updates[$key]['responses'][] = $row;
	// get prompts' comments for prompt posts OR new prompt updates
	$query = 	"SELECT * FROM card_walls " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND prompt_id IS NOT NULL";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		foreach($updates as $key => $update)
			if(array_key_exists('prompt_id', $update))
				if($update['prompt_id'] == $row['prompt_id'])
					$updates[$key]['responses'][] = $row;



 	// Get cards for each update
 	$query =	"SELECT * FROM cards" .
 				" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
	if($res)
		while($card = mysqli_fetch_assoc($res))
			foreach($updates as $key => $u)
				if($u['card_id'] == $card['id']){
					$updates[$key]['card'] = $card;
					if(!array_key_exists('title', $u)){
						$updates[$key]['object'] = $card;
						$updates[$key]['content'] = $card['idea'];
					}
					if(!array_key_exists('user_id', $u)){
						$updates[$key]['user_id'] = $card['author_id'];
					}
				}
	// Get cards for each update
	$query =	"SELECT * FROM prompts" .
				" WHERE id IN ( " . implode($prompt_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
	if($res)
		while($prompt = mysqli_fetch_assoc($res))
			foreach($updates as $key => $u)
				if(array_key_exists('prompt_id', $u))
					if($u['prompt_id'] == $prompt['id']){
						$updates[$key]['object'] = $prompt;
						$updates[$key]['content'] = $prompt['text'];
					}

	// Close connection
 	if($res) mysqli_free_result($res);
 	mysqli_close($db);
	exit(json_encode($updates, JSON_PRETTY_PRINT));

?>



if(!array_key_exists('title', $row))
	$activity[$key]['title'] = '';
if(!array_key_exists('text', $row))
	$activity[$key]['text'] = $row['message'];