<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	if( isset($_GET['card_id']) ) {
		$card_id = mysqli_real_escape_string($db, $_GET['card_id']);
	} else {
		exit('no card_id');
	}
	$names = isset($_GET['names']) ? true : false;

	// Query database
 	$query =	"SELECT * FROM collaborations " .
 				"WHERE card_id = " . $card_id .
 				" AND accepted = 1" .
 				" OR" .
 				" card_id = " . $card_id .
 				" AND role = 'Owner'";
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$team = array();
	$user_ids = [];
	while($row = mysqli_fetch_assoc($res)){
		$team[] = $row;
		$user_ids[] = $row['user_id'];
	}

	// Get names for each user
	if($names){
		$query =	"SELECT * FROM users " .
					"WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res)){
			foreach($team as $key => $member)
				if($member['user_id'] == $row['user_id']){
					$team[$key]['fir_name'] = $row['fir_name'];
					$team[$key]['las_name'] = $row['las_name'];
					$team[$key]['photo_url'] = $row['photo_url'];
				}
		}
	}

	// Close connection and ID
	if($res) mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($team));

?>