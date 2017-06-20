<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	@$push_token = $data->push_token ? mysqli_real_escape_string($db, $data->push_token) : false;

	// update device token
	if($push_token){
		// create new token
		$query =	"SELECT * FROM devices " .
				"WHERE token = '" . $push_token . "' ";
		$res = mysqli_query($db, $query);
		if (mysqli_num_rows($res) === 0){
			$query =	"INSERT INTO devices " .
							"(token)" .
						" VALUES (" .
							"'" . $push_token . "'" .
						")";
			$res = mysqli_query($db, $query);
		}
	}


 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>