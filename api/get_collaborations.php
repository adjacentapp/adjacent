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



	if( isset($_GET['user_id']) ) {
		$user_id = $_GET['user_id'];
	} else {
		exit('no user_id');
	}
	$card_id = isset($_GET['card_id']) ? $_GET['card_id'] : 0;

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM collaborations " .
 				"WHERE user_id = " . $user_id;
	 if($card_id != 0)
	 	$query .= " AND card_id = " . $card_id;
	 else
	 	$query .= " AND accepted = 1";

 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$collabs = array();
	$card_ids = array();

	while($row = mysqli_fetch_assoc($res)){
		$row['card'] = [];
		$row['team'] = [];
		$row['messages'] = [];
		$row['posts'] = [];
		$collabs[] = $row;
		$card_ids[] = $row['card_id'];
	}

	// Get card object for each collaboration
	$query = 	"SELECT * FROM cards " .
				" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);

	// info for unaccepted collab requests probably shouldn't go past this point for privacy reasons

	while($card = mysqli_fetch_assoc($res))
		foreach($collabs as $key => $c)
			if($c['card_id'] == $card['id'])
				$collabs[$key]['card'] = $card;



	// Get team for each collaboration
	$query = 	"SELECT * FROM collaborations " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )";
	$res = mysqli_query($db, $query);

	while($member = mysqli_fetch_assoc($res))
		foreach($collabs as $key => $c)
			if($c['card_id'] == $member['card_id'])
				if($member['accepted'] == 1)
					$collabs[$key]['team'][] = $member;



	// Get messages for each collaboration
	$query = 	"SELECT * FROM messages " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" ORDER BY time DESC";
	$res = mysqli_query($db, $query);

	while($message = mysqli_fetch_assoc($res))
		foreach($collabs as $key => $c)
			if($c['card_id'] == $message['card_id'])
				$collabs[$key]['messages'][] = $message;



	// Get posts for each collaboration card
	$query = 	"SELECT * FROM card_walls " .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" ORDER BY timestamp ASC";
	$res = mysqli_query($db, $query);

	while($post = mysqli_fetch_assoc($res))
		foreach($collabs as $key => $c)
			if($c['card_id'] == $post['card_id'])
				$collabs[$key]['posts'][] = $post;




	// Sort conversations by newst first
	usort( $collabs, 'ReverseChronological');

	// Get messages for specified collab
	if($card_id != 0){

	 	$query =	"SELECT * FROM messages WHERE card_id=" . $card_id;
		$res = mysqli_query($db, $query);

		$collabs[0]['messages'] = array();
		while($row = mysqli_fetch_assoc($res)) {
			$collabs[0]['messages'][] = $row;
		}

		// Sort messages by oldest first
		usort( $collabs[0]['messages'], 'Chronological');

		// push changes to orginal array
		// $conversations[$key] = $con;
	}

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);


	exit(json_encode($collabs));

?>