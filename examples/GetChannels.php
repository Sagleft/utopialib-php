<?php
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$token = "31229B57C738EBFE69461177C31E31D1";
	$client = new UtopiaLib\Client($token, "http://127.0.0.1", 22824);
	//filter to select 10 records without offset
	$query_filter = new UtopiaLib\Filter('', '', 10);
	
	print_r($client->getChannels($query_filter));
	