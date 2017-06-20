<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = mysqli_real_escape_string($db, $data->email);
	@$pass = mysqli_real_escape_string($db, $data->pass);
	@$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;

	// Check for existing account
 	$query =	"SELECT * FROM users " .
 				"WHERE email = '" . $email . "'";
 	$res = mysqli_query($db, $query);
 	$user = array();
	while($row = mysqli_fetch_assoc($res))
		$user[] = $row;
 	if( count($user) != 0)
 		exit('user_already_exists');

	// don't require umich verification until we figure that email filtering out...
	// $provider = strtolower( explode("@", $email, 2)[1] );
	// if($provider == "umich.edu"){
	// 	$query =	"INSERT INTO users " .
	// 				"(email, password, verified) " .
	// 				"VALUES (" .
	// 					"'" . $email . "', " .
	// 					"'" . $pass . "', " .
	// 					"1" .
	// 				")";
	// 	$res = mysqli_query($db, $query);
	// }
	// else {
		$verification_code = uniqid('', true);
	 	$query =	"INSERT INTO users " .
	 				"(email, password, verification_code) " .
	 				"VALUES (" .
	 					"'" . $email . "', " .
	 					"'" . $pass . "', " .
	 					"'" . $verification_code . "'" .
	 				")";
	 	$res = mysqli_query($db, $query);
	 // }

 	$user_id = mysqli_insert_id($db);

 	// create new session token
 	$token = uniqid('', true);
 	$query =	"INSERT INTO sessions " .
 					"(user_id, token)" .
 				" VALUES (" .
 					$user_id . ", " .
 					"'" . $token . "'" .
 				")";
 	$res = mysqli_query($db, $query);

 	// echo $user_id;
 	$user = (object) ['user_id' => $user_id, 'token' => $token];


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
		if($res) mysqli_free_result($res);
		while($row = mysqli_fetch_assoc($res)){
			$query =	"UPDATE devices " .
						"SET user_id = " . $user_id .
						" WHERE token = '" . $push_token . "'";
			$res = mysqli_query($db, $query);
		}
	}


 	// Close connection
	mysqli_close($db);
	exit(json_encode($user));
?>