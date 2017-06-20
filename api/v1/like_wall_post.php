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
	@$post_id = mysqli_real_escape_string($db, $data->post_id);
	@$new_entry = mysqli_real_escape_string($db, $data->new_entry);
	
	if($new_entry){
		echo 'create wall_post_like';
		$query =	"INSERT INTO wall_post_likes " .
					"(card_id, user_id, post_id) " .
					"VALUES (" .
						$card_id . ", " .
						$user_id . ", " .
						$post_id .
					")";
		$res = mysqli_query($db, $query);
	}
	else {
		echo 'delete wall_post_like';
	 	$query =	"DELETE FROM wall_post_likes " .
	 				"WHERE user_id = " . $user_id .
	 				" AND card_id = " . $card_id .
	 				" AND post_id = " . $post_id;
	 	$res = mysqli_query($db, $query);
	}

 	// Close connection
	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>