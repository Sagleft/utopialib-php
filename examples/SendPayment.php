<?php
	require_once __DIR__ . "/../vendor/autoload.php";

	new TestEnvironment();

	$client = new UtopiaLib\Client(
		getenv('utopia_token'),
		getenv('utopia_host'),
		getenv('utopia_port')
	);
	$client->setDebugMode();

	$card_id = "0FEE00763E10A3BB";
	$nick = "Sagleft";
	$amount = 50;
	$comment = "donate for utopialib-php";
	$result = $client->sendPayment($card_id, $nick, $amount, $comment);

	echo var_dump($result);
