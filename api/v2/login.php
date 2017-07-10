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
	}
	else if( isset($_GET['email']) && isset($_GET['facebook_hash']) ) {
		$email = mysqli_real_escape_string($db, $_GET['email']);
		$fb_hash = mysqli_real_escape_string($db, $_GET['facebook_hash']);
	}
	else
		exit( json_encode((object)array("message" => 'first one failed'), JSON_PRETTY_PRINT) );
	$push_token = isset($_GET['push_token']) ? mysqli_real_escape_string($db, $_GET['push_token']) : false;

	// Query database
 	$query =	"SELECT * FROM users WHERE email = '{$email}'";
 	if($pass)
 		$query .= " AND password = '{$pass}'";
 	else if($fb_hash)
 		$query .= " AND facebook_hash = '{$fb_hash}'";
 	$res = mysqli_query($db, $query);

	$user = array();
	while($row = mysqli_fetch_assoc($res)){
		unset($row['password']);
		$row['networks'] = [];

		// exit if unverfied and signup > 12hrs ago
 		if(!$row['verified'])
			if(strtotime($row['created_at']) <= strtotime('-12 hours')){
				$row['expired'] = true;
				$user[] = $row;
				mysqli_free_result($res);
				mysqli_close($db);
				exit( json_encode((object)array("message" => 'third one failed'), JSON_PRETTY_PRINT) );
				exit(json_encode($user[0]));
			}

		$user[] = $row;
 		
 		if( count($user) == 0)
 			exit( json_encode((object)array("message" => 'That email address and password does not match any user we have on record.'), JSON_PRETTY_PRINT) );
	}


	// find user's networks
	$query =	"SELECT * FROM networks " .
				"WHERE user_id = " . $user[0]['user_id'];
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$user[0]['networks'][] = $row['network'];

	// create new session token
	$user[0]['token'] = uniqid('', true);
	$query =	"INSERT INTO sessions " .
					"(user_id, token)" .
				" VALUES (" .
					$user[0]['user_id'] . ", " .
					"'" . $user[0]['token'] . "'" .
				")";
	$res = mysqli_query($db, $query);


	// create new device push token
	$user_id = $user[0]['user_id'];
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

 	$user[0]['valid'] = true;

 	mysqli_close($db);
 	exit( json_encode($user[0], JSON_PRETTY_PRINT) );

?>