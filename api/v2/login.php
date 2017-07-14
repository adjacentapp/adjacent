<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	// Check for email address arguments
	// Decode card into JSON
	// $postdata = file_get_contents("php://input");
	// $data = json_decode($postdata);
	// @$email = mysqli_real_escape_string($db, $data->email);
	// @$fb_hash = mysqli_real_escape_string($db, $data->facebook_hash);
	// @$pass = mysqli_real_escape_string($db, $data->pass);
	// @$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;

	if( isset($_GET['email']) && isset($_GET['pass']) ) {
		$email = mysqli_real_escape_string($db, $_GET['email']);
		$pass = mysqli_real_escape_string($db, $_GET['pass']);
		$fb_hash = false;
	}
	else if( isset($_GET['email']) && isset($_GET['facebook_hash']) ) {
		$email = mysqli_real_escape_string($db, $_GET['email']);
		$fb_hash = mysqli_real_escape_string($db, $_GET['facebook_hash']);
		$pass = false;
	}
	else
		exit( json_encode((object)array("message" => 'email and pass or facebook_hash required'), JSON_PRETTY_PRINT) );
	$push_token = isset($_GET['push_token']) ? mysqli_real_escape_string($db, $_GET['push_token']) : false;

	// Query database
	$user = null;
 	$query =	"SELECT * FROM users WHERE email = '{$email}'";
 	if($fb_hash)
 		$query .= " AND facebook_hash = '{$fb_hash}'";
 	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res)){

		if($pass && $row['password'] !== $pass)
			exit( json_encode((object)array("message" => 'Incorrect password.'), JSON_PRETTY_PRINT) );

		$user = (object)array(
			"id" => $row['user_id'], 
			"email" => $row['email'],
			"fir_name" => $row['fir_name'],
			"las_name" => $row['las_name'],
			"photo_url" => $row['photo_url'],
			"bio" => $row['bio'],
			"valid" => false
		);
		// exit if unverfied and signup > 12hrs ago
 		if(!$row['verified'])
			if(strtotime($row['created_at']) <= strtotime('-12 hours')){
				$user['expired'] = true;
				mysqli_free_result($res);
				mysqli_close($db);
				exit(json_encode($user));
				// exit( json_encode((object)array("message" => 'You must verify your email address.'), JSON_PRETTY_PRINT) );
			}
	}
	if(!$user)
 		exit( json_encode((object)array("message" => 'Incorrect email address.'), JSON_PRETTY_PRINT) );


 	// cast user from object to array
	$user = (array)$user;
	$user_id = $user['id'];

	// find user's networks
	$user['networks'] = array();
	$query =	"SELECT * FROM networks " .
				"WHERE user_id = " . $user_id;
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$user['networks'][] = $row['network'];

	// create new session token
	$user['token'] = uniqid('', true);
	$query =	"INSERT INTO sessions " .
					"(user_id, token)" .
				" VALUES (" .
					$user_id . ", " .
					"'" . $user['token'] . "'" .
				")";
	$res = mysqli_query($db, $query);

	// create new device push token
	if($push_token){
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

 	$user['valid'] = true;
 	mysqli_close($db);
 	exit( json_encode($user, JSON_PRETTY_PRINT) );
?>