<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	$db = connect_db();

	// Check for metadata arguments
	if(isset($_GET['id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['id']);
	else
		exit('No user id provided');


	// Query database
 	$query =	"SELECT * FROM users" .
 				" WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	while($row = mysqli_fetch_assoc($res)) {
		unset($row['password']);
		$profile = $row;
	}

	// Count collaborations user owns
	$query =	"SELECT * FROM cards" .
				" WHERE author_id = " . $user_id .
				" AND active = 1";
	$res = mysqli_query($db, $query);
	$profile['cards_count'] = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
	// Make array of cards
	// $profile['cards'] = array();
	$my_card_ids = array(0);
	while($row = mysqli_fetch_assoc($res)){
		// $profile['cards'][] = $row;
		$my_card_ids[] = $row['id'];
	}

	// Count collaborations user is a member of
	$query =	"SELECT * FROM collaborations" .
				" WHERE user_id = " . $user_id .
				" AND status != 'owner'" .
				" AND accepted = 1";
	$res = mysqli_query($db, $query);
	$all_card_ids = array(0);
	// $all_card_ids[] = 0;
	while($row = mysqli_fetch_assoc($res))
		$all_card_ids[] = $row['card_id'];
	
	// Remove deactivated cards
	$query =	"SELECT * FROM cards" .
				" WHERE id IN ( " . implode($all_card_ids, ", ") . " )" .
				" AND active = 1";
	$res = mysqli_query($db, $query);
	
	$profile['collaborations_count'] = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
	
	// Make array of collab_ids
	$card_ids = array(0);
	// $card_ids[] = 0;
	while($row = mysqli_fetch_assoc($res))
		$card_ids[] = $row['id'];
	
	// Make array of collaborations
	// $query =	"SELECT * FROM cards" .
	// 			" WHERE id IN ( " . implode($card_ids, ", ") . " )";
	// $res = mysqli_query($db, $query);
	// $profile['collaborations'] = array();
	// while($row = mysqli_fetch_assoc($res))
	// 	$profile['collaborations'][] = $row;


	// Count unique user connections via collabs, bookmarks, and convos
	$query =	"SELECT DISTINCT user_id FROM collaborations" .
				" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
				" AND accepted = 1" .
				" AND user_id != " . $user_id;
	$query .=	" OR card_id IN ( " . implode($my_card_ids, ", ") . " )" .
				" AND accepted = 1" .
				" AND user_id != " . $user_id;
	$res = mysqli_query($db, $query);
	$profile['connections_count'] = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;

	// Keep track of counted users
	$user_ids = array(0);
	while($row = mysqli_fetch_assoc($res))
		$user_ids[] = $row['user_id'];

	// // conversation connections
	// $query =	"SELECT * FROM conversations" .
	// 			" WHERE (" .
	// 				"author_id = " . $user_id .
	//  				" AND other_id NOT IN ( " . implode($user_ids, ", ") . " )" .
	// 			") OR (" .
	// 				"other_id = " . $user_id .
	// 				" AND author_id NOT IN ( " . implode($user_ids, ", ") . " )" .
	// 			")";

	// $res = mysqli_query($db, $query);
	// $add = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
	// $profile['connections_count'] += $add;

	// // Keep track of counted users
	// while($row = mysqli_fetch_assoc($res)){
	// 	if($row['author_id'] != $user_id)
	// 		$user_ids[] = $row['author_id'];
	// 	else if($row['other_id'] != $user_id)
	// 		$user_ids[] = $row['other_id'];
	// }

	// // follower connections
	// $query =	"SELECT * FROM bookmarks" .
	// 			" WHERE card_id IN ( " . implode($card_ids, ", ") . " )" .
	// 			" AND user_id NOT IN ( " . implode($user_ids, ", ") . " )";
	// $res = mysqli_query($db, $query);
	// $add = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
	// $profile['connections_count'] += $add;

	// // Keep track of counted users
	// while($row = mysqli_fetch_assoc($res))
	// 	$user_ids[] = $row['user_id'];

	// // followering connections
	$following_card_ids = array(0);
	$query =	"SELECT * FROM bookmarks" .
				" WHERE user_id = " . $user_id .
				" AND card_active = 1" .
				" AND active = 1";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$following_card_ids[] = $row['card_id'];
	if(!empty($following_card_ids)){
		$query =	"SELECT DISTINCT user_id FROM collaborations" .
					" WHERE card_id IN ( " . implode($following_card_ids, ", ") . " )" .
					" AND accepted = 1" .
					" AND user_id != " . $user_id;
		if (!empty($user_ids))
			$query .= " AND user_id NOT IN ( " . implode($user_ids, ", ") . " )";
		$res = mysqli_query($db, $query);

		// Keep track of counted users
		$add = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
		$profile['connections_count'] += $add;
		while($row = mysqli_fetch_assoc($res))
			$user_ids[] = $row['user_id'];
	}


	// Make array of connections
	$profile['connections'] = array();
	$query =	"SELECT user_id, fir_name, las_name, photo_url FROM users" .
				" WHERE user_id IN ( " . implode($user_ids, ", ") . " )";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$profile['connections'][] = $row;
	

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($profile));

?>