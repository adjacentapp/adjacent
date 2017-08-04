<?php
	header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	require_once('../db_connect.php');
	$db = connect_db();

	$postdata = file_get_contents("php://input");
	$data = json_decode($postdata);
	@$user_id 		= mysqli_real_escape_string($db, $data->user->id);
	@$fir_name 		= mysqli_real_escape_string($db, $data->user->fir_name);
	@$las_name 		= mysqli_real_escape_string($db, $data->user->las_name);
	@$bio 			= $data->bio ? mysqli_real_escape_string($db, $data->bio) : null;
	@$photo_url 	= $data->photo_url ? mysqli_real_escape_string($db, $data->photo_url) : null;
	@$skills 		= $data->skills ? $data->skills : [];

	$query =	"UPDATE users " .
				"SET fir_name = '" . 	$fir_name . "', " .
				"las_name = '" . 		$las_name . "', " .
				"bio = '" . 			$bio . "'";
	if($photo_url)
		$query .= ", photo_url = '" .	$photo_url . "'";
	$query .=	" WHERE user_id = " . 	$user_id;
	$res = mysqli_query($db, $query);

	if(!empty($skills)){
		foreach($skills as $index => $skill)
			$skills[$index] = mysqli_real_escape_string($db, $skill);


		$active_skills = array();
		$query =	"SELECT name from skills WHERE user_id = {$user_id} AND active = 1";
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			$active_skills[] = $row['name'];

		$query =	"UPDATE skills SET active = 1 WHERE user_id = {$user_id}".
					" AND name IN ( '" . implode($skills, "', '") . "' )" .
					" AND name NOT IN ( '" . implode($active_skills, "', '") . "' )";
		$res = mysqli_query($db, $query);
		$query =	"UPDATE skills SET active = 0 WHERE user_id = {$user_id}" .
					" AND name NOT IN ( '" . implode($skills, "', '") . "' )" .
					" AND name IN ( '" . implode($active_skills, "', '") . "' )";
		$res = mysqli_query($db, $query);

		
		foreach($skills as $index => $skill) {
			if(!in_array($skill, $active_skills)){
				$query =	"INSERT INTO skills (user_id, name) VALUES ({$user_id}, '{$skill}')";
				$res = mysqli_query($db, $query);
			}
		}
	}

	$profile = (object)array("id" => $user_id, "fir_name" => $fir_name, "las_name" => $las_name, "bio" => $bio, "photo_url" => $photo_url, 
		"skills" => $skills,
		"valid" => true
	);

	// if(is_a($res, 'mysqli_result')) mysqli_free_result($res);
	mysqli_close($db);
	exit(json_encode($profile, JSON_PRETTY_PRINT));
?>