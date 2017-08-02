<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');
	$db = connect_db();

	if(isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('No user id provided');

	$my_id = isset($_GET['my_id']) ? mysqli_real_escape_string($db, $_GET['my_id']) : '0';

 	$query =	"SELECT * FROM users WHERE user_id = {$user_id}";
 	$res = mysqli_query($db, $query);
 	if(!$res) exit(json_encode(array()));
	while($row = mysqli_fetch_assoc($res)) {
		unset($row['password']);
		$profile = $row;
	}

	// Count collaborations user owns
	$query =	"SELECT id, author_id as user_id, idea as pitch, industry_string as industry, anonymous, challenge, background, stage, challenge_details, create_time as created_at, update_time as updated_at " .
				"FROM cards WHERE author_id = ${user_id} AND active = 1 ";
	if($user_id != $my_id) $query .= "AND anonymous = 0 ";
	$query .=	"ORDER BY created_at DESC";
	$res = mysqli_query($db, $query);
	$profile['cards_count'] = mysqli_num_rows($res) ? mysqli_num_rows($res) : 0;
	// Make array of cards
	$profile['cards'] = array();
	$my_card_ids = array(0);
	while($row = mysqli_fetch_assoc($res)){
		$row['topComment'] = array();
		$row['comments'] = array();
		$row['founder_id'] = $user_id;
		$row['anonymous'] = $row['anonymous'] == '1' ? true : false;
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
	while($row = mysqli_fetch_assoc($res))
		$profile['contributions'][] = $row;

	// Check if each card is bookmarked by the my_id user
	if($user_id != $my_id){
		$query = 	"SELECT * FROM bookmarks " .
					" WHERE card_id IN ( " . implode($my_card_ids, ", ") . " )" .
					" AND card_active = 1" .
					" AND active = 1" .
					" AND user_id = {$my_id}";
		$res = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($res))
			foreach($profile['cards'] as $key => $card)
				if($card['id'] == $row['card_id'])
					$profile['cards'][$key]['following'] = true;
	}

	// Reformat keys
	$profile = (object)array(
		"user_id"	=> 	$profile['user_id'],
		"email"		=> 	$profile['email'],
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
