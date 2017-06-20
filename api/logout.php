<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : false;
	@$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;
	@$session_token = $data->session_token ? mysqli_real_escape_string($db, $data->session_token) : false;

	if($user_id && $push_token){
		$query =	"DELETE FROM devices " .
					"WHERE token = '" . $push_token . "' " .
					"AND user_id = " . (int)$user_id;
		$res = mysqli_query($db, $query);
		echo $query;
	}

	if($user_id && $session_token){
		$query =	"DELETE FROM sessions " .
					"WHERE token = '" . $session_token . "' " .
					"AND user_id = " . (int)$user_id;
		$res = mysqli_query($db, $query);
		echo $query;
	}

	if($push_token){
		// clear badge count
	 	require_once('push_notification.php');
	 	update_badge(array($push_token), 0);
	}


 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>