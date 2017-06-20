<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$newCard = json_decode($postdata);
	@$card_id = $newCard->id;
	@$author_id = $newCard->author_id;
	@$title = $newCard->title ? $newCard->title : null;
	@$idea = $newCard->idea ? $newCard->idea : null;
	@$photo_url = $newCard->photo_url ? $newCard->photo_url : null;
	@$prompt = $newCard->prompt ? $newCard->prompt : null;
	@$website_url = $newCard->website_url ? $newCard->website_url : null;
	@$type = $newCard->type ? $newCard->type : null;
	@$lat = $newCard->lat ? $newCard->lat : null;
	@$lon = $newCard->lon ? $newCard->lon : null;

	if($idea) $idea = strip_tags($idea);

	// Make array of notifications to send out
	$notifications = array();
	if($newCard->idea_notification)
		$notifications[] = mysqli_real_escape_string($db, $newCard->idea_notification);
	if($newCard->type_notification)
		$notifications[] = mysqli_real_escape_string($db, $newCard->type_notification);
	if($newCard->prompt_notification)
		$notifications[] = mysqli_real_escape_string($db, $newCard->prompt_notification);
	if($newCard->website_notification)
		$notifications[] = mysqli_real_escape_string($db, $newCard->website_notification);

	// Query database
 	$query =	"UPDATE cards SET ";
 		if($title)
 		 			$query .= "title = '" .	mysqli_real_escape_string($db, $title) . "', ";
 		if($idea)
 			$query .= "idea = '" . 	mysqli_real_escape_string($db, $idea) . "', ";
 		if($photo_url)
 			$query .= "photo_url = '" .	mysqli_real_escape_string($db, $photo_url) . "', ";
 		if($type)
 			$query .= "type = " . 	mysqli_real_escape_string($db, $type) . ", ";
 		if($lat && $lon){
 			$query .= 	"lat = " .	mysqli_real_escape_string($db, $lat) . ", " .
 						"lon = " .	mysqli_real_escape_string($db, $lat) . ", ";
 		}

 		if($prompt)
 			$query .= "prompt = " . mysqli_real_escape_string($db, $prompt) . ", ";
 		if($website_url)
 			$query .= "website_url = '" . mysqli_real_escape_string($db, $website_url) . "', ";
 		
 		$query = substr($query, 0, -2);
 		$query .= " WHERE id = " . 	mysqli_real_escape_string($db, $card_id);
 	$res = mysqli_query($db, $query);


 	// Notify members
 	if(count($notifications)){

	 	// Create array of users to notify
		$users_to_notify = array();
		$query =	"SELECT * FROM collaborations" .
	 				" WHERE card_id = " . mysqli_real_escape_string($db, $card_id) .
	 				" AND user_id != " . $author_id .
	 				" AND accepted = 1";
		$res = mysqli_query($db, $query);
	 	while($row = mysqli_fetch_assoc($res))
	 		$users_to_notify[] = $row['user_id'];

	 	// Create notification messages
	 	$message_ids = array();
	 	for($i=0; $i<count($notifications); $i++){
		 	$query =	"INSERT INTO messages " .
		 				"(user_id, text, card_id, response_to, alert) " .
		 				"VALUES (" .
		 					$author_id .
		 				", " .
		 					"'" . $notifications[$i] . "'" .
		 				", " .
		 					$card_id .
		 				", " .
		 					"0" .
		 				", " .
		 					"1" .
		 				")";
		 	$res = mysqli_query($db, $query);
		 	$message_ids[] = mysqli_insert_id($db);
		 }

		 // Create notification receipts
	 	for($i=0; $i<count($users_to_notify); $i++){
	 		for($j=0; $j<count($message_ids); $j++){
		 		$query =	"INSERT INTO message_receipts " .
		 					"(user_id, card_id, message_id) " .
		 					"VALUES (" .
		 						$users_to_notify[$i] .
		 					", " .
		 						$card_id .
		 					", " .
		 						$message_ids[$j] .
		 					")";
		 		$res = mysqli_query($db, $query);
		 	}
	 	}

	 }

 	// Close connection
	mysqli_close($db);
	// exit();
	exit(json_encode($notifications, JSON_PRETTY_PRINT));
?>