<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Check for metadata arguments
	if(isset($_GET['card_id']) )
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	else
		exit('No card id provided');

	// Query database
 	$query =	"SELECT * FROM prompts" .
 				" WHERE card_id = " . $card_id .
 				" ORDER BY updated_at DESC";
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$prompts = array();
	$prompt_ids = array();
	while($row = mysqli_fetch_assoc($res)) {
		$row['iterations'] = array();
		$row['comments'] = array();
		$row['likes'] = array();
		$prompts[] = $row;
		$prompt_ids[] = $row['id'];
	}

	// Get number of comments
	$query = 	"SELECT * FROM card_walls " .
				" WHERE card_id = " . $card_id .
				" AND prompt_id IS NOT NULL";
	$res = mysqli_query($db, $query);
	while($comment = mysqli_fetch_assoc($res))
		foreach($prompts as $key => $prompt)
			if($prompt['id'] == $comment['prompt_id'])
				$prompts[$key]['comments'][] = $comment['id'];

	// Get post's likes
 	$query =	"SELECT * FROM prompt_likes" .
 				" WHERE card_id = " . $card_id;
	$res = mysqli_query($db, $query);
 	while($like = mysqli_fetch_assoc($res))
 		foreach($prompts as $key => $prompt)
 			if($prompt['id'] == $like['prompt_id'])
 				$prompts[$key]['likes'][] = $like['user_id'];

	// Close connection and return JSON
	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($prompts));

?>