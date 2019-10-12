<?php
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$token = "3D4F4A5E34706378B4A4541502E603B6";
	$client = new UtopiaLib\Client($token, "http://127.0.0.1", 22824);
	print_r($client->getContacts());
	