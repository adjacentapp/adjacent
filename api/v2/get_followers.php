<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');
	$db = connect_db();

	if( isset($_GET['card_id']) )
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	else
		exit('no card_id');
	$offset = isset($_GET['offset']) ? mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$limit = isset($_GET['limit']) ? mysqli_real_escape_string($db, $_GET['limit']) : 20;

	$user_ids = [];
 	$query =	"SELECT user_id FROM bookmarks WHERE card_id = {$card_id}" .
 				" AND card_active = 1 AND active = 1" .
 				" ORDER BY updated_at DESC LIMIT {$limit} OFFSET {$offset}";
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
		$user_ids[] = $row['user_id'];

	if(!count($user_ids)) exit(json_encode(array()));

	$followers = array();
	$query = 	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users " .
				" WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$followers[] = $row;

 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($followers, JSON_PRETTY_PRINT));
?>