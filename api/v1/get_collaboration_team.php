<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	if( isset($_GET['card_id']) ) {
		$card_id = $_GET['card_id'];
	} else {
		exit('no card_id');
	}

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM collaborations " .
 				"WHERE card_id = " . $card_id;

 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$team = array();
	$member_ids = array();
	$roles = array();

	while($row = mysqli_fetch_assoc($res)){
		$team[] = $row;
		$member_ids[] = $row['user_id'];
		$roles[] = $row['role'];
	}

	// Get card object for each collaboration
	// $query = 	"SELECT * FROM cards " .
	// 			" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	// $res = mysqli_query($db, $query);

	// while($card = mysqli_fetch_assoc($res))
	// 	foreach($collabs as $key => $c)
	// 		if($c['card_id'] == $card['id'])
	// 			$collabs[$key]['card'] = $card;

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);


	exit(json_encode($team));

?>