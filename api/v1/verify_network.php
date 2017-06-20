<html>
<head>
	<meta charset="UTF-8">
	<title>Adjacent - Network Verification</title>
</head>
<body>

<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../db_connect.php');

	$msg = '';

	$db = connect_db();
	// Check for arguments
	if(isset($_GET['user_id']) )
		$user_id = mysqli_real_escape_string($db, $_GET['user_id']);
	else
		exit('error');
	if(isset($_GET['verification_code']) )
		$verification_code = mysqli_real_escape_string($db, $_GET['verification_code']);
	else
		exit('error');
	if(isset($_GET['network']) )
		$network = mysqli_real_escape_string($db, $_GET['network']);
	else
		exit('error');

	// Query database
 	$query =	"SELECT * FROM networks" .
 				" WHERE user_id = " . $user_id .
 				" AND network = '" . $network . "'";
 	$res = mysqli_query($db, $query);

 	if(!$res)
 		echo mysqli_error();

 	// Check for reset_code match
	while($row = mysqli_fetch_assoc($res)) {
		if($row['verification_code'] == $verification_code){
			$query =	"UPDATE networks " .
						"SET verified = 1, " .
						"verification_code = '' " .
						"WHERE user_id = " . $user_id .
						" AND network = '" . $network . "'";
			$res = mysqli_query($db, $query);
			$msg = "Thank you. You've successfully joined the " . strtoupper($network) . " network.";
		}
		else 
			$msg = "We're sorry, an error has occurred. Please request a new network confirmation email.";
	}

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 	
 	echo '<h3>Adjacent Network Verification</h3>';
 	echo $msg;

?>
	
</body>
</html>