<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');
	$db = connect_db();

 	$query =	"SELECT * FROM skill_list";
 	$res = mysqli_query($db, $query);
	$skills = [];
	while($row = mysqli_fetch_assoc($res))
		$skills[] = $row['name'];

 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($skills));
?>