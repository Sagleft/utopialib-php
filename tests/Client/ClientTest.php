<?php
	use Dotenv\Dotenv;
	use UtopiaLib\Client as UtopiaClient;
	use PHPUnit\Framework\TestCase;

	class ClientTest extends TestCase {
		private $api_port  = 22824;
		private $api_host  = '127.0.0.1';
		private $api_token = '';

		public function setUp(): void
		{
			//load data for a test connection
			$dotenv = Dotenv::create(__DIR__ . '/../data/');
			//load environment variables
			$dotenv->load();
			$this->api_port  = getenv('api_port');
			$this->api_host  = 'http://' . getenv('api_host');
			$this->api_token = getenv('api_token');
		}

		/**
			* @Exception \GuzzleHttp\Exception\ConnectException
			* @ExceptionMessage unable to connect to host
		*/
		public function testClientCanConnect() {
			$client = new UtopiaClient(
				$this->api_token,
				$this->api_host,
				$this->api_port
			);
			$client->setDebugMode();
			$response = $client->getSystemInfo();
			$this->assertTrue($response != "");
			return $client;
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetSystemInfo($client) {
			$response = $client->getSystemInfo();
			$this->assertTrue(isset($response['result']));
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientSetProfileStatus($client) {
			$status_success = $client->setProfileStatus("Available", "online");
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetStickerCollections($client) {
			$collection = $client->getStickerCollections();
			$status_success = $collection != [];
			$this->assertTrue($status_success);
			return $collection;
		}

		/**
			@depends testClientCanConnect
			@depends testClientGetStickerCollections
		*/
		public function testClientGetStickersInPack($client, $collection) {
			$stickerPackName = $collection[0];
			$names = $client->getStickerNamesByCollection($stickerPackName);

			$status_success = $names != [] && count($names) > 0;
			$this->assertTrue($status_success);
			return $names;
		}

		/**
			@depends testClientCanConnect
			@depends testClientGetStickerCollections
			@depends testClientGetStickersInPack
		*/
		public function testClientGetStickerImage($client, $collection, $sticker_names) {
			$stickerPackName = $collection[0];
			$sticker_name = $sticker_names[0];

			$sticker_image = $client->getImageSticker($stickerPackName, $sticker_name);
			$status_success = $sticker_image != "";
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetContactsWithFilter($client) {
			//sortBy, offset, limit
			$query_filter = new \UtopiaLib\Filter("nick", 0, 2);
			$contacts_data = $client->getContacts("", $query_filter);

			$status_success = $contacts_data != [];
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetInboxUMails($client) {
			$result = $client->getEmailFolder();
			$status_success = is_array($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetFinanceInfo($client) {
			$result = $client->getFinanceSystemInformation();
			$status_success = $result != [] && isset($result['cardCreatePrice']);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetBalance($client) {
			$result = $client->getBalance();
			$status_success = $result >= 0;
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientGetFinanceHistory($client) {
			$result = $client->getFinanceHistory();
			$status_success = count($result) >= 0;
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsPOSenabled($client) {
			$result = $client->isPOSenabled();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsNetworkEnabled($client) {
			$result = $client->isNetworkEnabled();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsMyPubkeyNotEmpty($client) {
			$result = $client->getMyPubkey();
			$status_success = $result != '';
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsCryptonEngineReady($client) {
			$result = $client->isCryptonEngineReady();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsNATDetectionON($client) {
			$result = $client->isNATDetectionON();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsUPNPDetectionON($client) {
			$result = $client->isUPNPDetectionON();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientIsChannelDatabaseReady($client) {
			$result = $client->isChannelDatabaseReady();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}

		/**
			@depends testClientCanConnect
		*/
		public function testClientCheckConnection($client) {
			$result = $client->checkClientConnection();
			$status_success = is_bool($result);
			$this->assertTrue($status_success);
		}
	}
