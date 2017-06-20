<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');


	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM cards";
 	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res)){
		$query = 	"INSERT into collaborations  " .
					"(card_id, user_id, role, accepted) " .
					"VALUES (" . $row['id'] . ", " . $row['author_id'] . ", 'Owner', 1)";
		$new = mysqli_query($db, $query);

	}

 	mysqli_free_result($res);
 	mysqli_free_result($new);
 	mysqli_close($db);

?>