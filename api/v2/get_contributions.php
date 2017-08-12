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
 	$top_comments = array();
 	$query = 	"SELECT * FROM (" .
 					"SELECT card_walls.*, (" .
						"SELECT SUM(active) as score FROM wall_post_likes WHERE post_id = card_walls.id" .
					") AS score FROM card_walls " .
					"WHERE response_to IS NULL AND prompt_id IS NULL AND user_id = {$user_id}" .
					" ORDER BY score DESC" .
				") as x GROUP BY card_id ORDER BY timestamp DESC" .
				" LIMIT {$limit} OFFSET {$offset}";
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res)){
		$card_ids[] = $row['card_id'];
		$top_comments[] = $row;
 	}

 	if(!count($card_ids)) exit(json_encode([]));

 	$user;
 	$query = 	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users  WHERE user_id = {$user_id}";
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		$user = $row;

	include 'functions.php';
	$CARDS = get_cards_by_ids($card_ids);	
	$CARDS = add_bookmark_user_ids($CARDS, $user_id);
	$CARDS = add_comment_user_ids($CARDS, $user_id);

	foreach($CARDS as $key => $card)
		foreach($top_comments as $comment)
			if($card['id'] == $comment['card_id']){
				$CARDS[$key]['topComment'] = $comment;
				$CARDS[$key]['topComment']['user'] = $user;
			}

 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($CARDS, JSON_PRETTY_PRINT));
?>