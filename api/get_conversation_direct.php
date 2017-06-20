<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	if( isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('no user_id');
	if( isset($_GET['other_id']) )
		$other_id = mysqli_real_escape_string($db, $_GET['other_id']);
	else
		exit('no other_id');

	// Find conversation_id
 	$query =	"SELECT * FROM conversations" .
 				" WHERE (" .
 					"author_id = " . 		$user_id .
 					" AND other_id = " . 	$other_id .
 				") OR (" .
					"author_id = " . 		$other_id .
					" AND other_id = " . 	$user_id .
				")";
	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res))
 		$conversation = $row;

	$db = connect_db();
 	$query =	"SELECT * FROM messages" .
 				" WHERE conversation_id = " . $conversation['conversation_id'] .
				" ORDER BY time ASC";
	$res = mysqli_query($db, $query);

 	$messages = array();
 	while($row = mysqli_fetch_assoc($res))
 		$messages[] = $row;

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);
	exit(json_encode($messages));
?>