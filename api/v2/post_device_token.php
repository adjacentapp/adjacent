<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	if($data->user_id && $data->token){
		@$user_id = mysqli_real_escape_string($db, $data->user_id);
		@$token = mysqli_real_escape_string($db, $data->token);
	}
	else
		exit();

	// Delete all records for this device
	$query =	"DELETE FROM devices WHERE token = '{$token}'";
	$res = mysqli_query($db, $query);

	// Create new record for this user
	$query =	"INSERT INTO devices (user_id, token) VALUES ({$user_id}, '{$token}')";
	$res = mysqli_query($db, $query);

	mysqli_close($db);
	exit();
?>