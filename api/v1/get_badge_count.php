<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../db_connect.php');

	$db = connect_db();

	$user_id = $_GET['user_id'];

	$query =	"SELECT * FROM users WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);
 	while($row = mysqli_fetch_assoc($res))
 		echo "USER Badge_count = " . $row['badge_count'];

 	echo "<hr/>";

	 $badge_count = 0;
	 $query =	"SELECT * FROM message_receipts WHERE user_id = " . $user_id;
	 $res = mysqli_query($db, $query);
	 while($row = mysqli_fetch_assoc($res))
	 	$badge_count++;
	 echo "message receipt count = " . $badge_count;
	 echo "<hr/>";
	 
	 $wall_receipts = 0;
	 $query =	"SELECT * FROM wall_receipts WHERE new = 1 AND user_id = " . $user_id;
	 $res = mysqli_query($db, $query);
	 while($row = mysqli_fetch_assoc($res)){
	 	$wall_receipts++;
	 	$badge_count++;
	 }	 	

	 echo "wall receipt count = " . $wall_receipts;
	 echo "<hr/>";
	 
	 $updates = 0;
	 $query =	"SELECT * FROM update_receipts WHERE new = 1 AND user_id = " . $user_id;
	 $res = mysqli_query($db, $query);
	 while($row = mysqli_fetch_assoc($res)){
	 	$updates++;
	 	$badge_count++;
	 }

	 echo "updates receipts = " . $updates;
	 echo "<hr/>";

	 echo "total calculated badge_count = " . $badge_count;
	 echo "<hr/>";
	 
	 // $query =	"UPDATE users SET badge_count = " . $badge_count . " WHERE user_id = " . $user_id;
	 // $res = mysqli_query($db, $query);


	mysqli_free_result($res);
	mysqli_close($db);
	exit();
?>