<?php
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$token = "3D4F4A5E34706378B4A4541502E603B6";
	$client = new UtopiaLib\Client($token, "http://127.0.0.1", 22824);
	$system_info = $client->getSystemInfo();
	
	if($system_info == []) {
		echo 'last error: ' . $client->error;
	} else {
		print_r($system_info);
	}
	