<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = mysqli_real_escape_string($db, $data->card_id);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$prompt_id = mysqli_real_escape_string($db, $data->prompt_id);
	@$new_entry = mysqli_real_escape_string($db, $data->new_entry);
	
	if($new_entry){
		echo 'create prompt like';
		$query =	"INSERT INTO prompt_likes " .
					"(card_id, user_id, prompt_id) " .
					"VALUES (" .
						$card_id . ", " .
						$user_id . ", " .
						$prompt_id .
					")";
		$res = mysqli_query($db, $query);
	}
	else {
		echo 'delete prompt like';
	 	$query =	"DELETE FROM prompt_likes " .
	 				"WHERE user_id = " . $user_id .
	 				" AND card_id = " . $card_id .
	 				" AND prompt_id = " . $prompt_id;
	 	$res = mysqli_query($db, $query);
	}

 	// Close connection
	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>