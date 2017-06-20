<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	// header('Content-Type: application/json');
	require_once('db_connect.php');

	$password = 'keepitsecret2313';
	$pw = md5('longtokencode'.$password);
	echo $pw;

?>