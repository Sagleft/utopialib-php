<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);

	$result = $client->getMyPubkey();
	if($result != '') {
		echo $result;
	} else {
		echo 'Something went wrong. ' . PHP_EOL;
		echo 'Response: ' . $client->last_response . '. ' . PHP_EOL;
		echo 'Last error: ' . $client->error . '. ' . PHP_EOL;
	}
