<?php
	require_once __DIR__ . "/../vendor/autoload.php";

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);

	$result = $client->addCard("BLK", "#000000");
	if($result) {
		echo "black crypto card successfully created";
	} else {
		echo "failed to create crypto card";
	}
