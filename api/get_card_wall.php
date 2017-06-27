<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Check for metadata arguments
	if(isset($_GET['card_id']) )
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	else
		exit('No card id provided');

	// Query database
 	$query =	"SELECT * FROM card_walls" .
 				" WHERE card_id = " . $card_id .
 				" AND response_to IS NULL" .
 				" ORDER BY timestamp DESC";
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$messages = array();
	$m_ids = array();
	while($row = mysqli_fetch_assoc($res)) {
		$row['responses'] = array();
		$row['likes'] = array();
		$messages[] = $row;
		$m_ids[] = $row['id'];
	}

	// Get post's likes
 	$query =	"SELECT * FROM wall_post_likes" .
 				" WHERE card_id = " . $card_id;
 				// " AND post_id IN ( " . implode($m_ids, ", ") . " )";
	$res = mysqli_query($db, $query);

 	while($like = mysqli_fetch_assoc($res))
 		foreach($messages as $key => $m)
 			if($m['id'] == $like['post_id'])
 				$messages[$key]['likes'][] = $like['user_id'];


 	// Query for all responses to initial message
 	$query =	"SELECT * FROM card_walls" .
 				" WHERE response_to IN ( " . implode($m_ids, ", ") . " )" .
				" ORDER BY timestamp ASC";
	$res = mysqli_query($db, $query);

	if($res){
	 	while($msg = mysqli_fetch_assoc($res))
	 		foreach($messages as $key => $m)
	 			if($m['id'] == $msg['response_to'])
	 				$messages[$key]['responses'][] = $msg;
	 					// fancy pants!

		// Close connection and return JSON
	 	mysqli_free_result($res);
	 }
 	mysqli_close($db);
 	exit(json_encode($messages));

?>