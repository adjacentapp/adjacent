<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	function Chronological($a, $b){
		if($a['time'] == $b['time']) return 0;
		return ($a['time'] < $b['time']) ? -1 : 1;
	}
	function ReverseChronological($a, $b){
		if($a['time'] == $b['time']) return 0;
		return ($a['time'] < $b['time']) ? 1 : -1;
	}

	if( isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('no user_id');

 	$query =	"SELECT conversations.*, update_time as timestamp FROM conversations " .
 				"WHERE author_id = " . $user_id .
 				" OR other_id = " . $user_id .
 				" ORDER BY update_time DESC";
 	$res = mysqli_query($db, $query);

 	// Make array of all conversations
 	$conversations = array();
 	$conversation_ids = array();
 	$other_ids = array();
 	while($row = mysqli_fetch_assoc($res)){
 		$conversation_ids[] = $row['conversation_id'];
		$conversations[] = $row;
		$other_ids[] = $row['author_id'] == $user_id ? $row['other_id'] : $row['author_id'];
 	}

 	// Get most recent message from each conversation
 	$query = 	"SELECT m1.*, m1.time as timestamp " .
				"FROM messages AS m1 LEFT JOIN messages AS m2 " .
				"ON (m1.conversation_id = m2.conversation_id AND m1.time < m2.time) " .
				"WHERE m2.time IS NULL " .
				"AND m1.conversation_id IN ( " . implode($conversation_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);

 	// Add each message to appropriate conversation
 	while($msg = mysqli_fetch_assoc($res))
 		foreach($conversations as $key => $c)
 			if($c['conversation_id'] == $msg['conversation_id'])
 				$conversations[$key]['last_message'] = $msg;

 	// Get other user
 	$query = 	"SELECT user_id as id, email, fir_name, las_name, photo_url FROM users " .
 				"WHERE user_id IN ( " . implode($other_ids, ", ") . " )";
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		foreach($conversations as $key => $c)
 			if($c['author_id'] == $row['id'] || $c['other_id'] == $row['id']){
 				$conversations[$key]['last_message']['user'] = $row;
 				$conversations[$key]['other'] = $row;
 			}
	
	// // Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($conversations, JSON_PRETTY_PRINT));

?>