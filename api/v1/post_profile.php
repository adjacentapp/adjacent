<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Decode card into JSON
	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id = $data->user_id ? mysqli_real_escape_string($db, $data->user_id) : null;
	@$fir_name = $data->fir_name ? mysqli_real_escape_string($db, $data->fir_name) : null;
	@$las_name = $data->las_name ? mysqli_real_escape_string($db, $data->las_name) : null;
	@$bio = $data->bio ? mysqli_real_escape_string($db, $data->bio) : null;
	@$photo_url = $data->photo_url ? mysqli_real_escape_string($db, $data->photo_url) : false;

	$query =	"UPDATE users " .
				"SET fir_name = '" . 	$fir_name . "', " .
				"las_name = '" . 		$las_name . "', " .
				"bio = '" . 			$bio . "'";
	if($photo_url)
		$query .= ", photo_url = '" .	$photo_url . "'";
	$query .=	" WHERE user_id = " . 	$user_id;
	$res = mysqli_query($db, $query);

 	// Close connection
 	mysqli_free_result($res);
	mysqli_close($db);
	exit($query);
?>