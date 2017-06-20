<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$author_id = $data->author_id ? mysqli_real_escape_string($db, $data->author_id) : null;
	@$idea = $data->idea ? mysqli_real_escape_string($db, $data->idea) : null;
	@$hum = $data->hum ? mysqli_real_escape_string($db, $data->hum) : null;
	@$status = $data->status ? mysqli_real_escape_string($db, $data->status) : 0;
	@$background = $data->background ? mysqli_real_escape_string($db, $data->background) : null;
	@$recruit = $data->recruit ? mysqli_real_escape_string($db, $data->recruit) : null;
	@$topic = $data->topic ? mysqli_real_escape_string($db, $data->topic) : null;
	@$role = $data->role ? mysqli_real_escape_string($db, $data->role) : null;
	@$prompt = $data->prompt ? mysqli_real_escape_string($db, $data->prompt) : 0;
	@$website_url = $data->website_url ? mysqli_real_escape_string($db, $data->user_id) : null;
	// @$team = $data->team ? mysqli_real_escape_string($db, $data->team) : null;
	$team = null;
	@$type = $data->type ? mysqli_real_escape_string($db, $data->type) : 1;
	@$lat = $data->lat ? mysqli_real_escape_string($db, $data->lat) : null;
	@$lon = $data->lon ? mysqli_real_escape_string($db, $data->lon) : null;

	if($idea == '')
		$idea = $topic;

	// Query database
 	$query =	"INSERT INTO cards " .
 				"(author_id, idea, hum, status, background, recruit, prompt, website_url, type, create_time, message_time, lat, lon) " .
 				"VALUES (" .
 					$author_id .
 				", " .
 					"'" . $idea . "'" .
 				", " .
 					$hum .
 				", " .
 					$status .
 				", " .
 					"'" . $background . "'" .
 				", " .
 					"'" . $recruit . "'" .
 				", " .
 					$prompt .
 				", " .
 					"'" . $website_url . "'" .
 				", " .
 					$type .
 				", " .
 					"now()" .
 				", " .
 					"now()" .
 				", " .
 					"'" . $lat . "'" .
 				", " .
 					"'" . $lon . "'" .
 				")";
 	$res = mysqli_query($db, $query);

 	$new_card_id = mysqli_insert_id($db);
 	echo $new_card_id;

 	if($role){
 		$query =	"INSERT INTO collaborations " .
 					"(user_id, card_id, accepted, role, status) " .
 					"VALUES (" .
 						$author_id .
 						", " .
 						$new_card_id .
 						", " .
 						"1" .
 						", " .
 						"'" . $role . "'," .
 						"'owner'" .
 					")";
 		$res = mysqli_query($db, $query);
 	}

 	if($team){
 		echo 'insdie team';
 		for($i=0; $i<count($team); $i++){
 			echo $team[$i];
	 		$query =	"INSERT INTO collaborations " .
	 					"(user_id, card_id, accepted) " .
	 					"VALUES (" .
	 						$team[$i] .
	 						", " .
	 						$new_card_id .
	 						", " .
	 						"1" .
	 					")";
	 		$res = mysqli_query($db, $query);
	 	}
 	}

 	// Close connection
 	// if($res) mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>