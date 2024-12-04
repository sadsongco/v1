<?php

try {
	$db = new PDO('mysql:host=127.0.0.1;dbname=ut_orders;charset=utf8', 'ut_orders', 'cfodkipG53');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
catch (PDOException $e) {
	//db connection failed. Kill everything
	echo "Couldn't connect to the database!";
    echo $e;
	die();
	}

?>