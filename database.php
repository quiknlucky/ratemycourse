<?php 
	//database server
	define('db_server', 'localhost');

	//user, password, and database variables
	$db_user = 'rmc_user';
	$db_password = 'Ui0MT8Dnj9HN';    
	$db_dbname = 'ratemycourse';
		
	$db = mysqli_connect(db_server, $db_user, $db_password);
	if (!$db) {
		die('Could Not Connect: ' . mysql_error());
	}
	//select database name
	mysqli_select_db($db, $db_dbname);
?>