<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('db_connect.php');

	// Check for metadata arguments
	if(isset($_GET['card_id']) )
		$card_id = $_GET['card_id'];
	else
		exit('No card id provided');

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM updates" .
 				" WHERE card_id = " . $card_id .
 				" AND response_to IS NULL";
 				" ORDER BY timestamp ASC";
 	$res = mysqli_query($db, $query);

 	if(!$res) exit(json_encode(array()));

 	// Create JSON from database results
	$messages = array();
	$m_ids = array();
	while($row = mysqli_fetch_assoc($res)) {
		$row['responses'] = array();
		$messages[] = $row;
		$m_ids[] = $row['id'];
	}

 	// Query for all responses to initial message
 	$query =	"SELECT * FROM updates" .
 				" WHERE response_to IN ( " . implode($m_ids, ", ") . " )" .
				" ORDER BY timestamp ASC";
	$res = mysqli_query($db, $query);

 	while($msg = mysqli_fetch_assoc($res))
 		foreach($messages as $key => $m)
 			if($m['id'] == $msg['response_to'])
 				$messages[$key]['responses'][] = $msg;
 					// fancy pants!

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	exit(json_encode($messages));

?>