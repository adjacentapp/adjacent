<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();
	$tissueTesting = true;

	// Check for metadata arguments
	if(isset($_GET['card_id']) )
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	else
		exit('No card id provided');

	$prompt_id = isset($_GET['prompt_id']) ? mysqli_real_escape_string($db, $_GET['prompt_id']) : false;

	$user_ids = [];

	// Query database
 	$query =	"SELECT * FROM card_walls" .
 				" WHERE card_id = " . $card_id .
 				" AND response_to IS NULL";
 	if($prompt_id)
 		$query .= " AND prompt_id = " . $prompt_id;
 	else
 		$query .= " AND prompt_id IS NULL";
 	$query .=	" ORDER BY timestamp DESC";
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$messages = array();
	$m_ids = array();
	while($row = mysqli_fetch_assoc($res)) {
		$row['responses'] = array();
		$row['likes'] = array();
		$row['dislikes'] = array();
		$messages[] = $row;
		$m_ids[] = $row['id'];
		if(!in_array($row['user_id'], $user_ids)) $user_ids[] = $row['user_id'];
	}

	// Get post's likes
 	$query =	"SELECT * FROM wall_post_likes" .
 				" WHERE card_id = " . $card_id;
 				// " AND post_id IN ( " . implode($m_ids, ", ") . " )";
	$res = mysqli_query($db, $query);

 	while($like = mysqli_fetch_assoc($res))
 		foreach($messages as $key => $m)
 			if($m['id'] == $like['post_id']){
 				if($tissueTesting){
	 				if($like['active'] == 1)
	 					$messages[$key]['likes'][] = '-99';
	 				else if($like['active'] == -1)
	 					$messages[$key]['dislikes'][] = '-99';
	 			}
	 			else {
	 				if($like['active'] == 1)
	 					$messages[$key]['likes'][] = $like['user_id'];
	 				else if($like['active'] == -1)
	 					$messages[$key]['dislikes'][] = $like['user_id'];
	 			}
 			}


 	// Query for all responses to initial message
 	$query =	"SELECT * FROM card_walls" .
 				" WHERE response_to IN ( " . implode($m_ids, ", ") . " )" .
 				" ORDER BY timestamp ASC";
	$res = mysqli_query($db, $query);

	if($res){
	 	while($row = mysqli_fetch_assoc($res)){
	 		if(!in_array($row['user_id'], $user_ids)) $user_ids[] = $row['user_id'];
	 		foreach($messages as $key => $m)
	 			if($m['id'] == $row['response_to'])
	 				$messages[$key]['responses'][] = $row;
	 	}
	 }

	 // Get user for each comment
	 $users = [];
	 $query =	"SELECT user_id, fir_name, las_name, photo_url FROM users" .
	 			" WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
	 $res = mysqli_query($db, $query);
	 if($res)
		 while($row = mysqli_fetch_assoc($res))
		 	$users[] = $row;
	 foreach($messages as $key => $message){
	 	$messages[$key]['user'] = array();
	 	foreach($users as $user){
	 		if($message['user_id'] == $user['user_id']){
	 			$messages[$key]['user'] = $user;
	 		}
	 	}
	 }

	if($res) mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($messages, JSON_PRETTY_PRINT));

?>