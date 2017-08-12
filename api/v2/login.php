<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$email = $data->email ? mysqli_real_escape_string($db, $data->email) : null;
	@$pass = $data->pass ? mysqli_real_escape_string($db, $data->pass) : null;
	@$fb_hash = $data->facebook_hash ? mysqli_real_escape_string($db, $data->facebook_hash) : null;
	@$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;

	if( !$email || (!$pass && !$fb_hash) )
		exit( json_encode((object)array("message" => 'Email and (password or facebook_hash) required.'), JSON_PRETTY_PRINT) );
	
	$USER = null;
 	$query =	"SELECT * FROM users WHERE email = '{$email}'";
 	if($fb_hash)
 		$query .= " AND facebook_hash = '{$fb_hash}'";
 	$res = mysqli_query($db, $query);

	while($row = mysqli_fetch_assoc($res)){

		if($pass && $row['password'] !== $pass)
			exit( json_encode((object)array("message" => 'Incorrect password.'), JSON_PRETTY_PRINT) );

		$USER = (object)array(
			"user_id" => $row['user_id'], 
			"email" => $row['email'],
			"fir_name" => $row['fir_name'],
			"las_name" => $row['las_name'],
			"photo_url" => $row['photo_url'],
			"valid" => false
		);
		// exit if unverfied and signup > 2 days ago
 		if(!$row['verified'])
			if(strtotime($row['created_at']) <= strtotime('-48 hours')){
				$USER['expired'] = true;
				mysqli_free_result($res);
				mysqli_close($db);
				exit(json_encode($USER));
				// exit( json_encode((object)array("message" => 'You must verify your email address.'), JSON_PRETTY_PRINT) );
			}
	}
	if(!$USER)
 		exit( json_encode((object)array("message" => 'Incorrect email address.'), JSON_PRETTY_PRINT) );


 	// cast user from object to array
	$USER = (array)$USER;
	$user_id = $USER['user_id'];

	// find user's networks
	$USER['networks'] = array();
	$query =	"SELECT * FROM networks WHERE user_id = {$user_id}";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$USER['networks'][] = $row['network'];

	// create new session token
	$USER['token'] = uniqid('', true);
	$query =	"INSERT INTO sessions (user_id, token) VALUES ({$user_id}, '{$USER['token']}')";
	$res = mysqli_query($db, $query);

	// create new device push token
	if($push_token){
		$query =	"SELECT * FROM devices WHERE token = '{$push_token}' AND user_id = {$user_id}";
		$res = mysqli_query($db, $query);
		if (mysqli_num_rows($res) === 0){
			$query =	"INSERT INTO devices (user_id, token) VALUES ({$user_id}, '{$push_token}')";
			$res = mysqli_query($db, $query);
		}

		// update null user_id for device
		$query =	"SELECT * FROM devices WHERE token = '{$push_token}' AND user_id IS NULL";
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res)){
			$query =	"UPDATE devices SET user_id = {$user_id} WHERE token = '{$push_token}'";
			$res = mysqli_query($db, $query);
		}
 	}

 	$USER['valid'] = true;
 	if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
 	mysqli_close($db);
 	exit( json_encode($USER, JSON_PRETTY_PRINT) );
?>