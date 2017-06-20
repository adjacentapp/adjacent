<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');
	header('Content-Type: application/json');
	require_once('../../db_connect.php');
	$db = connect_db();

	$chunk = isset($_GET['chunk']) ? mysqli_real_escape_string($db, $_GET['chunk']) : 'month';
	$end = isset($_GET['end']) ? mysqli_real_escape_string($db, $_GET['end']) : 'now';
	$range = isset($_GET['range']) ? mysqli_real_escape_string($db, $_GET['range']) : 'all';
	$summary = isset($_GET['summary']) && $_GET['summary'] == 'false' ? false : true;
	$details = isset($_GET['details']) && $_GET['details'] == 'false' ? false : true;
	$details_content = isset($_GET['details']) && $_GET['details'] == 'true' ? true : false;
	$content = isset($_GET['content']) && $_GET['content'] == 'true' ? true : false;
	$params = isset($_GET['params']) && $_GET['params'] == 'false' ? false : true;
	$log = isset($_GET['log']) && $_GET['log'] == 'true' ? true : false;

	$month_names = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
	$chunk_multiples = array(
		'day' => (60*60*24),
		'month' => (60*60*24*29.69),
		'year' => (60*60*24*365.25)
	);
	$start_time = time();
	$output = array();

	if($params)
		$output[] = array(
			'parameters' => array(
				'example' => 'http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/v2/dashboard/analytics.php?chunk=month&range=6&params=false',
				'chunk' => ['day','month','year'],
				'end' => 'now',
				'range' => '30',
				'summary' => 'show summary (default true)',
				'details' => 'show chunk details (default true)',
				'content' => 'show results content (default false)',
				'params' => 'show parameter options (default true)',
				'log' => 'show change log (default false)',
			)
		);

	if($log)
		$output[] = array(
			'log' => array(
				'5/31/17' => 'Created analytics portal. TUNE THE ENGINE!'
			)
		);

	// New users
	$query =	"SELECT * FROM users ORDER BY created_at ASC LIMIT 1";
	$res = mysqli_query($db, $query);
	$adjusted_range = $range;
	while($row = mysqli_fetch_assoc($res))
		$adjusted_range = $row['created_at'];
	$chunks_old = floor( ($start_time - strtotime($adjusted_range)) / $chunk_multiples[$chunk] );
	$adjusted_range = ($range == 'all' || $range > $chunks_old) ? $chunks_old : $range;
	$query =	"SELECT email, created_at, verified FROM users" .
				" WHERE created_at > now() - INTERVAL $adjusted_range $chunk";
	$res = mysqli_query($db, $query);
	$output['signups']['summary'] = array(
		"description" => "New users created in the last $adjusted_range {$chunk}s",
		"query" => $query,
		"count" => mysqli_num_rows($res),
		"average" => (mysqli_num_rows($res)/$adjusted_range) . "/{$chunk}"
	);
	if($content)
		while($row = mysqli_fetch_assoc($res))
			$output['signups']['content'][] = $row;
	if($details){
		for($i=$adjusted_range; $i>0; $i--){
			$j = $i - 1;
			$query =	"SELECT email, created_at, verified FROM users" .
				" WHERE created_at >= now() - INTERVAL $i $chunk" .
				" AND created_at < now() - INTERVAL $j $chunk";
			$res = mysqli_query($db, $query);
			if($res){
				$month = '';
				while($row = mysqli_fetch_assoc($res)){
					$month = $month_names[date_parse($row['created_at'])['month']-1] .
					'-' . date_parse($row['created_at'])['year'];
				}
				$output['signups']['details'][$month]['summary'] = array(
					// "description" => "New users created in $month",
					// "query" => $query,
					"count" => mysqli_num_rows($res),
					// "average" => (mysqli_num_rows($res)/$range) . "/{$chunk}"
				);
				if($details_content){
					mysqli_data_seek($res, 0);
					while($row = mysqli_fetch_assoc($res)){
						$month = $month_names[date_parse($row['created_at'])['month']-1] .
						'-' . date_parse($row['created_at'])['year'];
						$output['signups']['details'][$month]['content'][] = $row;
					}
				}
			}
		}
	}

	// Comments
	$query =	"SELECT * FROM card_walls ORDER BY timestamp ASC LIMIT 1";
	$res = mysqli_query($db, $query);
	$adjusted_range = $range;
	while($row = mysqli_fetch_assoc($res))
		$adjusted_range = $row['timestamp'];
	$chunks_old = floor( ($start_time - strtotime($adjusted_range)) / $chunk_multiples[$chunk] );
	$adjusted_range = ($range == 'all' || $range > $chunks_old) ? $chunks_old : $range;
	$query =	"SELECT * FROM card_walls" .
				" WHERE timestamp > now() - INTERVAL $adjusted_range $chunk";
	$res = mysqli_query($db, $query);
	$output['comments']['summary'] = array(
		"description" => "New comments posted in the last $adjusted_range {$chunk}s",
		"query" => $query,
		"count" => mysqli_num_rows($res),
		"average" => (mysqli_num_rows($res)/$adjusted_range) . "/{$chunk}"
	);
	if($content)
		while($row = mysqli_fetch_assoc($res))
			$output['comments']['content'][] = $row;
	if($details){
		for($i=$adjusted_range; $i>0; $i--){
			$j = $i - 1;
			$query =	"SELECT * FROM card_walls" .
				" WHERE timestamp >= now() - INTERVAL $i $chunk" .
				" AND timestamp < now() - INTERVAL $j $chunk";
			$res = mysqli_query($db, $query);
			$month = '';
			if($res){
				while($row = mysqli_fetch_assoc($res)){
					$month = $month_names[date_parse($row['timestamp'])['month']-1] .
					'-' . date_parse($row['timestamp'])['year'];
				}
				$output['comments']['details'][$month]['summary'] = array(
					"count" => mysqli_num_rows($res),
				);
				if($details_content){
					mysqli_data_seek($res, 0);
					while($row = mysqli_fetch_assoc($res)){
						$month = $month_names[date_parse($row['timestamp'])['month']-1] .
					'-' . date_parse($row['timestamp'])['year'];
						$output['comments']['details'][$month]['content'][] = $row;
					}
				}
			}
		}
	}

	// Comment votes
	$query =	"SELECT * FROM wall_post_likes ORDER BY time ASC LIMIT 1";
	$res = mysqli_query($db, $query);
	$adjusted_range = $range;
	while($row = mysqli_fetch_assoc($res))
		$adjusted_range = $row['time'];
	$chunks_old = floor( ($start_time - strtotime($adjusted_range)) / $chunk_multiples[$chunk] );
	$adjusted_range = ($range == 'all' || $range > $chunks_old) ? $chunks_old : $range;
	$query =	"SELECT * FROM wall_post_likes" .
				" WHERE time > now() - INTERVAL $adjusted_range $chunk";
	$res = mysqli_query($db, $query);
	$output['votes']['summary'] = array(
		"description" => "New comment votes in the last $adjusted_range {$chunk}s",
		"query" => $query,
		"count" => mysqli_num_rows($res),
		"average" => (mysqli_num_rows($res)/$adjusted_range) . "/{$chunk}"
	);
	if($content)
		while($row = mysqli_fetch_assoc($res))
			$output['votes']['content'][] = $row;
	if($details){
		for($i=$adjusted_range; $i>0; $i--){
			$j = $i - 1;
			$query =	"SELECT * FROM wall_post_likes" .
				" WHERE time >= now() - INTERVAL $i $chunk" .
				" AND time < now() - INTERVAL $j $chunk";
			$res = mysqli_query($db, $query);
			if($res){
				$month = '';
				while($row = mysqli_fetch_assoc($res)){
					$month = $month_names[date_parse($row['time'])['month']-1] .
					'-' . date_parse($row['time'])['year'];
				}
				$output['votes']['details'][$month]['summary'] = array(
					"count" => mysqli_num_rows($res),
				);
				if($details_content){
					mysqli_data_seek($res, 0);
					while($row = mysqli_fetch_assoc($res)){
						$month = $month_names[date_parse($row['time'])['month']-1] .
						'-' . date_parse($row['time'])['year'];
						$output['votes']['details'][$month]['content'][] = $row;
					}
				}
			}
		}
	}

	// New cards
	$query =	"SELECT * FROM cards ORDER BY create_time ASC LIMIT 1";
	$res = mysqli_query($db, $query);
	$adjusted_range = $range;
	while($row = mysqli_fetch_assoc($res))
		$adjusted_range = $row['create_time'];
	$chunks_old = floor( ($start_time - strtotime($adjusted_range)) / $chunk_multiples[$chunk] );
	$adjusted_range = ($range == 'all' || $range > $chunks_old) ? $chunks_old : $range;
	$query =	"SELECT * FROM cards" .
				" WHERE create_time > now() - INTERVAL $adjusted_range $chunk";
	$res = mysqli_query($db, $query);
	$output['cards']['summary'] = array(
		"description" => "New cards created in the last $adjusted_range {$chunk}s",
		"query" => $query,
		"count" => mysqli_num_rows($res),
		"average" => (mysqli_num_rows($res)/$adjusted_range) . "/{$chunk}"
	);
	if($content)
		while($row = mysqli_fetch_assoc($res))
			$output['cards']['content'][] = $row;
	if($details){
		for($i=$adjusted_range; $i>0; $i--){
			$j = $i - 1;
			$query =	"SELECT * FROM cards" .
				" WHERE create_time >= now() - INTERVAL $i $chunk" .
				" AND create_time < now() - INTERVAL $j $chunk";
			$res = mysqli_query($db, $query);
			if($res){
				$month = '';
				while($row = mysqli_fetch_assoc($res)){
					$month = $month_names[date_parse($row['create_time'])['month']-1] .
					'-' . date_parse($row['create_time'])['year'];
				}
				$output['cards']['details'][$month]['summary'] = array(
					"count" => mysqli_num_rows($res),
				);
				if($details_content){
					mysqli_data_seek($res, 0);
					while($row = mysqli_fetch_assoc($res)){
						$month = $month_names[date_parse($row['create_time'])['month']-1] .
					'-' . date_parse($row['create_time'])['year'];
						$output['cards']['details'][$month]['content'][] = $row;
					}
				}
			}
		}
	}

	// New bookmarks
	$query =	"SELECT * FROM bookmarks ORDER BY time ASC LIMIT 1";
	$res = mysqli_query($db, $query);
	$adjusted_range = $range;
	while($row = mysqli_fetch_assoc($res))
		$adjusted_range = $row['time'];
	$chunks_old = floor( ($start_time - strtotime($adjusted_range)) / $chunk_multiples[$chunk] );
	$adjusted_range = ($range == 'all' || $range > $chunks_old) ? $chunks_old : $range;
	$query =	"SELECT * FROM bookmarks" .
				" WHERE time > now() - INTERVAL $adjusted_range $chunk";
	$res = mysqli_query($db, $query);
	$output['bookmarks']['summary'] = array(
		"description" => "New bookmarks created in the last $adjusted_range {$chunk}s",
		"query" => $query,
		"count" => mysqli_num_rows($res),
		"average" => (mysqli_num_rows($res)/$adjusted_range) . "/{$chunk}"
	);
	if($content)
		while($row = mysqli_fetch_assoc($res))
			$output['bookmarks']['content'][] = $row;
	if($details){
		for($i=$adjusted_range; $i>0; $i--){
			$j = $i - 1;
			$query =	"SELECT * FROM bookmarks" .
				" WHERE time >= now() - INTERVAL $i $chunk" .
				" AND time < now() - INTERVAL $j $chunk";
			$res = mysqli_query($db, $query);
			if($res){
				$month = '';
				while($row = mysqli_fetch_assoc($res)){
					$month = $month_names[date_parse($row['time'])['month']-1] .
					'-' . date_parse($row['time'])['year'];
				}
				$output['bookmarks']['details'][$month]['summary'] = array(
					"count" => mysqli_num_rows($res),
				);
				if($details_content){
					mysqli_data_seek($res, 0);
					while($row = mysqli_fetch_assoc($res)){
						$month = $month_names[date_parse($row['time'])['month']-1] .
					'-' . date_parse($row['time'])['year'];
						$output['bookmarks']['details'][$month]['content'][] = $row;
					}
				}
			}
		}
	}


	if($res) mysqli_free_result($res);
 	mysqli_close($db);
 	exit( json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) );
?>