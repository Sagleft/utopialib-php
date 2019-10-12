<?php
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$token = "3D4F4A5E34706378B4A4541502E603B6";
	$client = new UtopiaLib\Client($token, "http://127.0.0.1", 22824);
	$client->setDebugMode();
	
	$card_id = "0FEE00763E10A3BB";
	$nick = "Sagleft";
	$amount = 50;
	$comment = "donate for utopialib-php";
	$result = $client->sendPayment($card_id, $nick, $amount, $comment);
	
	echo var_dump($result);
	