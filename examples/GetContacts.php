<?php
	require_once __DIR__ . "/../vendor/autoload.php";
	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);
	
	//filter to select 10 records without offset sorted by date
	$query_filter = new Filter("toDate", 0, 10);

	print_r($client->getContacts($query_filter));

	//or without filter
	//$client->getContacts();
