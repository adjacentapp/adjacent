<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = mysqli_real_escape_string($db, $data->email);
	@$pass = mysqli_real_escape_string($db, $data->pass);
	@$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;

	// Check for existing account
 	$query =	"SELECT * FROM users WHERE email = '{$email}'";
 	$res = mysqli_query($db, $query);
	if(mysqli_num_rows($res) > 0)
 		exit( json_encode((object)array("message" => 'An account with this email address already exists')) );

 	// create new user
 // 	$query =	"INSERT INTO users " .
 // 				"(email, password) " .
 // 				"VALUES (" .
 // 					"'" . $email . "', " .
 // 					"'" . $pass . "'" .
 // 				")";
 // 	$res = mysqli_query($db, $query);
 // 	$user_id = mysqli_insert_id($db);

 // 	// create new session token
 // 	$token = uniqid('', true);
 // 	$query =	"INSERT INTO sessions " .
 // 					"(user_id, token)" .
 // 				" VALUES (" .
 // 					$user_id . ", " .
 // 					"'" . $token . "'" .
 // 				")";
 // 	$res = mysqli_query($db, $query);

 // 	// create user object to be returned
 // 	$user = (object)array("user_id" => $user_id, "email" => $email, "token" => $token, "valid" => true);
 	$user = (object)array("id" => 1, "email" => $email, "token" => $token, "valid" => true);

	// // update device token
	// if($push_token){
	// 	// create new token
	// 	$query =	"SELECT * FROM devices " .
	// 			"WHERE token = '" . $push_token . "' " .
	// 			"AND user_id = " . $user_id;
	// 	$res = mysqli_query($db, $query);
	// 	if (mysqli_num_rows($res) === 0){
	// 		$query =	"INSERT INTO devices " .
	// 						"(user_id, token)" .
	// 					" VALUES (" .
	// 						$user_id . ", " .
	// 						"'" . $push_token . "'" .
	// 					")";
	// 		$res = mysqli_query($db, $query);
	// 	}
	// 	// update null user_id for device
	// 	$query =	"SELECT * FROM devices " .
	// 				"WHERE token = '" . $push_token . "' " .
	// 				"AND user_id IS NULL";
	// 	$res = mysqli_query($db, $query);
	// 	while($row = mysqli_fetch_assoc($res)){
	// 		$query =	"UPDATE devices " .
	// 					"SET user_id = " . $user_id .
	// 					" WHERE token = '" . $push_token . "'";
	// 		$res = mysqli_query($db, $query);
	// 	}
	// 	if($res) mysqli_free_result($res);
	// }

	// Send verification emails
	require_once('send_verification_email.php');
	send_email($email);
	// THROWING ERROR ======= ^^^^^^^^^^^^^^^^^^^^^^

 	// Close connection
	mysqli_close($db);
	exit(json_encode($user, JSON_PRETTY_PRINT));
?>