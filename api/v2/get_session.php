<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');
	$db = connect_db();

	// Check for email address arguments
	if( isset($_GET['token']) )
		$token = mysqli_real_escape_string($db, $_GET['token']);
	else
		exit( json_encode((object)array("message" => 'no token'), JSON_PRETTY_PRINT) );

	$push_token = isset($_GET['push_token']) ? mysqli_real_escape_string($db, $_GET['push_token']) : false;

	// Query database
 	$query =	"SELECT * FROM sessions WHERE token = '{$token}'";
 	$res = mysqli_query($db, $query);

 	// Create JSON from database results
	$session = array();
	while($row = mysqli_fetch_assoc($res))
		if(strtotime($row['update_time']) >= strtotime('-30 days'))
			$session[] = $row;

 	if( count($session) === 0)
 		exit( json_encode((object)array("message" => 'no session'), JSON_PRETTY_PRINT) );
 	else {
 		// get user
	 	$query =	"SELECT * FROM users " .
	 				"WHERE user_id = " . $session[0]['user_id'] .
	 				" LIMIT 1";
	 	$res = mysqli_query($db, $query);

	 	// Create user JSON
		$user = array();
 		while($row = mysqli_fetch_assoc($res)){
 			unset($row['password']);
 			unset($row['bio']);

 			// exit if unverfied and signup > 2 days ago
	 		if(!$row['verified'])
 				if(strtotime($row['created_at']) <= strtotime('-48 hours')){
 					$row['expired'] = true;
 					$user[] = $row;
 					mysqli_free_result($res);
	 				mysqli_close($db);
 					exit(json_encode($user[0]));
 				}

 			$user[] = $row;
 		}

 		// find user's networks
 		$query =	"SELECT * FROM networks " .
 					"WHERE user_id = " . $user[0]['user_id'];
 		$res = mysqli_query($db, $query);
 		while($row = mysqli_fetch_assoc($res))
 			$user[0]['networks'][] = $row['network'];

	 	// update session timestamp
 		$query =	"UPDATE sessions " .
 					"SET update_time = now() " .
 					"WHERE token = '" . $token . "'";
 		$res = mysqli_query($db, $query);

 		$user_id = $user[0]['user_id'];
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

		// recalcuate badge count
		$badge_count = 0;
		$query =	"SELECT * FROM message_receipts WHERE user_id = " . $user_id;
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			$badge_count++;
		$query =	"SELECT * FROM wall_receipts WHERE new = 1 AND user_id = " . $user_id;
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			$badge_count++;
		$query =	"SELECT * FROM update_receipts WHERE new = 1 AND user_id = " . $user_id;
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			$badge_count++;
		$query =	"UPDATE users SET badge_count = " . $badge_count . " WHERE user_id = " . $user_id;
		$res = mysqli_query($db, $query);
		$user[0]['badge_count'] = $badge_count;

		// update badge count on other devices
		if($push_token){
	 		require_once('push_notification.php');
	 		update_badge(array($push_token), $badge_count);
	 	}

	 	$user[0]['valid'] = true;

	 	if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	 	mysqli_close($db);
 		exit(json_encode($user[0], JSON_PRETTY_PRINT));
 	}
 		

?>