<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();
	$tissueTesting = false;

	// Check for metadata arguments
	if(isset($_GET['card_id']) )
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	else
		exit('No card id provided');
	$offset = isset($_GET['offset']) ? mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$limit = isset($_GET['limit']) ? mysqli_real_escape_string($db, $_GET['limit']) : 10;

	$query =	"SELECT * from (" .
					"SELECT card_walls.*, (" .
						"SELECT SUM(active) as score FROM wall_post_likes WHERE post_id = card_walls.id" .
					") AS score FROM card_walls WHERE response_to IS NULL AND prompt_id IS NULL ORDER BY score DESC" .
				") AS x WHERE card_id = {$card_id}" .
				" ORDER BY score DESC LIMIT {$limit} OFFSET {$offset}";
 	$res = mysqli_query($db, $query);
 	if (mysqli_num_rows($res) === 0)
 		exit(json_encode(array()));

 	$user_ids = [];
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
 	$query =	"SELECT * FROM wall_post_likes WHERE card_id = {$card_id}";
	$res = mysqli_query($db, $query);
 	while($like = mysqli_fetch_assoc($res))
 		foreach($messages as $key => $m)
 			if($m['id'] == $like['post_id']){
 				if($like['active'] == 1)
 					$messages[$key]['likes'][] = $like['user_id'];
 				else if($like['active'] == -1)
 					$messages[$key]['dislikes'][] = $like['user_id'];
 			}

 	// Query for all responses to initial messages
 	$query =	"SELECT * FROM card_walls" .
 				" WHERE response_to IN ( " . implode($m_ids, ", ") . " )" .
 				" ORDER BY timestamp ASC";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res)){
 		if(!in_array($row['user_id'], $user_ids)) $user_ids[] = $row['user_id'];
 		foreach($messages as $key => $m)
 			if($m['id'] == $row['response_to'])
 				$messages[$key]['responses'][] = $row;
 	}

	 if(!count($user_ids)) exit(json_encode($messages, JSON_PRETTY_PRINT));

	 // Get user for each comment
	 $users = [];
	 $query =	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users" .
	 			" WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
	 $res = mysqli_query($db, $query);
	 if($res)
		 while($row = mysqli_fetch_assoc($res))
		 	$users[] = $row;
	 foreach($messages as $key => $message){
	 	$messages[$key]['user'] = array();
	 	foreach($users as $user){
	 		if($message['user_id'] == $user['id']){
	 			$messages[$key]['user'] = $user;
	 		}
			foreach($message['responses'] as $ley => $response)
				if($response['user_id'] == $user['id'])
					$messages[$key]['responses'][$ley]['user'] = $user;
	 	}
	 }

	if($res) mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($messages, JSON_PRETTY_PRINT));

?>
