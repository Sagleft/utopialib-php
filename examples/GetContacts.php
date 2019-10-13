<?php
	require_once __DIR__ . "/../vendor/autoload.php";
	use UtopiaLib;
	
	$token = "3D4F4A5E34706378B4A4541502E603B6";
	$client = new Client($token, "http://127.0.0.1", 22824);
	//filter to select 10 records without offset sorted by date
	$query_filter = new Filter("toDate", 0, 10);
	
	print_r($client->getContacts($query_filter));
	
	//or without filter
	//$client->getContacts();
	