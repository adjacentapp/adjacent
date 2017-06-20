<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();
	
	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$card_id = $data->card_id ? mysqli_real_escape_string($db, $data->card_id) : 'null';
	@$post_id = $data->post_id ? mysqli_real_escape_string($db, $data->post_id) : 'null';
	@$other_id = $data->other_id ? mysqli_real_escape_string($db, $data->other_id) : 'null';

	// Check for existing like
	$query =	"SELECT * FROM silencers " .
				"WHERE user_id = " . $user_id;
	if($card_id !== 'null')
		$query .=	" AND card_id = " . $card_id;
	else if ($post_id !== 'null')
		$query .=	" AND post_id = " . $post_id;
	else if ($other_id !== 'null')
		$query .=	" AND other_id = " . $other_id;
	$res = mysqli_query($db, $query);
	
	if(mysqli_num_rows($res)==0){
		$query =	"INSERT INTO silencers " .
					"(user_id, card_id, post_id, other_id) " .
					"VALUES (" .
						$user_id . ", " .
						$card_id . ", " .
						$post_id . ", " .
						$other_id .
					")";
		$res = mysqli_query($db, $query);
		$silenced = true;
	}
	else {
		$query =	"DELETE FROM silencers " .
					"WHERE user_id = " . $user_id;
		if($card_id !== 'null')
			$query .=	" AND card_id = " . $card_id;
		else if ($post_id !== 'null')
			$query .=	" AND post_id = " . $post_id;
		else if ($other_id !== 'null')
			$query .=	" AND other_id = " . $other_id;
		$res = mysqli_query($db, $query);
		$silenced = false;
	}

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit($silenced)
?>