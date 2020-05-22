<?php
	require_once __DIR__ . "/../vendor/autoload.php";

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);
	//$client->setDebugMode();

	$pk = '9BF2B71EA5E8E8150F4B4F4ADF96B6F3C6A2DEB6AE15990ED22712FF2EF3935D';
	$result = $client->isUserMyContact($pk, $subject, $body);

	echo 'last response: ' . json_encode($client->last_response) . PHP_EOL . PHP_EOL;
	echo 'result: ' . var_dump($result) . PHP_EOL;
	echo 'last error: ' . $client->error . PHP_EOL;
