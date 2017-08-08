<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	$limit = 10;

	if( isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('no user_id');
	@$other_id = isset($_GET['other_id']) ? mysqli_real_escape_string($db, $_GET['other_id']) : null;
	@$card_id = isset($_GET['card_id']) ? mysqli_real_escape_string($db, $_GET['card_id']) : null;

	$card_ids = array();

	if($card_id) $card_ids[] = $card_id;

	if(!$other_id && !$card_id)
	 	$query =	"SELECT conversations.*, update_time as timestamp FROM conversations " .
	 				"WHERE author_id = " . $user_id .
	 				" OR other_id = " . $user_id .
	 				" ORDER BY update_time DESC";
	 else {
	 	$query =	"SELECT conversations.*, update_time as timestamp FROM conversations " .
	 				"WHERE ( " .
	 					"(author_id = {$user_id} AND other_id = {$other_id}) " .
	 					"OR (other_id = {$user_id} AND author_id = {$other_id}) " .
	 				") ";
	 	$query .=	$card_id ? "AND card_id = {$card_id} " : "AND card_id = 0 ";
	 	$query .=	"ORDER BY update_time DESC";
	 }
 	$res = mysqli_query($db, $query);

 	// Make array of all conversations
 	$conversations = array();
 	$conversation_ids = array();
 	$other_ids = array();
 	while($row = mysqli_fetch_assoc($res)){
 		$row['messages'] = array();
		$conversations[] = $row;
		
		if(!$card_id)
			$other_ids[] = $row['author_id'] == $user_id ? $row['other_id'] : $row['author_id'];
 	}

 	$query = "";
 	foreach($conversations as $key => $c)
 		$query .= "(SELECT * FROM messages WHERE conversation_id = {$c["conversation_id"]} ORDER BY time DESC LIMIT {$limit}) UNION ALL ";
	if(strlen($query)){
		$query = substr($query, 0, -11);
 		$res = mysqli_query($db, $query);
 	}

 	// Add each message to appropriate conversation
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($conversations as $key => $c)
 			if($c['conversation_id'] == $msg['conversation_id'])
 				$conversations[$key]['messages'][] = $msg;

 	// Get messages ['user']
	$other_ids[] = $user_id;
 	$query = 	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users " .
 				"WHERE user_id IN ( " . implode($other_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);
 	
 	while($row = mysqli_fetch_assoc($res))
 		foreach($conversations as $key => $c) {
 			// get message's ['user']
			foreach($c['messages'] as $ley => $m)
				if($m['user_id'] == $row['id']){
					$conversations[$key]['messages'][$ley]['user'] = $row;
					
					if(!$card_id && $c['card_id']=="0")
						$conversations[$key]['other'] = (object)array("fir_name" => "Anonymous", "las_name" => "");
					else if ($c['card_id']!="0")
						$card_ids[] = $c['card_id'];

					if($row['id'] != $user_id) // add conversation's ['other'] ONLY IF they have responded
						if(!$card_id) // redundant sanity check
							$conversations[$key]['other'] = $row;
				}
		}

	if(count($card_ids)){
		// get card instead of profile
		$query =	"SELECT id, idea as pitch, author_id as founder_id, industry_string as industry, challenge, background as who, stage, challenge_details, create_time as created_at, update_time as updated_at, anonymous " .
					"FROM cards WHERE id IN ( " . implode($card_ids, ", ") . " )";
		$res = mysqli_query($db, $query);
		$cards = array();
		while($row = mysqli_fetch_assoc($res))
			$cards[] = $row;

		// get followers
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

		// get comments
		$query = 	"SELECT * FROM card_walls " .
					" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
					" AND prompt_id IS NULL";
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res))
			foreach($cards as $key => $card)
				if($card['id'] == $row['card_id'])
					$cards[$key]['comments'][] = $row;



		// assign convo's and message's ['card'] instead of ['user']
 		foreach($cards as $key => $card) {
 			foreach($conversations as $ley => $convo) {
 				if($convo['card_id'] == $card['id']){
	 				$conversations[$ley]['card'] = $card;
 				
					foreach($convo['messages'] as $mey => $msg)
						if($m['user_id'] == $card['founder_id'])
							$conversations[$ley]['messages'][$mey]['card'] = $card;
				}
			}
		}
		


	}

 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($conversations, JSON_PRETTY_PRINT));

?>