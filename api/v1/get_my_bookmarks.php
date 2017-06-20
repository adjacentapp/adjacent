<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	$user_id = isset($_GET['user_id']) ? mysqli_real_escape_string($db, $_GET['user_id']) : 0;

	// Get id's of collaborations
 	$query =	"SELECT * FROM bookmarks" .
 				" WHERE user_id = " . $user_id .
 				" AND card_active = 1" .
 				" AND active = 1";
 	$res = mysqli_query($db, $query);

	$collab_card_ids = array();
	while($row = mysqli_fetch_assoc($res))
		$collab_card_ids[] = $row['card_id'];

	$cards = array();
	if(count($collab_card_ids)){

	 	$query =	"SELECT * FROM cards" .
	 				" WHERE id IN ( '" . implode($collab_card_ids, "', '") . "' )" .
	 				" AND active = 1" .
	 				" ORDER BY update_time DESC";
	 	$res = mysqli_query($db, $query);

	 	// Create JSON from database results
		$cards = array();
		while($row = mysqli_fetch_assoc($res)){
			$row['team'] = array();
			$row['followers'] = array();
			$row['likes'] = array();
			$row['comments'] = array();
			$cards[] = $row;
		}


		// Get collaboration teams
	 	$query =	"SELECT * FROM collaborations " .
	 				" WHERE card_id IN ( '" . implode($collab_card_ids, "', '") . "' )" .
	 				" AND accepted = 1" .
	 				" OR role = 'Owner'" .
	 				" ORDER BY time ASC";
	 	$res = mysqli_query($db, $query);
	 	// Assign members to card
	 	while($member = mysqli_fetch_assoc($res))
	 		foreach($cards as $key => $c)
	 			if($c['id'] == $member['card_id']){
	 				$cards[$key]['team'][] = $member;
	 				// if($member['user_id'] == $user_id)
	 				// 	$cards[$key]['member'] = $member['accepted'];
	 			}

	 	// Get bookmarks
		$query = 	"SELECT * FROM bookmarks " .
					" WHERE card_id IN ( " . implode($collab_card_ids, ", ") . " )" .
					" AND active = 1";
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			foreach($cards as $key => $card)
				if($card['id'] == $row['card_id']){
					$cards[$key]['followers'][] = $row['user_id'];
					// if($row['user_id'] == $user_id)
					// 	$cards[$key]['saved'] = true;
				}

	 	// Get likes
		$query = 	"SELECT * FROM likes " .
					" WHERE card_id IN ( " . implode($collab_card_ids, ", ") . " )" .
					" AND card_active = 1" .
					" AND active = 1";
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			foreach($cards as $key => $card)
				if($card['id'] == $row['card_id']){
					$cards[$key]['likes'][] = $row['user_id'];
					// if($row['user_id'] == $user_id)
					// 	$cards[$key]['liked'] = true;
				}

		// Get comments
		$query = 	"SELECT * FROM card_walls " .
					" WHERE card_id IN ( " . implode($collab_card_ids, ", ") . " )" .
					" AND prompt_id IS NULL";
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res))
			foreach($cards as $key => $card)
				if($card['id'] == $row['card_id'])
					$cards[$key]['comments'][] = $row['id'];
	}
	
	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($cards));

?>