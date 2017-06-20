<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = mysqli_real_escape_string($db, $data->user_id);
	@$reset = mysqli_real_escape_string($db, $data->reset);
	if($reset)
		$query =	"UPDATE users SET discover_offset = 0 WHERE user_id = " . $user_id;
	else
		$query =	"UPDATE users SET discover_offset = discover_offset + 1 WHERE user_id = " . $user_id;
	 $res = mysqli_query($db, $query);

	 echo $query;

 	// Close connection
	// if($res) mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>