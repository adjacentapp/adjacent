<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = mysqli_real_escape_string($db, $data->email);
	@$fb_hash = mysqli_real_escape_string($db, $data->facebook_hash);
	@$fir_name = mysqli_real_escape_string($db, $data->fir_name);
	@$las_name = mysqli_real_escape_string($db, $data->las_name);
	@$photo_url = mysqli_real_escape_string($db, $data->photo_url);
	@$gender = mysqli_real_escape_string($db, $data->gender);
	@$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;

	// Check for existing account
 	$query =	"SELECT * FROM users " .
 				"WHERE email = '" . mysqli_real_escape_string($db, $email) . "' " .
 				"LIMIT 1";
 	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$user[] = $row;
 	if( count($user) != 0){
 		// Add facebook_hash to user
		$query =	"UPDATE users " .
					"SET facebook_hash = '" . mysqli_real_escape_string($db, $fb_hash) . "', " .
					"photo_url = '" . mysqli_real_escape_string($db, $photo_url) . "', " .
					"verified = 1 " .
					"WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
	 	$res = mysqli_query($db, $query);
	 	$user_id = $user[0]['user_id'];
 	}
 	else{
		// Query database
	 	$query =	"INSERT INTO users " .
	 				"(email, facebook_hash, fir_name, las_name, photo_url, verified) " .
	 				"VALUES (" .
	 					"'" . mysqli_real_escape_string($db, $email) . "', " .
	 					"'" . mysqli_real_escape_string($db, $fb_hash) . "', " .
	 					"'" . mysqli_real_escape_string($db, $fir_name) . "', " .
	 					"'" . mysqli_real_escape_string($db, $las_name) . "', " .
	 					"'" . mysqli_real_escape_string($db, $photo_url) . "', " .
	 					"1 " .
	 				")";
	 	$res = mysqli_query($db, $query);
	 	$user_id = mysqli_insert_id($db);
	 }

 	// create new session token
 	$token = uniqid('', true);
 	$query =	"INSERT INTO sessions " .
 					"(user_id, token)" .
 				" VALUES (" .
 					$user_id . ", " .
 					"'" . $token . "'" .
 				")";
 	$res = mysqli_query($db, $query);


 	// update device token
 	if($push_token){
 		// create new token
 		$query =	"SELECT * FROM devices " .
 				"WHERE token = '" . $push_token . "' " .
 				"AND user_id = " . $user_id;
	 	$res = mysqli_query($db, $query);
	 	if (mysqli_num_rows($res) === 0){
	 		$query =	"INSERT INTO devices " .
	 						"(user_id, token)" .
	 					" VALUES (" .
	 						$user_id . ", " .
	 						"'" . $push_token . "'" .
	 					")";
	 		$res = mysqli_query($db, $query);
	 	}

	 		// update null user_id for device
	 	$query =	"SELECT * FROM devices " .
	 				"WHERE token = '" . $push_token . "' " .
	 				"AND user_id IS NULL";
	 	$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res)){
 			$query =	"UPDATE devices " .
 						"SET user_id = " . $user_id .
 						" WHERE token = '" . $push_token . "'";
 			$res = mysqli_query($db, $query);
 		}
 	}


 	// echo $user_id;
 	$user = (object) ['user_id' => $user_id, 'token' => $token];

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode($user));
?>