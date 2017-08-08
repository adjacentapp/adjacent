<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	if( isset($_GET['convo_id']) )
		$convo_id = mysqli_real_escape_string($db, $_GET['convo_id']);
	else
		exit('no convo_id');
	
	$offset = isset($_GET['offset']) ? mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$limit = 10;

 	$query =	"SELECT * FROM messages WHERE conversation_id = {$convo_id} ORDER BY time DESC LIMIT {$limit} OFFSET {$offset}";
	$res = mysqli_query($db, $query);

 	$messages = array();
 	while($row = mysqli_fetch_assoc($res))
 		$messages[] = $row;

 	// Get user_id's
 	$query = 	"SELECT * FROM conversations WHERE conversation_id = {$convo_id}";
 	$res = mysqli_query($db, $query);
 	$author_id;
 	$other_id;
 	while($row = mysqli_fetch_assoc($res)){
 		$author_id = $row['author_id'];
 		$other_id = $row['other_id'];
 	}

 	// Assign ['user'] to each message
 	$query = 	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users WHERE user_id = {$author_id} OR user_id = {$other_id}";
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		foreach($messages as $key => $m)
 			if($m['user_id'] == $row['id'])
 				$messages[$key]['user'] = $row;

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);
	exit(json_encode($messages));
?>