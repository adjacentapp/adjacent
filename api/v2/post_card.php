<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$founder_id = $data->founder_id ? mysqli_real_escape_string($db, $data->founder_id) : null;
	@$pitch = $data->pitch ? strip_tags( mysqli_real_escape_string($db, $data->pitch) ) : null;
	@$photo_url = $data->photo_url ? strip_tags( mysqli_real_escape_string($db, $data->photo_url) ) : null;
	@$who = $data->who ? mysqli_real_escape_string($db, $data->who) : null;
	@$stage = $data->stage ? mysqli_real_escape_string($db, $data->stage) : null;
	@$lat = $data->lat ? mysqli_real_escape_string($db, $data->lat) : null;
	@$lon = $data->lon ? mysqli_real_escape_string($db, $data->lon) : null;
	@$challenge = $data->challenge ? mysqli_real_escape_string($db, $data->challenge) : null;
	@$challenge_details = $data->challenge_details ? mysqli_real_escape_string($db, $data->challenge_details) : null;
	@$anonymous = $data->anonymous ? mysqli_real_escape_string($db, $data->anonymous) : 0;
	
	@$industry = $data->industry ? strip_tags( mysqli_real_escape_string($db, $data->industry) ) : null;
	@$networks = $data->networks ? mysqli_real_escape_string($db, $data->lon) : null;
	

 	$query =	"INSERT INTO cards " .
 				"(author_id, idea, industry_string, photo_url, background, stage, challenge, challenge_details, anonymous, message_time, lat, lon) " .
 				"VALUES (" .
 					$founder_id .
 				", " .
 					"'" . $pitch . "'" .
				", " .
 					"'" . $industry . "'" .
 				", " .
 					($photo_url ? "'{$photo_url}'" : "null") . 
 				", " .
 					($who ? "'{$who}'" : "null") . 
 				", " .
 					($stage ? "{$stage}" : "null") . 
				", " .
 					($challenge ? "'{$challenge}'" : "null") . 
 				", " .
 					($challenge_details ? "'{$challenge_details}'" : "null") . 
 				", " .
 					$anonymous
 				", " .
 					"now()" .
 				", " .
 					($lat ? "'{$lat}'" : "null") . 
 				", " .
 					($lon ? "'{$lon}'" : "null") . 
 				")";
 	$res = mysqli_query($db, $query);
 	$new_card_id = mysqli_insert_id($db);

	$query =	"INSERT INTO collaborations " .
				"(user_id, card_id, accepted, status) " .
				"VALUES (" .
					$founder_id .
				", " .
					$new_card_id .
				", " .
					"1" .
				", " .
					"'owner'" .
				")";
	$res = mysqli_query($db, $query);


	// $card;
	// $query =	"SELECT * FROM cards WHERE id = {$new_card_id}";
	// $res = mysqli_query($db, $query);
	// while($row = mysqli_fetch_assoc($res))
	// 	$card = $row;
	$card = (object)array("id" => $new_card_id, "founder_id" => $founder_id, "pitch" => $pitch, "photo_url" => $photo_url, "who" => $who, "stage" => $stage, "challenge" => $challenge, "challenge_details" => $challenge_details, "anonymous" => $anonymous, "industry" => $industry, "networks" => $networks, "comments" => []);

	// if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode($card, JSON_PRETTY_PRINT));
?>