<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	//data for connecting to the client are indicated in the file ../.env
	//view ../README.md
	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);

	$result = $client->checkClientConnection();
	if($result) {
		echo 'Everything is OK, connection to the Utopia client is active';
	} else {
		echo 'Something went wrong. Unable to connect to Utopia client. ' . PHP_EOL;
		echo 'Response: ' . $client->last_response . '. ' . PHP_EOL;
		echo 'Last error: ' . $client->error . '. ' . PHP_EOL;
	}
