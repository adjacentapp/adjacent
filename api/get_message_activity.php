<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	if( isset($_GET['user_id']) ) {
		$user_id = $_GET['user_id'];
	} else {
		exit('no user_id');
	}

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM message_receipts " .
 				"WHERE user_id = " . $user_id;
 	$res = mysqli_query($db, $query);
 	if(!$res) exit(json_encode(array()));

	// Get root update id's
	$groups = array();
	$directs = array();
	while($row = mysqli_fetch_assoc($res)){
		if($row['card_id'] != null)
			$groups[] = (object) ['card_id' => $row['card_id'], 'message_id' => $row['message_id']];
		else if($row['conversation_id'] != null)
			$directs[] = (object) ['conversation_id' => $row['conversation_id'], 'message_id' => $row['message_id']];
	}

	$notifications = [$groups, $directs];

	// Close connection
 	mysqli_free_result($res);
 	mysqli_close($db);
	exit(json_encode($notifications));

?>