<?php 
	//database server
	define('db_server', 'https://csci440.cs.montana.edu/');

	//user, password, and database variables
	$db_user = 'fromelt';
	$db_password = 'atC4D?ZBkCT8';    
	$db_dbname = 'ratemycourse';
		
	$db = mysql_connect(db_server, $db_user, $db_password);
	if (!$db) {
		die('Could Not Connect: ' . mysql_error());
	} else {
		echo"Connected Successfully";
	}	
	//select database name
	mysql_select_db($db_dbname);
?>