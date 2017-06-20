<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = mysqli_real_escape_string($db, $data->card_id);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$new_entry = mysqli_real_escape_string($db, $data->new_entry);
	
	$like_id = null;
	$query = 	"SELECT * FROM likes " .
				"WHERE user_id = " . $user_id .
				" AND card_id = " . $card_id;
	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
	 	$like_id = $row['id'];

	 if(!$new_entry && $like_id){
	 	echo 'deactivate like';
	  	$query =	"UPDATE likes SET active = 0 WHERE id = " . $like_id;
	  	$res = mysqli_query($db, $query);
	 }
	 else {
	 	if($like_id){
	 		echo 'reactive like';
	 		$query =	"UPDATE likes SET active = 1 WHERE id = " . $like_id;
	 		$res = mysqli_query($db, $query);	
	 	}
	 	else {
			echo 'create like';
			$query =	"INSERT INTO likes " .
						"(card_id, user_id) " .
						"VALUES (" .
							$card_id . ", " .
							$user_id .
						")";
			$res = mysqli_query($db, $query);
		}
	}

 	// Update cards table
	if(!$like_id){
		$query =	"UPDATE cards " .
					"SET yes_count = yes_count + 1 ".
					"WHERE id = " . $card_id;
	}
	else if(!$new_entry && $like_id){
 		$query =	"UPDATE cards " .
 				"SET yes_count = yes_count - 1 ".
 				"WHERE id = " . $card_id;
	 }
	 $res = mysqli_query($db, $query);

 	// Close connection
	// if($res) mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>