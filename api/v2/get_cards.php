<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	require_once('haversine_formula.php');
	$db = connect_db();
	$tissueTesting = false;

	// Check for arguments
	$card_id = isset($_GET['card_id']) ? mysqli_real_escape_string($db, $_GET['card_id']) : 0;
	$user_id = isset($_GET['user_id']) ? mysqli_real_escape_string($db, $_GET['user_id']) : 0;
	$order_by = isset($_GET['order_by']) ? mysqli_real_escape_string($db, $_GET['order_by']) : false;
	$types = isset($_GET['types']) ? mysqli_real_escape_string($db, $_GET['types']) : '1,2,3';

	$limit = isset($_GET['limit']) ? mysqli_real_escape_string($db, $_GET['limit']) : '15';
	$offset = isset($_GET['offset']) ? (int)mysqli_real_escape_string($db, $_GET['offset']) : 0;
	$sub_offset = isset($_GET['sub_offset']) ? (int)mysqli_real_escape_string($db, $_GET['sub_offset']) : 0;
	
	$distance = isset($_GET['distance']) ? mysqli_real_escape_string($db, $_GET['distance']) : false;
	$lat = isset($_GET['lat']) ? mysqli_real_escape_string($db, $_GET['lat']) : false;
	$lon = isset($_GET['lon']) ? mysqli_real_escape_string($db, $_GET['lon']) : false;

	// Order cards by score
 	function compare($a, $b){
		if($a['score'] == $b['score']) return 0;
		return ($a['score'] < $b['score']) ? 1 : -1;
	}

	// get user's offest
	$user_offset = 0;
	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$offset += $row['discover_offset'];

 	// Ordering algorithm weighing by newness, activity, and likes
 	$cards = array();
	$card_ids = array();
	$new_offset = $offset;
	$new_sub_offset = $sub_offset;
	$step = 25; // arbitrary chunk for filtering through

	$query =		"SELECT COUNT(id) as total FROM cards WHERE active = 1 AND type IN ({$types})";
	$res = mysqli_query($db, $query);
	$data = mysqli_fetch_assoc($res);
	$card_max = $data['total'];

	function getCard(){
		global 	$cards, $card_ids, $card_id, $db;
		$query =	"SELECT * FROM cards" .
					" WHERE id = " . $card_id;
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res)) {
			$cards[] = $row;
			$card_ids[] = $row['id'];
		}
	}

	function getCards(){
		global 	$cards, $card_ids, $card_id, $types, $order_by, $limit, 
				$step, $offset, $new_offset, $sub_offset, $new_sub_offset, 
				$distance, $lat, $lon, $db, $card_max;

		$ordered_cards = [];
		
		$query = "SELECT * FROM cards WHERE active = 1 AND type IN ({$types}) ORDER BY" .
					" (SELECT SUM(active) as score FROM wall_post_likes WHERE card_id = cards.id)" .
					" desc LIMIT {$step} OFFSET {$offset}";
		
		$res = mysqli_query($db, $query);
		if(!$res) exit(mysqli_error());
		while($row = mysqli_fetch_assoc($res))
	 		$ordered_cards[] = $row;

		foreach($ordered_cards as $index => $row) {
			if($index >= $sub_offset){
				if(count($card_ids) < (int)$limit){
					$new_sub_offset++;	 // next time, start from after this card

					$row['saved'] = false;
					$row['liked'] = false;
					$row['member'] = false;

					if($lat && $lon){
						if($ordered_cards[$index]['lat'] && $ordered_cards[$index]['lon']){
							$miles_away = haversineGreatCircleDistance(
								$lat, $lon, $ordered_cards[$index]['lat'], $ordered_cards[$index]['lon']);
							$row['distance'] = $miles_away;
						}
						else
							$miles_away = 9999999;
					}

					if(!$distance || $miles_away <= $distance){
						$cards[] = $row;
						$card_ids[] = $row['id'];
					}
				}
			}
		}

		if(count($card_ids) < (int)$limit){
			if($offset + $step > (int)$card_max) {
				$new_offset = false;
			} else {
				$offset += $step;
				$new_offset = $offset;
				$sub_offset = 0;
				$new_sub_offset = 0;
				getCards();
			}
		}
	}

	if($card_id > 0)
		getCard();
	else
		getCards();


	foreach($cards as $key => $card) {
		$cards[$key]['team'] = array();
		$cards[$key]['followers'] = array();
		$cards[$key]['likes'] = array();
		$cards[$key]['dislikes'] = array();
		$cards[$key]['comments'] = array();

		// Check for other prompt photos if no card.photo_url
		if(!$cards[$key]['photo_url']){
			$query = 	"SELECT * FROM prompts" .
						" WHERE card_id = " . $cards[$key]['id'] .
						" AND photo_url IS NOT NULL" .
						" AND photo_url != 'null'" .
						" AND photo_url != ''" .
						" ORDER BY updated_at DESC" .
						" LIMIT 1";
			$res = mysqli_query($db, $query);
			if($res)
				while($row = mysqli_fetch_assoc($res))
					$cards[$key]['photo_url'] = $row['photo_url'];
		}

		// Check for latest wall_photo if not card.photo_url
		if(!$cards[$key]['photo_url']){
			$query = 	"SELECT * FROM card_walls" .
						" WHERE card_id = " . $cards[$key]['id'] .
						" AND photo_url IS NOT NULL" .
						" AND photo_url != 'null'" .
						" AND photo_url != ''" .
						" ORDER BY timestamp DESC" .
						" LIMIT 1";
			$res = mysqli_query($db, $query);
			if($res)
				while($row = mysqli_fetch_assoc($res))
					$cards[$key]['photo_url'] = $row['photo_url'];
		}
	}

	// Check if each card is bookmarked by the user
	$query = 	"SELECT * FROM bookmarks " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND card_active = 1" .
				" AND active = 1";
	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res))
		foreach($cards as $key => $card)
			if($card['id'] == $row['card_id']){
				$cards[$key]['followers'][] = $row['user_id'];
				if($row['user_id'] == $user_id && !$tissueTesting)
					$cards[$key]['saved'] = true;
			}

	// Check if each card is liked by the user
	$query = 	"SELECT * FROM likes " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND card_active = 1" .
				" AND active = 1";
	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res))
		foreach($cards as $key => $card)
			if($card['id'] == $row['card_id']){
				$cards[$key]['likes'][] = $row['user_id'];
				if($row['user_id'] == $user_id)
					$cards[$key]['liked'] = true;
			
	}

	// Check user's member status
	$query = 	"SELECT * FROM collaborations " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);

	foreach($cards as $key => $card) {
		$cards[$key]['team'] = array();
		$cards[$key]['requests'] = array();
	}

	while($row = mysqli_fetch_assoc($res)){
		foreach($cards as $key => $card){		
			if($card['id'] == $row['card_id']){
				// Get user's membership status
				if($card['author_id'] == $user_id)
					$cards[$key]['member'] = "2";
				else if($row['user_id'] == $user_id)
					$cards[$key]['member'] = $row['accepted'];
			
				// Construct team array
				if($row['user_id'] == $card['author_id'])
				// if($card['author_id'] == $user_id)
					$cards[$key]['team'][] = $row['user_id'];
				else if($row['accepted'] == "1")
					$cards[$key]['team'][] = $row['user_id'];
				else if($row['accepted'] != "0")
					$cards[$key]['requests'][] = $row['user_id'];
			}
		}
	}

	// Get number of comments on this card
	$query = 	"SELECT * FROM card_walls " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND prompt_id IS NULL";
	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res))
		foreach($cards as $key => $card)
			if($card['id'] == $row['card_id'])
				$cards[$key]['comments'][] = $row;

	// No empty fields
	foreach($cards as $key => $card){
		$cards[$key]['distance'] = array_key_exists('distance', $card) ? $card['distance'] : null;
		$cards[$key]['topComment'] = array();
	}

	// Get topComment
	$topCommentUserIds = [];
	$query =	"SELECT * from (" .
					"SELECT card_walls.*, (" .
						"SELECT SUM(active) as score FROM wall_post_likes WHERE post_id = card_walls.id" .
					") as score FROM card_walls ORDER BY score desc" .
				") AS x WHERE card_id IN (".implode($card_ids,", ") . ") GROUP BY card_id";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res)){
		foreach($cards as $key => $card){
			if($card['id'] == $row['card_id']){
				$cards[$key]['topComment'] = $row;
				$topCommentUserIds[] = $row['user_id'];
			}
		}
	}
	// Get topComment likes
 	$query =	"SELECT * FROM wall_post_likes" .
 				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
 	while($like = mysqli_fetch_assoc($res))
 		foreach($cards as $key => $card)
 			if(array_key_exists('id', $card['topComment']) && $card['topComment']['id'] == $like['post_id']){
 				if($tissueTesting){
 					if ($like['active'] == 1)
 						$cards[$key]['topComment']['likes'][] = '-99';
 					else if ($like['active'] == -1)
 						$cards[$key]['topComment']['dislikes'][] = '-99';
 				}
 				else {
	 				if ($like['active'] == 1)
	 					$cards[$key]['topComment']['likes'][] = $like['user_id'];
	 				else if ($like['active'] == -1)
	 					$cards[$key]['topComment']['dislikes'][] = $like['user_id'];
	 			}
 			}
	// Get topComment user info
	$query = 	"SELECT user_id, fir_name, las_name, photo_url FROM users " .
				" WHERE user_id IN ( " . implode($topCommentUserIds, ", ") . " )";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		foreach($cards as $key => $card)
			if(array_key_exists('user_id', $card['topComment']) && $card['topComment']['user_id'] == $row['user_id'])
				$cards[$key]['topComment']['user'] = $row;

	// Reformat keys
	foreach($cards as $key => $card){
		$cards[$key] = (object)array(
			"id"		=> 	$card['id'],
			"founder_id"=> 	$card['author_id'],
			"industry"	=> 	0,
			"pitch"		=>	$card['idea'],
			"distance"	=>	$card['distance'],
			"comments"	=>	$card['comments'],
			"topComment"=>	$card['topComment'],
			"following"	=>	$card['saved'],
		);
	}

 	if($res) mysqli_free_result($res);
 	mysqli_close($db);
 	exit( json_encode($cards, JSON_PRETTY_PRINT) );
?>