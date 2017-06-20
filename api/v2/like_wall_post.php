<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');
	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$card_id = mysqli_real_escape_string($db, $data->card_id);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$post_id = mysqli_real_escape_string($db, $data->post_id);
	@$score = mysqli_real_escape_string($db, $data->score);

	$msg = '';

	$like_id = null;
	$query = 	"SELECT * FROM wall_post_likes" .
				" WHERE user_id = " . $user_id .
				" AND post_id = " . $post_id;
	$res = mysqli_query($db, $query);
	if($res)
 		while($row = mysqli_fetch_assoc($res))
	 		$like_id = $row['id'];
	
	if($like_id){
		$query =	"UPDATE wall_post_likes SET active = {$score}  WHERE id = {$like_id}";
		$res = mysqli_query($db, $query);
		$msg = "update {$score}";
	}
	else {
		$query =	"INSERT INTO wall_post_likes " .
					"(card_id, user_id, post_id, active) " .
					"VALUES (" .
						$card_id . ", " .
						$user_id . ", " .
						$post_id . ", " .
						$score .
					")";
		$res = mysqli_query($db, $query);
		$msg = 'create {$score}';
	}

 	// Close connection
	if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode( (object)array("message" => $msg) ));
?>