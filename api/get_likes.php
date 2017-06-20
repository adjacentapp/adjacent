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
 	$query =	"SELECT * FROM likes " .
 				"WHERE card_id = " . $card_id;
 	$res = mysqli_query($db, $query);

 	$followers = array();

 	if(!$res) exit(json_encode($followers));

 	// Create JSON from database results
	$user_ids = [];
	while($row = mysqli_fetch_assoc($res)){
		$followers[] = $row;
		$user_ids[] = $row['user_id'];
	}

	// Get names for each user
	if($names){
		$query =	"SELECT * FROM users " .
					"WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
		$res = mysqli_query($db, $query);

		while($row = mysqli_fetch_assoc($res))
			foreach($followers as $key => $f)
				if($f['user_id'] == $row['user_id']){
					$followers[$key]['fir_name'] = $row['fir_name'];
					$followers[$key]['las_name'] = $row['las_name'];
					$followers[$key]['photo_url'] = $row['photo_url'];
				}
	}

	// Close connection and ID
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($followers));

?>