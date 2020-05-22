<?php
	require_once __DIR__ . "/../vendor/autoload.php";

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);

	$result = $client->addCard("GOLD", "#ffd700");
	if($result) {
		echo "gold crypto card successfully created";
	} else {
		echo "failed to create crypto card";
	}
