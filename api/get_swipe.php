<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	if( isset($_GET['card_id']) ) {
		$card_id = $_GET['card_id'];
	} else {
		exit('no card_id');
	}

	if( isset($_GET['user_id']) ) {
		$user_id = $_GET['user_id'];
	} else {
		exit('no user_id');
	}

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM swipes " .
 				"WHERE card_id = " . $card_id .
 				" AND user_id = " . $user_id;
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$swipes = array();
	while($row = mysqli_fetch_assoc($res))
		$swipes[] = $row;

	// Close connection and ID
 	mysqli_free_result($res);
 	mysqli_close($db);

 	if( count($swipes) == 0)
 		exit('no_swipe');
 	else
 		exit($swipes[0]['like_bool']);

?>