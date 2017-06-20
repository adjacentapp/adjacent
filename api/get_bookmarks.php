<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	if( isset($_GET['user_id']) ) {
		$user_id = $_GET['user_id'];
	} else {
		exit('no user_id');
	}
	$card_id = isset($_GET['card_id']) ? $_GET['card_id'] : 0;

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM bookmarks " .
 				"WHERE user_id = " . $user_id .
 				" AND card_active = 1" .
 				" AND active = 1";
	 if($card_id != 0)
	 	$query .= " AND card_id = " . $card_id;

 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$bookmarks = array();
	$bm_ids = array();

	while($row = mysqli_fetch_assoc($res)){
		$bookmarks[] = $row;
		$bm_ids[] = $row['card_id'];
	}
	
	// Get idea text for each bookmarked card
	$query = 	"SELECT * FROM cards " .
				" WHERE id IN ( " . implode($bm_ids, ", ") . " )" .
				" AND active = 1";
	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res))
		foreach($bookmarks as $key => $bm)
			if($bm['card_id'] == $row['id'])
				$bookmarks[$key]['card'] = $row;

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);


	exit(json_encode($bookmarks));

?>