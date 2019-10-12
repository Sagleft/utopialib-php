<?php
	namespace UtopiaLib;
	
	class Client implements ClientInterface {
		public $error = ""; //last error
		
		private $api_port    = 22659;
		private $api_host    = "";
		private $api_token   = "";
		private $api_version = "1.0";
		private $client = null; //Graze\GuzzleHttp\JsonRpc\Client
		private $is_debug = false;
		
		public function __construct($token = "", $host = "http://127.0.0.1", $port = 22659) {
			$this->api_token = $token;
			$this->api_port  = $port;
			$this->api_host  = $host;
			$this->init();
		}
		
		private function getApiUrl() {
			return $this->api_host . ":" . $this->api_port . "/api/" . $this->api_version;
		}
		
		private function api_query($method = "getSystemInfo", $params = [], $filter = null): array {
			//filter - \Utopia\Filter object
			$this->error = "";
			$query_body = [
				'method' => $method,
				'params' => $params,
				'token'  => $this->api_token
			];
			if($filter != null) {
				$query_body['filter'] = [
					'sortBy' => $filter->sortBy,
					'offset' => $filter->offset,
					'limit'  => $filter->limit,
				];
			}
			$response = $this->guzzleQuery($this->getApiUrl(), $query_body);
			
			if(! Utilities::isJson($response)) {
				return [];
			}
			if($response == "") {
				return [];
			}
			return json_decode($response, true);
		}
		
		private function guzzleQuery($url, $query_body) {
			$response = $this->client->request("POST", $url, [
				'json' => $query_body
			]);
			return $response->getBody()->getContents();
		}
		
		private function init() {
			$this->client = new \GuzzleHttp\Client([
				'timeout' => 2.0
			]);
		}
		
		private function checkResultContains($response = []): bool {
			if(!isset($response['result'])) {
				if(isset($response['error'])) {
					$this->error = $response['error'];
				}
				$this->error = "the 'result' key was not found in the response";
				if($this->is_debug) {
					throw new \RuntimeException($this->error);
				}
				return false;
			}
			return true;
		}
		
		private function checkResultBool($value): bool {
			if(!is_bool($value)) {
				$this->last_error = "'result' contains " . gettype($value) . " expected bool";
				if($this->is_debug) {
					throw new \RuntimeException($this->last_error);
				}
				return false;
			}
			return true;
		}
		
		public function setDebugMode($is_debug = true) {
			$this->is_debug = $is_debug;
		}
		
		public function getSystemInfo(): array {
			return $this->api_query("getSystemInfo");
		}
		
		public function getProfileStatus(): array {
			return $this->api_query("getProfileStatus");
		}
		
		public function setProfileStatus($status = "Available", $mood = ""): bool {
			switch($status) {
				default:
					$status = "Available"; break;
				case 'Away': break;
				case 'DoNotDisturb': break;
				case 'Invisible': break;
				case 'Offline': break;
			}
			$params = [
				'status' => $status
			];
			if($mood != "") {
				$params['mood'] = $mood;
			}
			$response = $this->api_query("setProfileStatus", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			if(! $this->checkResultBool($response['result'])) {
				return false;
			}
			return $response['result'];
		}
		
		public function getOwnContact(): array {
			return $this->api_query("getOwnContact");
		}
		
		public function getContacts($search_filter = "", $query_filter = null): array {
			$params = [
				'filter' => $search_filter
			];
			$response = $this->api_query("getContacts", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}
		
		public function getContactAvatar($pk = "", $coder = "BASE64", $format = "PNG"): string {
			switch($coder) {
				default:
					$coder = "BASE64"; break;
				case 'BASE64': break;
				case 'HEX': break;
			}
			switch($format) {
				default:
					$format = "JPG"; break;
				case 'JPG': break;
				case 'PNG': break;
			}
			$params = [
				'pk'     => $pk,
				'coder'  => $coder,
				'format' => $format
			];
			$response = $this->api_query("getContactAvatar", $params);
			if(! $this->checkResultContains($response)) {
				return "";
			}
			return $response['result'];
		}
		
		public function getChannelAvatar($channelid = "", $coder = "BASE64", $format = "PNG"): string {
			switch($coder) {
				default:
					$coder = "BASE64"; break;
				case 'BASE64': break;
				case 'HEX': break;
			}
			switch($format) {
				default:
					$format = "JPG"; break;
				case 'JPG': break;
				case 'PNG': break;
			}
			$params = [
				'channelid' => $channelid,
				'coder'     => $coder,
				'format'    => $format
			];
			$response = $this->api_query("getChannelAvatar", $params);
			if(! $this->checkResultContains($response)) {
				return "";
			}
			return $response['result'];
		}
		
		public function setContactGroup($pk = "", $groupName = ""): bool {
			$params = [
				'pk'        => $pk,
				'groupName' => $groupName
			];
			$response = $this->api_query("setContactGroup", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			if(! $this->checkResultBool($response['result'])) {
				return false;
			}
			return $response['result'];
		}
		
		public function setContactNick($pk = "", $newNick = ""): bool {
			$params = [
				'pk'      => $pk,
				'newNick' => $newNick
			];
			$response = $this->api_query("setContactNick", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			if(! $this->checkResultBool($response['result'])) {
				return false;
			}
			return $response['result'];
		}
		
		public function sendInstantMessage($pkOrNick = "", $message = "test message"): int {
			$params = [
				'to'   => $pkOrNick,
				'text' => $message
			];
			$response = $this->api_query("sendInstantMessage", $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			$result = $response['result'];
			if(!is_int($result)) {
				$this->last_error = "Invalid message ID received";
				if($this->is_debug) {
					throw new \UnexpectedValueException($this->last_error);
				}
				return 0;
			}
			return $result;
		}
		
		public function sendInstantQuote($pkOrNick = "", $text = "instant quoute", $id_message = 232): int {
			$params = [
				'to'         => $pkOrNick,
				'text'       => $text,
				'id_message' => $id_message
			];
			$response = $this->api_query("sendInstantQuote", $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			if(!is_int($result)) {
				$this->last_error = "Invalid message ID received";
				if($this->is_debug) {
					throw new \UnexpectedValueException($this->last_error);
				}
				return 0;
			}
			return $result;
		}
		
		public function sendInstantSticker($pkOrNick = "", $collection = "434", $name = "343"): int {
			$params = [
				'to'         => $pkOrNick,
				'collection' => $collection,
				'name'       => $name
			];
			$response = $this->api_query("sendInstantSticker", $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			if(!is_int($result)) {
				$this->last_error = "Invalid message ID received";
				if($this->is_debug) {
					throw new \UnexpectedValueException($this->last_error);
				}
				return 0;
			}
			return $result;
		}
		
		public function getStickerCollections(): array {
			$response = $this->api_query("getStickerCollections");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}
		
		public function getStickerNamesByCollection($collection_name = "Default Stickers"): array {
			$params = [
				'collection_name' => $collection_name
			];
			$response = $this->api_query("getStickerNamesByCollection", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}
		
		public function getImageSticker($collection_name = "Default Stickers", $sticker_name = "airship", $coder = "BASE64"): string {
			switch($coder) {
				default:
					$coder = "BASE64"; break;
				case 'BASE64': break;
				case 'HEX': break;
			}
			$params = [
				'collection_name' => $collection_name,
				'sticker_name'    => $sticker_name,
				'coder'           => $coder
			];
			$response = $this->api_query("getImageSticker", $params);
			if(! $this->checkResultContains($response)) {
				return "";
			}
			return $response['result'];
		}
		
		public function sendInstantBuzz($pkOrNick = "", $comments = "test"): int {
			$params = [
				'to'       => $pkOrNick,
				'comments' => $comments
			];
			$response = $this->api_query("sendInstantBuzz", $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}
		
		public function sendInstantInvitation($pkOrNick, $channelid, $description, $comments): int {
			$params = [
				'to'          => $pkOrNick,
				'channelid'   => $channelid,
				'description' => $description,
				'comments'    => $comments
			];
			$response = $this->api_query("sendInstantInvitation", $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}
		
		public function removeInstantMessages($pk = ""): bool {
			$params = [
				'hex_contact_public_key' => $pkOrNick
			];
			$response = $this->api_query("sendInstantInvitation", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}
		
		public function getContactMessages($pk, $query_filter = null): array {
			$params = [
				'pk' => $pk
			];
			$response = $this->api_query("getContactMessages", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}
		
		public function sendEmailMessage($pkOrNick, $subject = "test message", $body = "message content"): bool {
			$params = [
				'pk'      => $pkOrNick,
				'subject' => $subject,
				'body'    => $body
			];
			$response = $this->api_query("sendEmailMessage", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}
		
		public function sendPayment($cardid = "", $pkOrNick = "", $amount = 1, $comment = "", $fromCard = ""): string {
			$params = [
				'cardid'  => $cardid,
				'to'      => $pkOrNick,
				'amount'  => $amount
			];
			if($fromCard != "") {
				$params['fromCard'] = $fromCard;
			}
			if($comment != "") {
				$params['comment'] = $comment;
			}
			$response = $this->api_query("sendPayment", $params);
			if(! $this->checkResultContains($response)) {
				return "";
			}
			return $response['result'];
		}
		
		public function getEmailFolder($folderType = 1, $search_filter = "", $query_filter = null): array {
			$params = [
				'folderType' => Utilities::filterFolderType($folderType),
				'filter'     => $search_filter
			];
			$response = $this->api_query("getEmailFolder", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}
		
		public function getEmails($folderType = 1, $search_filter = "", $query_filter = null): array {
			$params = [
				'folderType' => Utilities::filterFolderType($folderType),
				'filter'     => $search_filter
			];
			$response = $this->api_query("getEmails", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}
	}
	