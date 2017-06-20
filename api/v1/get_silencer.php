<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	if( isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('no user_id');
	$card_id = isset($_GET['card_id']) ? mysqli_real_escape_string($db, $_GET['card_id']) : 'null';
	$post_id = isset($_GET['post_id']) ? mysqli_real_escape_string($db, $_GET['post_id']) : 'null';
	$other_id = isset($_GET['other_id']) ? mysqli_real_escape_string($db, $_GET['other_id']) : 'null';

	// Query database
 	$query =	"SELECT * FROM silencers " .
 				"WHERE user_id = " . $user_id;
 	if($card_id != 'null')
 	$query .=	" AND card_id = " . $card_id;
 	else if($post_id != 'null')
 	$query .=	" AND post_id = " . $post_id;
 	else if($other_id != 'null')
 	$query .=	" AND other_id = " . $other_id;
 	$res = mysqli_query($db, $query);
 	
 	if(mysqli_num_rows($res)==0)
 		$silenced = false;
 	else
 		$silenced = true;
 	
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit($silenced)

?>