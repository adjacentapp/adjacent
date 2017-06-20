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
	if( isset($_GET['card_id']) )
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	else
		exit('no card_id');

	$requests = isset($_GET['requests']) ? true: false;

	$query = 	"SELECT * FROM collaborations " .
				" WHERE card_id = ". $card_id .
				" AND user_id = " . $user_id .
				" LIMIT 1";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$collaborator = $row;

	// Query database for non-member
	if(!$collaborator['accepted'] || $collaborator['accepted'] === 0 || $collaborator['accepted'] == '0'){
	 	$query =	"SELECT * FROM messages" .
	 				" WHERE card_id = " . $card_id .
					" AND user_id = " . $user_id .
					" AND response_to = 0" .
					" AND alert = 0";
		$res = mysqli_query($db, $query);

	 	if(!$res) exit(json_encode(array()));

	 	$messages = array();
	 	while($row = mysqli_fetch_assoc($res)){
	 		$row['responses'] = array();
	 		$messages[] = $row;
	 	}

	 	// Query for all responses to initial message
 	 	$query =	"SELECT * FROM messages" .
 	 				" WHERE response_to = " . $messages[0]['id'] .
 					" ORDER BY time ASC";
 		$res = mysqli_query($db, $query);

 	 	while($row = mysqli_fetch_assoc($res)){
 	 		$messages[0]['responses'][] = $row;
 	 	}
	}

	else {
		// Query database for member
		if($requests){
			$request_ids = array();
			$query = 	"SELECT * FROM collaborations " .
						" WHERE card_id = ". $card_id .
						" AND accepted IS NULL";
			$res = mysqli_query($db, $query);
			while($row = mysqli_fetch_assoc($res))
				$request_ids[] = $row['user_id'];
		}


	 	$query =	"SELECT * FROM messages" .
	 					" WHERE card_id = " . $card_id .
						" AND response_to IS NULL" .
						" AND time > '" . $collaborator['accepted_time'] . "'";
						if($requests && !empty($request_ids)) $query .= " AND user_id IN ( " . implode($request_ids, ", ") . " )";
		$query .=	" OR" .
						" card_id = " . $card_id .
						" AND response_to = 0" .
						" AND time > '" . $collaborator['accepted_time'] . "'";
						if($requests && !empty($request_ids)) $query .= " AND user_id IN ( " . implode($request_ids, ", ") . " )";
		$query .=	" OR card_id = " . $card_id . " AND response_to IS NULL AND user_id = " . $user_id;
		$query .=	" OR card_id = " . $card_id . " AND response_to = 0 AND user_id = " . $user_id;
		$query .=	" ORDER BY time ASC";
		$res = mysqli_query($db, $query);

	 	if(!$res) exit(json_encode(array()));

	 	$messages = array();
	 	$m_ids = array();
	 	while($row = mysqli_fetch_assoc($res)){
	 		$row['responses'] = array();
	 		$messages[] = $row;
	 		$m_ids[] = $row['id'];
	 	}

	 	// Query for all responses to initial message
 	 	$query =	"SELECT * FROM messages" .
 	 				" WHERE response_to IN ( " . implode($m_ids, ", ") . " )" .
 					" ORDER BY time ASC";
 		$res = mysqli_query($db, $query);
 		
 	 	while($msg = mysqli_fetch_assoc($res))
 	 		foreach($messages as $key => $m)
 	 			if($m['id'] == $msg['response_to'])
 	 				$messages[$key]['responses'][] = $msg;
 	 					// fancy pants!
	}

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);
	// exit(json_encode($messages));
	exit(json_encode($messages, JSON_PRETTY_PRINT));

?>