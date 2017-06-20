<html>
<head>
	<meta charset="UTF-8">
	<title>Adjacent - Admin</title>
	<style>
		span.message {
			font-weight: 900;
		}
		span.message.success {
			color: #0f0;
		}
		span.message.fail {
			color: red;
		}
		input {
			margin: 12px;
			display: block;
		}
		textarea {
			margin: 0 12px;
			height: 50px;
			width: 300px;
		}
	</style>
</head>
<body>

<h3>Adjacent Admin Dashboard</h3>

<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	require_once('../../db_connect.php');
	$db = connect_db();

	error_reporting(0); //RISKY!!!

	$msg = '';
	$msg_status = 'fail';
	$res;
	$title = isset($_POST['title']) ? mysqli_real_escape_string($db, $_POST['title']) : '';
	$psa = isset($_POST['psa']) ? mysqli_real_escape_string($db, $_POST['psa']) : '';
	$user_id = isset($_POST['user_id']) ? mysqli_real_escape_string($db, $_POST['user_id']) : '';
	
	// restrict to Sal's and Rachel's user_ids
	if(strlen($user_id) && $user_id !== '1' && $user_id !== '2'){
		$msg = 'Only user_id 1 or 2 is valid for the time being.';
		$user_id = '';
	}

	if( isset($_POST['password']) && strlen($_POST['password']) ){
		// check password against server variable
		if(hash("sha256", $_POST['password']) !== hash("sha256", $_SERVER['RDS_PASSWORD'])){
			$msg = 'Incorrect password';
		}
		// check for title
		else if(strlen($title)==0){
			$msg = 'Title cannot be blank';
		}
		// check for psa
		else if(strlen($psa)==0){
			$msg = 'Message cannot be blank';
		}
		// send notification!
		else {
			$counter = 0;

			require_once('../push_notification.php');

			// update badge_count
			$query =	"UPDATE users SET badge_count = badge_count + 1 WHERE user_id = " . $user_id;
			$res = mysqli_query($db, $query);

			$query =	"SELECT * FROM devices WHERE user_id = " . $user_id;
			$res = mysqli_query($db, $query);
			$tokens = array();
			while($row = mysqli_fetch_assoc($res))
				$tokens[] = $row;

			$query =	"SELECT * FROM users where user_id = " . $user_id;
			$res = mysqli_query($db, $query);
			if($res)
				while($row = mysqli_fetch_assoc($res)){
				 	$badge_count = $row['badge_count'];
				 	$user_tokens = array();
				 	foreach($tokens as $key => $token)
				 		if($token['user_id'] == $row['user_id']){
				 			$user_tokens[] = $token['token'];
				 			$counter++;
				 		}
				 	push_notification($title, $psa, $user_tokens, $badge_count);
				 }
		 	// Close free results and close db connection
		  	if($res) mysqli_free_result($res);
		  	mysqli_close($db);
			
			$msg = 'PSA successfully delivered to ' . $counter . ' devices!';
			$msg_status = 'success';
		}
	}

	$output = "";
	
	if(strlen($msg)!==0) $output .= "<span class='message " . $msg_status ."'>" . $msg . "</span><br/>";

	$output .= "Please enter your admin password and the notification that you'd like to send to ALL Adjacent users: <br/>" .
			"<form method='POST'>" .
				"<input type='password' name='password' placeholder='password'>" .
				"<input type='text' name='title' placeholder='Title' maxlength='30' value='" . $title ."'>" .
				"<input type='text' name='user_id' placeholder='User ID' maxlength='3' value='" . $user_id ."'>" .
				"<textarea type='text' name='psa' placeholder='Public Service Announcement' maxlength='100'>" . $psa . "</textarea>" .
				"<input type='submit' value='Submit'>" .
			"</form>";
 
 	echo $output;

?>
	
</body>
</html>