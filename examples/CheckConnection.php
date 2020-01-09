<?php
	require_once __DIR__ . '/../vendor/autoload.php';
	
	$token = 'D075C9E46D5F20D10999911B6F10BB0B';
	$client = new UtopiaLib\Client($token, "http://127.0.0.1", 22824);
	//$client->setDebugMode(true);
	
	$result = $client->checkClientConnection();
	if($result) {
		echo 'Everything is OK, connection to the Utopia client is active';
	} else {
		echo 'Something went wrong. Unable to connect to Utopia client. ' . PHP_EOL;
		echo 'Response: ' . $client->last_response . '. ' . PHP_EOL;
		echo 'Last error: ' . $client->error . '. ' . PHP_EOL;
	}
	