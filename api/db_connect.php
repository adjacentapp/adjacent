<?php

function connect_db(){
	$db = mysqli_connect("aaso31d4ukbjgw.ch6wu94spsfp.us-west-2.rds.amazonaws.com:3306", "adjacent", "thebeautifulpossible", "adjacent");

	if(mysqli_connect_errno() ) {
		die("DB connection failed: " . 
			mysqli_connect_error() . 
			" (" . mysqli_connect_errno() . ")"
		);
	} else {
		return $db;
	}
}

?>