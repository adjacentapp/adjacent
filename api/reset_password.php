<html>
<head>
	<meta charset="UTF-8">
	<title>Adjacent - Password Reset</title>
</head>
<body>

<h3>Adjacent Password Reset</h3>

<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$msg = '';

	if( isset($_POST['new_password']) ){
		if(isset($_GET['email']) )
			$email = $_GET['email'];
		else
			exit('no email address');

		// Set new encrypted password
		// $pass = md5( 'aether12292015' . $_POST['new_password'] );
		// ready_for_sha256
		$pass = hash("sha256", 'aether12292015' . $_POST['new_password']);
		$db = connect_db();
		$query =	"UPDATE users" .
					" SET password= '" . $pass . "'" .
 					" WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
	 	$res = mysqli_query($db, $query);
	 	// if(!$res)
	 	// 	echo mysqli_error();
	 	// else
	 		$msg = 'Password successfully reset!';

 		// Delete old request
 		$query =	"DELETE FROM reset_password_requests" .
 					" WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
 		$res = mysqli_query($db, $query);
 		// if(!$res)
 	 // 		echo mysqli_error();

 	 	exit($msg);
	}

	// Check for arguments
	if(isset($_GET['email']) )
		$email = $_GET['email'];
	else
		exit('no email address');
	if(isset($_GET['reset_code']) )
		$reset_code = $_GET['reset_code'];
	else
		exit('no reset_code');

	// Query database
	$db = connect_db();
 	$query =	"SELECT * FROM reset_password_requests" .
 				" WHERE email = '" . mysqli_real_escape_string($db, $email) . "'";
 	$res = mysqli_query($db, $query);

 	// if(!$res)
 	// 	echo mysqli_error();

 	// Check for reset_code match
	while($row = mysqli_fetch_assoc($res)) {
		if( time() - strtotime($row['time']) > 1000*60*60*2)
			$msg = "This reset request has expired. Please request a new password reset.";
		else if($row['reset_code'] != $reset_code)
			$msg = "The provided reset code does not match what we have on record.";
		else{
			$msg = "Please enter your new password for " . $email . "<br>" .
					"<form method='POST'>" .
						"<input type='password' name='new_password'>" .
						"<input type='submit' value='Submit'>" .
					"</form>";
		}
	}

	// Close connection and return JSON
 	mysqli_free_result($res);
 	mysqli_close($db);
 
 	echo $msg;

?>
	
</body>
</html>