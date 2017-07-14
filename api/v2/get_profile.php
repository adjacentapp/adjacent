<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	$db = connect_db();

	// Check for metadata arguments
	if(isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('No user id provided');

 	$query =	"SELECT * FROM users WHERE user_id = {$user_id}";
 	$res = mysqli_query($db, $query);
 	if(!$res) exit(json_encode(array()));
	while($row = mysqli_fetch_assoc($res)) {
		unset($row['password']);
		$profile = $row;
	}

	// Count collaborations user owns
	$query =	"SELECT id, author_id as user_id, idea as pitch, industry, create_time as created_at, update_time as updated_at " .
				"FROM cards WHERE author_id = ${user_id} AND active = 1 " .
				"ORDER BY created_at DESC";
	$res = mysqli_query($db, $query);
	$profile['cards_count'] = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
	// Make array of cards
	$profile['cards'] = array();
	$my_card_ids = array(0);
	while($row = mysqli_fetch_assoc($res)){
		$row['topComment'] = array();
		$row['comments'] = array();
		$row['founder_id'] = $user_id;
		$profile['cards'][] = $row;
		$my_card_ids[] = $row['id'];
	}

	// Get skills
	$query =	"SELECT name from skills WHERE user_id = {$user_id} AND active = 1 ORDER BY name ASC";
	$res = mysqli_query($db, $query);
	$profile['skills'] = array();
	while($row = mysqli_fetch_assoc($res))
		$profile['skills'][] = $row['name'];

	// Get contribution score & cards/message
	$query = "SELECT * FROM card_walls WHERE user_id = {$user_id} ORDER BY" .
				" (SELECT SUM(active) as score FROM wall_post_likes WHERE card_id = cards.id)" .
				" desc";
	$query =	"SELECT SUM(active) as score FROM wall_post_likes WHERE user_id = {$user_id}";
	$res = mysqli_query($db, $query);
	while($row = mysqli_fetch_assoc($res))
		$profile['score'] = $row;

	$query =	"SELECT card_walls.*, (" .
					"SELECT SUM(active) as score FROM wall_post_likes WHERE post_id = card_walls.id" .
				") as score FROM card_walls WHERE user_id = {$user_id} ORDER BY score desc";
	$res = mysqli_query($db, $query);
	$profile['contributions'] = array();
	$my_card_ids = array(0);
	while($row = mysqli_fetch_assoc($res))
		$profile['contributions'][] = $row;

	// Reformat keys
	$profile = (object)array(
		"user_id"	=> 	$profile['user_id'],
		"fir_name"	=> 	$profile['fir_name'],
		"las_name"	=>	$profile['las_name'],
		"photo_url"	=>	$profile['photo_url'],
		"skills"	=>	$profile['skills'],
		"bio"		=>	$profile['bio'],
		"cards"		=>	$profile['cards'],
		"score"		=>	$profile['score'],
		"contributions" => $profile['contributions'],
	);

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit( json_encode($profile, JSON_PRETTY_PRINT) );
?>