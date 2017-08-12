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
	$offset = isset($_GET['offset']) ? mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$limit = isset($_GET['limit']) ? mysqli_real_escape_string($db, $_GET['limit']) : 10;

 	$card_ids = array();
 	$query =	"SELECT id, update_time as updated_at FROM cards WHERE author_id = {$user_id} AND active = 1 ORDER BY updated_at DESC LIMIT {$limit} OFFSET {$offset}";
 	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$card_ids[] = $row['id'];

	include 'functions.php';
	$CARDS = get_cards_by_ids($card_ids);
	$CARDS = add_bookmark_user_ids($CARDS, $user_id);
	$CARDS = add_comment_user_ids($CARDS, $user_id);
	
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($CARDS, JSON_PRETTY_PRINT));
?>