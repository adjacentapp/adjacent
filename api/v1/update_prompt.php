<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');

	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$newCard = json_decode($postdata);
	@$prompt_id = $newCard->prompt_id;
	@$title = $newCard->title ? $newCard->title : null;
	@$text = $newCard->text ? $newCard->text : null;
	@$photo_url = $newCard->photo_url ? $newCard->photo_url : null;

	if($text) $text = strip_tags($text);

 	$query =	"UPDATE prompts SET ";
 		if($title)
 		 			$query .= "title = '" .	mysqli_real_escape_string($db, $title) . "', ";
 		if($text)
 			$query .= "text = '" . 	mysqli_real_escape_string($db, $text) . "', ";
 		if($photo_url)
 			$query .= "photo_url = '" .	mysqli_real_escape_string($db, $photo_url) . "', ";
 		$query = substr($query, 0, -2);
 		$query .= 	" WHERE id = " . 	mysqli_real_escape_string($db, $prompt_id);
 	$res = mysqli_query($db, $query);

	mysqli_close($db);
	exit();
?>