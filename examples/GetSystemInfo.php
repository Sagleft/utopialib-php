<?php
	require_once __DIR__ . "/../vendor/autoload.php";

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);
	$system_info = $client->getSystemInfo();

	if($system_info == []) {
		echo 'last error: ' . $client->error;
	} else {
		print_r($system_info);
	}
