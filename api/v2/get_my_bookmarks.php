<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	$user_id = isset($_GET['user_id']) ? mysqli_real_escape_string($db, $_GET['user_id']) : 0;
	$offset = isset($_GET['offset']) ? mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$limit = 10;

	// Get id's of collaborations
 	$query =	"SELECT card_id FROM bookmarks WHERE user_id = {$user_id} AND active = 1 ORDER BY updated_at DESC LIMIT {$limit} OFFSET {$offset}";
 	$res = mysqli_query($db, $query);

	$card_ids = array();
	while($row = mysqli_fetch_assoc($res))
		$card_ids[] = $row['card_id'];

	$cards = array();
	if(count($card_ids)){
	 	$query =	"SELECT * FROM cards" .
	 				" WHERE id IN ( '" . implode($card_ids, "', '") . "' )" .
	 				" AND active = 1" .
	 				" ORDER BY FIELD(id, " . implode($card_ids, ", ") . " )";
	 	$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res)){
			$row['comments'] = array();
			$row['followers'] = array();
			$cards[] = $row;
		}

		// Get comments
		$query = 	"SELECT * FROM card_walls " .
					" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
					" AND prompt_id IS NULL";
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			foreach($cards as $key => $card)
				if($card['id'] == $row['card_id'])
					$cards[$key]['comments'][] = $row['id'];

		// Get other followers
		$query = 	"SELECT * FROM bookmarks " .
					" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
					" AND card_active = 1" .
					" AND active = 1";
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res))
			foreach($cards as $key => $card)
				if($card['id'] == $row['card_id'])
					$cards[$key]['followers'][] = $row['user_id'];
	}

	// Reformat keys
	foreach($cards as $key => $card){
		$cards[$key] = (object)array(
			"id"		=> 	$card['id'],
			"founder_id"=> 	$card['author_id'],
			"industry"	=> 	$card['industry_string'],
			"pitch"		=>	$card['idea'],
			"distance"	=>	'',
			"comments"	=>	$card['comments'],
			"following"	=>	true,
			"followers"	=>	$card['followers']
		);
	}
	
	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit( json_encode($cards, JSON_PRETTY_PRINT) );
?>