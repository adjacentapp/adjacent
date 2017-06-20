<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : null;
	@$network = $data->network ? mysqli_real_escape_string($db, $data->network) : null;

	// Query database
 	$query =	"DELETE FROM networks " .
 				"WHERE user_id = " . $user_id .
 				" AND network = '" . $network . "'";
 	$res = mysqli_query($db, $query);
 	
 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>