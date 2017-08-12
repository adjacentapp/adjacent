<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	require_once('haversine_formula.php');

	$db = connect_db();

	// Check for arguments
	$card_id = isset($_GET['card_id']) ? mysqli_real_escape_string($db, $_GET['card_id']) : 0;
	$user_id = isset($_GET['user_id']) ? mysqli_real_escape_string($db, $_GET['user_id']) : 0;
	$order_by = isset($_GET['order_by']) ? mysqli_real_escape_string($db, $_GET['order_by']) : false;
	$types = isset($_GET['types']) ? mysqli_real_escape_string($db, $_GET['types']) : '1,2,3';

	$limit = isset($_GET['limit']) ? mysqli_real_escape_string($db, $_GET['limit']) : '10';
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

	$query =		"SELECT * FROM cards WHERE active = 1 OR active = -1" .
					" AND type IN (" . $types . ")";
	$res = 			mysqli_query($db, $query);
	$card_max = 	mysqli_num_rows($res);

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

		$query =	"SELECT * FROM cards" .
					" WHERE type IN (" . $types . ")" .
					" AND active = 1 OR active = -1";

		if($order_by=='recent' || $order_by=='special')
			$query .=	" ORDER BY create_time DESC";
		else if($order_by=='active')
			$query .=	" ORDER BY update_time DESC";
		else if($order_by=='popular')
			$query .=	" ORDER BY yes_count DESC";
		else
			$query .=	" ORDER BY create_time DESC";
		$query .=		" LIMIT " . $step;
		$query .=		" OFFSET " . $offset;

		$res = mysqli_query($db, $query);
		if(!$res) exit(mysqli_error());
 		$ordered_cards = [];

	 	if($order_by=='special'){
	 		$avg_c_age = 0;
	 		$avg_u_age = 0;
	 		$avg_likes = 0;
	 		$card_count = 0;
		 	while($row = mysqli_fetch_assoc($res)) {
		 		$card_count++;
		 		// Get age in days
		 		$row['creation_age'] = floor((time() - strtotime($row['create_time']))/(60*60*24));
		 		$row['update_age'] = floor((time() - strtotime($row['update_time']))/(60*60*24));
		 		// Increment sums
		 		$avg_c_age += $row['creation_age'];
		 		$avg_u_age += $row['update_age'];
		 		$avg_likes += $row['yes_count'];
		 		$ordered_cards[] = $row;
		 	}
		 	
		 	if($card_count){
		 		// Calculate averages
			 	$avg_c_age = $avg_c_age/$card_count;
			 	$avg_u_age = $avg_u_age/$card_count;
			 	$avg_likes = $avg_likes/$card_count;

			 	// Calculate final scores, balancing each metric
			 	foreach($ordered_cards as $index => $row){
			 		$c_score = (($avg_c_age - $row['creation_age']) / ($avg_c_age || 1)) * 100;
			 		$u_score = (($avg_u_age - $row['update_age']) / ($avg_u_age || 1)) * 100;
			 		$l_score = (($avg_likes - $row['yes_count']) / ($avg_likes || 1)) * 100;
			 		$ordered_cards[$index]['score_newness'] = $c_score;
			 		$ordered_cards[$index]['score_activity'] = $u_score;
			 		$ordered_cards[$index]['score_likes'] = $l_score;
			 		$ordered_cards[$index]['score'] = $c_score + $u_score + $l_score;
			 	}

			 	// Order cards by score
			 	usort( $ordered_cards, 'compare');
			 }
		}
		else
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
				if($row['user_id'] == $user_id)
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
				$cards[$key]['comments'][] = $row['id'];

	if(!$card_id){
		$new_offset = $new_offset > (int)$card_max ? false : $new_offset;
		array_unshift($cards,  (object)array(
			"new_offset" => $new_offset,
			"data" => (object)array(
				"new_offset" => $new_offset,
				"new_sub_offset" => $new_sub_offset,
				"card_ids" => $card_ids
			), 
			"idea" => 'Thank you for using Adjacent. Please update to the most recent version to remove this message.')
		);
	}
	
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit( json_encode($cards, JSON_PRETTY_PRINT) );
?>