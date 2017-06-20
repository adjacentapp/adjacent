<?php
	require_once('../db_connect.php');
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *');


	$errors = array();

	// mysqli_real_escape_string( $_GET['hum'] )
	$hum = isset($_GET['hum']) ? $_GET['hum'] : '0';
	$soc = isset($_GET['soc']) ? $_GET['soc'] : '0';
	$nat = isset($_GET['nat']) ? $_GET['nat'] : '0';
	$form = isset($_GET['form']) ? $_GET['form'] : '0';
	$prof = isset($_GET['prof']) ? $_GET['prof'] : '0';


	$db = connect_db();
 	$query =	"SELECT * FROM cards " .
 				"WHERE hum >= " . $hum .
 				" AND soc >= " . $soc .
 				" AND nat >= " . $nat .
 				" AND form >= " . $form .
 				" AND prof >= " . $prof;
 	$res = mysqli_query($db, $query);

 	if(!$res)
 		$errors['INVALID QUERY'] = mysqli_error();





		$filters = array();
		if( isset($_GET['hum']) )
			$filters['HUM'] = 'humanities >= ' . $_GET['hum'];
		if( isset($_GET['soc']) )
			$filters['SOC'] = 'social sciences >= ' . $_GET['soc'];
		if( isset($_GET['NAT']) )
			$filters['NAT'] = 'natural sciences >= ' . $_GET['nat'];
		if( isset($_GET['form']) )
			$filters['FORM'] = 'formal sciences >= ' . $_GET['form'];
		if( isset($_GET['prof']) )
			$filters['PROF'] = 'professions >= ' . $_GET['prof'];


		$cards = array();
 		while($row = mysqli_fetch_assoc($res)) {
 			$cards[] = $row;
 		}
 		

 		$output = array(
			'errors' => $errors,
			'query' => $query,
			'filters' => $filters,
			'cards' => $cards,
		);


	 	mysqli_free_result($res);
	 	mysqli_close($db);

	 	exit(json_encode($output, JSON_PRETTY_PRINT));

?>