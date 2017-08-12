<?php		
	require_once('../db_connect.php');
	$db = connect_db();

	function get_cards_by_ids($card_ids){
		global $db;

		if(!count($card_ids)) return array();

		$query =	"SELECT " .
						"id, " .
						"author_id as founder_id, " .
						"idea as pitch, " .
						"industry_string as industry, " .
						"background as who, ".
						"challenge, " .
						"challenge_details, " .
						"stage, " .
						"0 as distance, " .
						"create_time as created_at, " .
						"update_time as updated_at " .
					"FROM cards WHERE id IN ( '" . implode($card_ids, "', '") . "' )" .
					" AND active = 1" .
					" ORDER BY FIELD(id, " . implode($card_ids, ", ") . " )";
	 	$res = mysqli_query($db, $query);

		$CARDS = array();
		while($row = mysqli_fetch_assoc($res)){
			$row['followers'] = array();
			$row['following'] = null;
			$row['comments'] = array();
			$CARDS[] = $row;
		}

		return $CARDS;
	}

	function add_bookmark_user_ids($CARDS, $user_id){
		global $db;

		if(!count($CARDS)) return $CARDS;

		$card_ids = array();
		foreach($CARDS as $card)
			$card_ids[] = $card['id'];

		$query = 	"SELECT * FROM bookmarks " .
					" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
					" AND active = 1";
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res))
			foreach($CARDS as $key => $card)
				if($card['id'] == $row['card_id']){
					$CARDS[$key]['followers'][] = $row['user_id'];
					if($row['user_id'] == $user_id)
						$CARDS[$key]['following'] = true;
				}

		return $CARDS;
	}

	function add_comment_user_ids($CARDS, $user_id){
		global $db;

		if(!count($CARDS)) return $CARDS;

		$card_ids = array();
		foreach($CARDS as $card)
			$card_ids[] = $card['id'];

		$query = 	"SELECT * FROM card_walls " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND prompt_id IS NULL";
				// " AND response_to IS NULL"; // dont count responses
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res))
			foreach($CARDS as $key => $card)
				if($card['id'] == $row['card_id'])
					$CARDS[$key]['comments'][] = $row['user_id'];

		return $CARDS;
	}

?>