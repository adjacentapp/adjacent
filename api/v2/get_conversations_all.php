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

	// Get user's collaborations
 	$query =	"SELECT * FROM collaborations" .
 				" WHERE user_id = " . $user_id; // .
 				// " AND (accepted = 1 OR accepted IS NULL)";
 	$res = mysqli_query($db, $query);

 	// Make array of card_ids
	$card_ids = array();
	$pending_ids = array();
	$denied_ids = array();
	while($row = mysqli_fetch_assoc($res)){
		$card_ids[] = $row['card_id'];
		// Array of pending id's to change last_message
		if($row['accepted'] === 0 || $row['accepted'] == '0')
			$denied_ids[] = $row['card_id'];
		else if($row['accepted'] != 1)
			$pending_ids[] = $row['card_id'];
	}

	// Get cards ordered by update time
 	$query =	"SELECT * FROM cards" .
 				" WHERE id IN ( '" . implode($card_ids, "', '") . "' )" .
 				" AND active = 1" .
 				" ORDER BY message_time DESC";
 	$res = mysqli_query($db, $query);

 	// Make array of cards
	$group_convos = array();
	while($row = mysqli_fetch_assoc($res)){
		$row['title'] = $row['idea'];
		$row['team'] = array();
		$group_convos[] = $row;
	}

	// Get team 3 members
 	$query =	"SELECT * FROM collaborations " .
 				"WHERE card_id IN ( '" . implode($card_ids, "', '") . "' )" .
 				" ORDER BY time ASC";
	 $res = mysqli_query($db, $query);

 	while($member = mysqli_fetch_assoc($res))
 		foreach($group_convos as $key => $c)
 			if($c['id'] == $member['card_id'])
 				$group_convos[$key]['team'][] = $member['user_id'];


	// Get most recent message from each group conversation
 	$query = 	"SELECT m1.* " .
				"FROM messages AS m1 LEFT JOIN messages AS m2 " .
				"ON (m1.card_id = m2.card_id AND m1.time < m2.time) " .
				"WHERE m2.time IS NULL " .
				"AND m1.card_id IN ( " . implode($card_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);

 	// Add each message to appropriate conversation
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($group_convos as $key => $c)
 			if($c['id'] == $msg['card_id']){
 				if(in_array($c['id'], $denied_ids))
 					$msg['text'] = 'Your request has been closed';
 				else if(in_array($c['id'], $pending_ids))
 					$msg['text'] = 'Your request is pending';
 				$group_convos[$key]['last_message'] = $msg;
 			}

 	// remove cards without messages
	foreach($group_convos as $key => $c)
		if(!array_key_exists('last_message',$c))
			unset($group_convos[$key]);
		else if(!$c['last_message'])
			unset($group_convos[$key]);





 	// Get direct message conversations now
	
	// Make array of blocked users
	$blocked_users = array();
	$query =	"SELECT * FROM silencers ";// .
				// "WHERE user_id = " . $user_id .
				// " AND other_id = " . $other_id;
	while($row = mysqli_fetch_assoc($res))
		$blocked_users[] = $row['other_id'];

	// Query database
 	$query =	"SELECT * FROM conversations " .
 				"WHERE author_id = " . $user_id .
 				" OR other_id = " . $user_id .
 				" ORDER BY message_time DESC";
 	$res = mysqli_query($db, $query);

 	// Make array of all conversations
 	$direct_convos = array();
 	$conversation_ids = array();
 	$others_ids = array();
 	while($row = mysqli_fetch_assoc($res)){
 		if(in_array($row['author_id'], $blocked_users) || in_array($row['other_id'], $blocked_users)){
 			// skip blocked users
 		} else {
	 		if($row['author_id'] == $user_id)
	 			$others_ids[] = $row['other_id'];
	 		else if($row['other_id'] == $user_id)
	 			$others_ids[] = $row['author_id'];
	 		$conversation_ids[] = $row['conversation_id'];
			$direct_convos[] = $row;
		}
 	}

	// Get users
 	$query =	"SELECT * FROM users" .
 				" WHERE user_id IN ( '" . implode($others_ids, "', '") . "' )";
 	$res = mysqli_query($db, $query);

 	// Add user names to conversations
 	while($user = mysqli_fetch_assoc($res))
 		foreach($direct_convos as $key => $c)
 			if($c['author_id'] == $user['user_id'] || $c['other_id'] == $user['user_id'])
 				$direct_convos[$key]['title'] = $user['fir_name'] . ' ' . $user['las_name'];

 	// Get most recent message from each conversation
 	$query = 	"SELECT m1.* " .
				"FROM messages AS m1 LEFT JOIN messages AS m2 " .
				"ON (m1.conversation_id = m2.conversation_id AND m1.time < m2.time) " .
				"WHERE m2.time IS NULL " .
				"AND m1.conversation_id IN ( " . implode($conversation_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);

 	// Add each message to appropriate conversation
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($direct_convos as $key => $c)
 			if($c['conversation_id'] == $msg['conversation_id'])
 				$direct_convos[$key]['last_message'] = $msg;
 				
 

 	// Combine group and direct convos by order
 	function Chronological($a, $b){
		if($a['message_time'] == $b['message_time']) return 0;
		return ($a['message_time'] < $b['message_time']) ? -1 : 1;
	}
	function ReverseChronological($a, $b){
		if($a['message_time'] == $b['message_time']) return 0;
		return ($a['message_time'] < $b['message_time']) ? 1 : -1;
	}

 	$all_convos = array_merge($group_convos, $direct_convos);
 	usort( $all_convos, 'ReverseChronological');



	
	// // Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	// exit(json_encode($all_convos));
 	exit(json_encode($all_convos, JSON_PRETTY_PRINT));

?>