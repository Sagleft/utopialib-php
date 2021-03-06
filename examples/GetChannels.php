<?php
	require_once __DIR__ . "/../vendor/autoload.php";

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);
	
	//filter to select 10 records without offset
	$query_filter = new UtopiaLib\Filter('', '', 10);

	print_r($client->getChannels($query_filter));
