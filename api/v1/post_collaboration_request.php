<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$card_id = mysqli_real_escape_string($db, $data->card_id);

	$query =	"SELECT * FROM collaborations " .
				"WHERE user_id = " . $user_id .
				" AND card_id = " . $card_id .
				" AND accepted = 0";
	$res = mysqli_query($db, $query);
	if (mysqli_num_rows($res) != 0){
		// reopen existing request
		$query =	"UPDATE collaborations " .
					"SET accepted = NULL" .
					" WHERE user_id = " . $user_id;
		$res = mysqli_query($db, $query);
	}
	else {
		// create new request
		$query =	"INSERT INTO collaborations " .
					"(card_id, user_id) " .
					"VALUES (" .
						$card_id . ", " .
						$user_id .
					")";
		$res = mysqli_query($db, $query);
	}


	// this should be handled by the post_message_group reciept, right?
 // 	// Post receipts
	// $users_to_notify = array();
	// // team member id's
	// $query =	"SELECT * FROM collaborations" .
 // 				" WHERE card_id = " . $card_id .
 // 				" AND accepted = 1";
	// $res = mysqli_query($db, $query);
 // 	while($row = mysqli_fetch_assoc($res))
 // 		$users_to_notify[] = $row['user_id'];

 // 	// Create receipt for each user
 // 	for($i=0; $i<count($users_to_notify); $i++){
 // 		if($user_to_notify[$i] != $user_id){
	//  		$query =	"INSERT INTO message_receipts " .
	//  					"(user_id, card_id) " .
	//  					"VALUES (" .
	//  						$users_to_notify[$i] .
	//  					", " .
	//  						$card_id .
	//  					")";
	//  		$res = mysqli_query($db, $query);
	//  	}
 // 	}

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();








	// what's this ancient artifact?
 	// Update cards table
 	$update_value = ($like_bool == 0) ? 'no_count' : 'yes_count';

 	if( $removeSwipe == true ){
	 	$query =	"UPDATE cards " .
	 				"SET " . $update_value . " = " . $update_value . " - 1 ".
	 				"WHERE id = " . $card_id;
	 }
	 else {
	 	 	$query =	"UPDATE cards " .
	 	 				"SET " . $update_value . " = " . $update_value . " + 1 ".
	 	 				"WHERE id = " . $card_id;
	 	 }
	 $res = mysqli_query($db, $query);

 	// Close connection
	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>