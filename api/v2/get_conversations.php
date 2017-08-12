<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	if( isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('no user_id');
	@$other_id = isset($_GET['other_id']) ? mysqli_real_escape_string($db, $_GET['other_id']) : null;
	@$card_id = isset($_GET['card_id']) ? mysqli_real_escape_string($db, $_GET['card_id']) : null;
	$offset = isset($_GET['offset']) ? mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$limit = isset($_GET['limit']) ? mysqli_real_escape_string($db, $_GET['limit']) : 10;
	$card_ids = $card_id ? [$card_id] : [];
	$user_ids = [$user_id];
	$messages_limit = 10;

	// Get conversations
 	$query =	"SELECT conversations.*, update_time as timestamp FROM conversations " .
 				"WHERE" .
 							" (author_id = {$user_id} OR other_id = {$user_id})";
 	if($other_id) $query .= " AND (author_id = {$other_id} OR other_id = {$other_id})";
 	if($card_id) $query .= 	" AND (card_id = {$card_id})";
 	
 	$query .=	"ORDER BY update_time DESC LIMIT {$limit} OFFSET {$offset}";
 	$res = mysqli_query($db, $query);

 	$conversations = array();
 	while($row = mysqli_fetch_assoc($res)){
 		$row['messages'] = array();
		$conversations[] = $row;
		if($row['card_id']==0)
			$user_ids[] = $row['author_id'] == $user_id ? $row['other_id'] : $row['author_id'];
 	}

 	// Check for speified card_id and other_id
 	if(!count($conversations))
 		$conversations[] = array("conversation_id" => "-1", "other_id" => $other_id, "author_id" => $user_id, "card_id" => $card_id, "messages" => []);
 	if($other_id && !in_array($other_id, $user_ids))
 		$user_ids[] = $other_id;

 	// Add each message to appropriate conversation
 	$query = "";
 	foreach($conversations as $key => $c)
 		$query .= "(SELECT * FROM messages WHERE conversation_id = {$c["conversation_id"]} ORDER BY time DESC LIMIT {$messages_limit}) UNION ALL ";
	if(strlen($query)){
		$query = substr($query, 0, -11);
 		$res = mysqli_query($db, $query);
 	}
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($conversations as $key => $c)
 			if($c['conversation_id'] == $msg['conversation_id'])
 				$conversations[$key]['messages'][] = $msg;

 	// Get convo's ['other'] && messages' ['user']
 	$query = 	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users " .
 				"WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		foreach($conversations as $key => $c) {
 			
 			if($row['id'] != $user_id)
 				if($row['id'] == $c['author_id'] || $row['id'] == $c['other_id'])
 					$conversations[$key]['other'] = $row;
			
			foreach($c['messages'] as $ley => $m)
				if($m['user_id'] == $row['id'])
					$conversations[$key]['messages'][$ley]['user'] = $row;

			if ($c['card_id']!="0" && !in_array($c['card_id'], $card_ids))
				$card_ids[] = $c['card_id'];
		}

	// Get convo's ['card']
	if(count($card_ids)){
		include 'functions.php';
		$CARDS = get_cards_by_ids($card_ids);
		$CARDS = add_bookmark_user_ids($CARDS, $user_id);
		$CARDS = add_comment_user_ids($CARDS, $user_id);

 		foreach($CARDS as $key => $card) 
 			foreach($conversations as $ley => $convo) 
 				if($convo['card_id'] == $card['id'])
	 				$conversations[$ley]['card'] = $card;
	}

 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($conversations, JSON_PRETTY_PRINT));
?>