<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	function Chronological($a, $b){
		if($a['time'] == $b['time']) return 0;
		return ($a['time'] < $b['time']) ? -1 : 1;
	}
	function ReverseChronological($a, $b){
		if($a['time'] == $b['time']) return 0;
		return ($a['time'] < $b['time']) ? 1 : -1;
	}


	$db = connect_db();

	if( isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('no user_id');

	// Get user's collaborations
 	$query =	"SELECT * FROM collaborations" .
 				" WHERE user_id = " . $user_id .
 				" AND (accepted = 1 OR accepted IS NULL)";
 	$res = mysqli_query($db, $query);

 	// Make array of card_ids
	$card_ids = array();
	$pending_ids = array();
	while($row = mysqli_fetch_assoc($res)){
		$card_ids[] = $row['card_id'];
		// Array of pending id's to change last_message
		if($row['accepted'] != 1)
			$pending_ids[] = $row['card_id'];
	}

	// Get cards ordered by update time
 	$query =	"SELECT * FROM cards" .
 				" WHERE id IN ( '" . implode($card_ids, "', '") . "' )" .
 				" ORDER BY update_time DESC";
 	$res = mysqli_query($db, $query);

 	// Make array of cards
	$cards = array();
	while($row = mysqli_fetch_assoc($res))
		$cards[] = $row;

	// Get most recent unread message for each card

	// Get most recent message from each group conversation
 	$query = 	"SELECT m1.* " .
				"FROM messages AS m1 LEFT JOIN messages AS m2 " .
				"ON (m1.card_id = m2.card_id AND m1.time < m2.time) " .
				"WHERE m2.time IS NULL " .
				"AND m1.card_id IN ( " . implode($card_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);

 	// Add each message to appropriate conversation
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($cards as $key => $c)
 			if($c['id'] == $msg['card_id']){
 				if(in_array($c['id'], $pending_ids))
 					$msg['text'] = 'Your request is pending';
 				$cards[$key]['last_message'] = $msg;
 			}
	
	// // Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($cards));









 	
	
	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($cards));




 	$query =	"SELECT * FROM conversations " .
 				"WHERE other_id = " . $user_id;
 	if( $card_id == 0 )
 		$query .= " OR author_id = " . $user_id;
 	else
 		$query .= " AND card_id = " . $card_id;

 	$res = mysqli_query($db, $query);
 	if(!$res)
 		exit(mysqli_error());

 	// Create JSON from database results
	// $conversations = array();
	// while($row = mysqli_fetch_assoc($res)) {
	// 	$conversations[] = $row;
	// }

 	// Create JSON from database results
	$conversations = array();
	$card_ids = array();

	while($row = mysqli_fetch_assoc($res)){
		$conversations[] = $row;
		$card_ids[] = $row['card_id'];
	}

	// Get idea text for each conversation
	$query = 	"SELECT * FROM cards " .
				" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res))
		foreach($conversations as $key => $c)
			if($c['card_id'] == $row['id'])
				$conversations[$key]['card'] = $row;


	// Sort conversations by newst first
	usort( $conversations, 'ReverseChronological');


	// // Get messages for each conversation
	foreach ($conversations as $key => $con) {
		$con['messages'] = array();

		$query =	"SELECT * FROM messages WHERE conversation_id = " . $con['conversation_id'];
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res)) {
			$con['messages'][] = $row;
		}

		// Sort messages by oldest first
		usort( $con['messages'], 'Chronological');

		// push changes to orginal array
		$conversations[$key] = $con;
	}

	// var_dump($conversations);
	
	// // Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($conversations));

?>