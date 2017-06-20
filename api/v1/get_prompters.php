<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	$json = file_get_contents("prompters.json");
	$prompters = json_decode($json, true);
	
	$db = connect_db();
	$card_id = isset($_GET['card_id']) ? mysqli_real_escape_string($db, $_GET['card_id']) : null;
	if($card_id){
	 	$query =	"SELECT * FROM cards" .
	 				" WHERE id = " . $card_id;
	 	$res = mysqli_query($db, $query);
	 	if(!$res) exit(json_encode($prompters,JSON_PRETTY_PRINT));

		$type = 0;
		while($row = mysqli_fetch_assoc($res))
			$type = (int)$row['type'] - 1;

		$prompters = $prompters[$type];

		mysqli_free_result($res);
	 	mysqli_close($db);
	}

 	exit(json_encode($prompters,JSON_PRETTY_PRINT));
?>