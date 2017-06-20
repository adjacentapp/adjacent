<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	// Check for metadata arguments
	if(isset($_GET['email']) )
		$email = $_GET['email'];
	else
		exit('No email address provided');


	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM users" .
 				" WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
 	$users = array();
	while($row = mysqli_fetch_assoc($res)) {
		unset($row['password']);
		$users[] = $row;
	}

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($users));

?>