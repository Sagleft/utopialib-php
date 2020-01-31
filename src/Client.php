<?php
	namespace UtopiaLib;

	class Client implements ClientInterface {
		public $error = ''; //last error
		public $last_response = ''; //for debug

		private $api_port    = 22659;
		private $api_host    = '';
		private $api_token   = '';
		private $api_version = '1.0';
		private $client = null; //Graze\GuzzleHttp\JsonRpc\Client
		private $is_debug = false;

		public function __construct($token = '', $host = 'http://127.0.0.1', $port = 22659) {
			$this->api_token = $token;
			$this->api_port  = $port;
			$this->api_host  = $host;
			$this->init();
		}

		private function getApiUrl() {
			return $this->api_host . ':' . $this->api_port . '/api/' . $this->api_version;
		}

		private function api_query($method = "getSystemInfo", $params = [], $filter = null): array {
			//filter - \Utopia\Filter object
			$this->error = '';
			$query_body = [
				'method' => $method,
				'params' => $params,
				'token'  => $this->api_token
			];
			if($filter != null) {
				$query_body['filter'] = [
					'sortBy' => $filter->sortBy,
					'offset' => $filter->offset,
					'limit'  => $filter->limit
				];
			}
			try {
				$response = $this->guzzleQuery($this->getApiUrl(), $query_body);
				$this->last_response = $response;
			} catch(\GuzzleHttp\Exception\ClientException $ex) {
				$response = '';
				$this->error = $ex->getMessage();
				return [];
			} catch(\GuzzleHttp\Exception\ConnectException $ex) {
				$response = '';
				$this->error = 'failed to connect to Utopia client, timeout';
				return [];
			}

			if(! Utilities::isJson($response)) {
				return [];
			}
			if($response == '') {
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
				//$this->last_response = $response;
				if($this->is_debug) {
					throw new \RuntimeException($this->error);
				}
				return false;
			}
			return true;
		}

		private function checkResultBool($value): bool {
			if(!is_bool($value)) {
				$this->error = "'result' contains " . gettype($value) . " expected bool";
				if($this->is_debug) {
					throw new \RuntimeException($this->error);
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

		public function setProfileStatus($status = 'Available', $mood = ''): bool {
			switch($status) {
				default:
					$status = 'Available'; break;
				case 'Away': break;
				case 'DoNotDisturb': break;
				case 'Invisible': break;
				case 'Offline': break;
			}
			$params = [
				'status' => $status
			];
			if($mood != '') {
				$params['mood'] = $mood;
			}
			$response = $this->api_query('setProfileStatus', $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			if(! $this->checkResultBool($response['result'])) {
				return false;
			}
			return $response['result'];
		}

		public function getOwnContact(): array {
			$response = $this->api_query('getOwnContact');
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getContacts($search_filter = '', $query_filter = null): array {
			$params = [
				'filter' => $search_filter
			];
			$response = $this->api_query('getContacts', $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getContactAvatar($pk = '', $coder = 'BASE64', $format = 'PNG'): string {
			if(!ctype_xdigit($channelid)) {
				$this->error = 'the public key must be in hexadecimal representation';
				return '';
			}
			switch($coder) {
				default:
					$coder = 'BASE64'; break;
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
			$response = $this->api_query('getContactAvatar', $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function getChannelAvatar($channelid = '', $coder = 'BASE64', $format = 'PNG'): string {
			if(!ctype_xdigit($channelid)) {
				$this->error = 'channelid must be in hexadecimal representation';
				return false;
			}
			switch($coder) {
				default:
					$coder = 'BASE64'; break;
				case 'BASE64': break;
				case 'HEX': break;
			}
			switch($format) {
				default:
					$format = 'JPG'; break;
				case 'JPG': break;
				case 'PNG': break;
			}
			$params = [
				'channelid' => $channelid,
				'coder'     => $coder,
				'format'    => $format
			];
			$response = $this->api_query('getChannelAvatar', $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function setContactGroup($pk = '', $groupName = ''): bool {
			if(!ctype_xdigit($pk)) {
				$this->error = 'the public key must be in hexadecimal representation';
				return false;
			}
			$params = [
				'contactPublicKey' => $pk,
				'groupName'        => $groupName
			];
			$response = $this->api_query('setContactGroup', $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			if(! $this->checkResultBool($response['result'])) {
				return false;
			}
			return $response['result'];
		}

		public function setContactNick($pk = '', $newNick = ''): bool {
			if(!ctype_xdigit($pk)) {
				$this->error = 'the public key must be in hexadecimal representation';
				return false;
			}
			$params = [
				'contactPublicKey' => $pk,
				'newNick'          => $newNick
			];
			$response = $this->api_query('setContactNick', $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			if(! $this->checkResultBool($response['result'])) {
				return false;
			}
			return $response['result'];
		}

		public function sendInstantMessage($pkOrNick = '', $message = 'test message'): int {
			$params = [
				'to'   => $pkOrNick,
				'text' => $message
			];
			$response = $this->api_query('sendInstantMessage', $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			$result = $response['result'];
			if(!is_int($result)) {
				$this->error = 'Invalid message ID received';
				if($this->is_debug) {
					throw new \UnexpectedValueException($this->error);
				}
				return 0;
			}
			return $result;
		}

		public function sendInstantQuote($pkOrNick = '', $text = 'instant quoute', $id_message = 232): int {
			$params = [
				'to'         => $pkOrNick,
				'text'       => $text,
				'id_message' => $id_message
			];
			$response = $this->api_query('sendInstantQuote', $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			if(!is_int($result)) {
				$this->error = 'Invalid message ID received';
				if($this->is_debug) {
					throw new \UnexpectedValueException($this->error);
				}
				return 0;
			}
			return $result;
		}

		public function sendInstantSticker($pkOrNick = '', $collection = '434', $name = '343'): int {
			$params = [
				'to'         => $pkOrNick,
				'collection' => $collection,
				'name'       => $name
			];
			$response = $this->api_query('sendInstantSticker', $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			if(!is_int($result)) {
				$this->error = 'Invalid message ID received';
				if($this->is_debug) {
					throw new \UnexpectedValueException($this->error);
				}
				return 0;
			}
			return $result;
		}

		public function getStickerCollections(): array {
			$response = $this->api_query('getStickerCollections');
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getStickerNamesByCollection($collection_name = 'Default Stickers'): array {
			$params = [
				'collection_name' => $collection_name
			];
			$response = $this->api_query('getStickerNamesByCollection', $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getImageSticker($collection_name = "Default Stickers", $sticker_name = "airship", $coder = 'BASE64'): string {
			switch($coder) {
				default:
					$coder = 'BASE64'; break;
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
				return '';
			}
			return $response['result'];
		}

		public function sendInstantBuzz($pkOrNick = '', $comments = "test"): int {
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

		public function removeInstantMessages($pk = ''): bool {
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

		public function sendEmailMessage($pkOrNick = '', $subject = 'test message', $body = 'message content'): bool {
			if($pkOrNick == '') {
				$this->error = 'empty pubkey given for sendEmailMessage method';
				return false;
			}
      
			$params = [
				'to'      => [$pkOrNick],
				'subject' => $subject,
				'body'    => $body
			];
			$response = $this->api_query("sendEmailMessage", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function sendManyEmailMessages($pk_arr = [], $subject = "test message", $body = "message content"): bool {
			$params = [
				'to'      => $pk_arr,
				'subject' => $subject,
				'body'    => $body
			];
			$response = $this->api_query("sendEmailMessage", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function sendPayment($cardid = '', $pkOrNick = '', $amount = 1, $comment = '', $fromCard = ''): string {
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
				return '';
			}
			return $response['result'];
		}

		public function getEmailFolder($folderType = 1, $search_filter = '', $query_filter = null): array {
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

		public function getEmails($folderType = 1, $search_filter = '', $query_filter = null): array {
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

		public function getEmailById($id = 33): array {
			$params = [
				'id' => $id
			];
			$response = $this->api_query("getEmailById", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function deleteEmail($id = 33): bool {
			$params = [
				'id' => $id
			];
			$response = $this->api_query("deleteEmail", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function sendReplyEmailMessage($id = 33, $body = "my message", $subject = "uMail subject"): bool {
			$params = [
				'id'      => $id,
				'body'    => $body,
				'subject' => $subject
			];
			$response = $this->api_query("sendReplyEmailMessage", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function sendForwardEmailMessage($id = 33, $pkOrNick = '', $body = "my message", $subject = "uMail subject"): bool {
			$params = [
				'id'      => $id,
				'to'      => $pkOrNick,
				'body'    => $body,
				'subject' => $subject
			];
			$response = $this->api_query("sendForwardEmailMessage", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getFinanceSystemInformation(): array {
			$response = $this->api_query("getFinanceSystemInformation");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getBalance(): float {
			$response = $this->api_query("getBalance");
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}

		public function getFinanceHistory($filters = "ALL_TRANSFERS", $referenceNumber = '', $toDate = '', $fromDate = '', $batchId = '', $fromAmount = '', $toAmount = '', $query_filter = null): array {
			$filters = Utilities::parseFinanceQueryFilters($filters);
			$params = [
				'filters'         => $filters,
				'referenceNumber' => $referenceNumber,
				'toDate'          => $toDate,
				'fromDate'        => $fromDate,
				'batchId'         => $batchId,
				'fromAmount'      => $fromAmount,
				'toAmount'        => $toAmount
			];
			$response = $this->api_query("getFinanceHistory", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getCards(): array {
			$response = $this->api_query("getCards");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function addCard($name = "new card", $color = "#FFFFFF", $numbers = "0000"): string {
			$color = Utilities::filterHEXColor($color);
			$params = [
				'color'                => $color,
				'name'                 => $name,
				'preorderNumberInCard' => $numbers
			];
			//TODO: filter $numbers
			$response = $this->api_query("addCard", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function deleteCard($cardId = ''): bool {
			if(!ctype_xdigit($cardId)) {
				$this->error = "the cardId must be in hexadecimal representation";
				return false;
			}
			$params = [
				'cardId' => $cardId
			];
			$response = $this->api_query("deleteCard", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function enableMining($enabled = true): bool {
			if(!is_bool($enabled)) {
				$this->error = "expected boolean parameter (enableMining method)";
				return false;
			}
			$params = [
				'enabled' => $enabled
			];
			$response = $this->api_query("enableMining", $params);
			//exit(json_encode($response));
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function enableInterest($enabled = true): bool {
			if(!is_bool($enabled)) {
				$this->error = "expected boolean parameter (enableMining method)";
				return false;
			}
			$params = [
				'enabled' => $enabled
			];
			$response = $this->api_query("enableInterest", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function enableHistoryMining($enabled = true): bool {
			if(!is_bool($enabled)) {
				$this->error = "expected boolean parameter (enableMining method)";
				return false;
			}
			$params = [
				'enabled' => $enabled
			];
			$response = $this->api_query("enableHistoryMining", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function statusHistoryMining(): int {
			$response = $this->api_query("statusHistoryMining");
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}

		public function getMiningBlocks($query_filter = null): array {
			$response = $this->api_query("getMiningBlocks", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getMiningInfo(): array {
			$response = $this->api_query("getMiningBlocks");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getVouchers($query_filter = null): array {
			$response = $this->api_query("getVouchers");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function createVoucher($amount = 1): string {
			if(!is_float($amount)) {
				return '';
			}
			$params = [
				'amount' => $amount
			];
			$response = $this->api_query("createVoucher", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function useVoucher($voucherid = ''): string {
			if($voucherid == "") {
				return '';
			}
			$params = [
				'voucherid' => $voucherid
			];
			$response = $this->api_query("useVoucher", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function deleteVoucher($voucherid = ''): string {
			if($voucherid == "") {
				return '';
			}
			$params = [
				'voucherid' => $voucherid
			];
			$response = $this->api_query("deleteVoucher", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function getInvoices($cardId = '', $invoiceId = '', $pk = '', $transactionId = '', $status = '', $startDateTime = '', $endDateTime = '', $referenceNumber = ''): array {
			$params = [
				'cardId'          => $cardId,
				'invoiceId'       => $invoiceId,
				'pk'              => $pk,
				'transactionId'   => $transactionId,
				'status'          => $status,
				'startDateTime'   => $startDateTime,
				'endDateTime'     => $endDateTime,
				'referenceNumber' => $referenceNumber,
			];
			$response = $this->api_query("getInvoices", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getInvoiceByReferenceNumber($referenceNumber = null): string {
			$params = [];
			if($referenceNumber != null) {
				$params['referenceNumber'] = $referenceNumber;
			}
	
			$response = $this->api_query("getInvoiceByReferenceNumber", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getTransactionIdByReferenceNumber($referenceNumber = null) {
			$params = [];
			if($referenceNumber != null) {
				$params['referenceNumber'] = $referenceNumber;
			}
			$response = $this->api_query("getTransactionIdByReferenceNumber", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function sendInvoice($cardid = '', $amount = 1, $comment = ''): string {
			$params = [
				'cardid'  => $cardid,
				'amount'  => $amount,
				'comment' => $comment
			];
			$response = $this->api_query("sendInvoice", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function acceptInvoice($invoiceid = ''): string {
			$params = [
				'invoiceid' => $invoiceid
			];
			$response = $this->api_query("acceptInvoice", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function declineInvoice($invoiceid = ''): string {
			$params = [
				'invoiceid' => $invoiceid
			];
			$response = $this->api_query("declineInvoice", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function cancelInvoice($invoiceid = '') {
			$params = [
				'invoiceid' => $invoiceid
			];
			$response = $this->api_query("cancelInvoice", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function requestUnsTransfer($name, $hexNewOwnerPk): string {
			$params = [
				'name'          => $name,
				'hexNewOwnerPk' => $hexNewOwnerPk
			];
			if(! ctype_xdigit($hexNewOwnerPk)) {
				$this->error = "expected hex parameter (requestUnsTransfer method)";
				return '';
			}
			$response = $this->api_query("requestUnsTransfer", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function acceptUnsTransfer($requestId = "123"): string {
			$params = [
				'requestId' => $requestId
			];
			$response = $this->api_query("acceptUnsTransfer", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function declineUnsTransfer($requestId = "123"): string {
			$params = [
				'requestId' => $requestId
			];
			$response = $this->api_query("declineUnsTransfer", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function incomingUnsTransfer($query_filter = null): array {
			$response = $this->api_query("incomingUnsTransfer", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function outgoingUnsTransfer($query_filter = null): array {
			$response = $this->api_query("outgoingUnsTransfer", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function storageWipe(): bool {
			$response = $this->api_query("storageWipe");
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function sendAuthorizationRequest($pk, $message = "auth request"): bool {
			$params = [
				'pk'      => $pk,
				'message' => $message
			];
			$response = $this->api_query("sendAuthorizationRequest", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function acceptAuthorizationRequest($pk, $message = "request accepted"): bool {
			$params = [
				'pk'      => $pk,
				'message' => $message
			];
			$response = $this->api_query("acceptAuthorizationRequest", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function rejectAuthorizationRequest($pk, $message = "request rejected"): bool {
			$params = [
				'pk'      => $pk,
				'message' => $message
			];
			$response = $this->api_query("rejectAuthorizationRequest", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function deleteContact($pk): bool {
			$params = [
				'pk' => $pk
			];
			$response = $this->api_query("deleteContact", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getChannels($search_filter = '', $channel_type = 0, $query_filter = null): array {
			$params = [
				'filter'       => $search_filter,
				'channel_type' => $channel_type
			];
			$response = $this->api_query("getChannels", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function sendChannelMessage($channelid, $message = "test message"): string {
			$params = [
				'channelid' => $channelid,
				'message'   => $message
			];
			$response = $this->api_query("sendChannelMessage", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function sendChannelPicture($channelid, $base64_image = '', $filename_image = ''): string {
			$params = [
				'channelid'      => $channelid,
				'base64_image'   => $base64_image,
				'filename_image' => $filename_image
			];
			$response = $this->api_query("sendChannelPicture", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function joinChannel($channelid, $password = ''): bool {
			$params = [
				'ident'    => $channelid,
				'password' => $password
			];
			$response = $this->api_query("joinChannel", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function leaveChannel($channelid): bool {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query("leaveChannel", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getChannelMessages($channelid, $query_filter = null) {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query("getChannelMessages", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getChannelInfo($channelid): array {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query("getChannelInfo", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getChannelModerators($channelid): array {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query("getChannelModerators", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getChannelContacts($channelid): array {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query("getChannelContacts", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getChannelModeratorRight($channelid, $moderator = "1") {
			$params = [
				'channelid' => $channelid,
				'moderator' => $moderator
			];
			$response = $this->api_query("getChannelModeratorRight", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function createChannel($channel_name = "my channel", $description = '', $read_only = '', $read_only_privacy = '', $password = '', $languages = '', $hashtags = '', $geoTag = '', $base64_avatar_image = '', $hide_in_UI = ''): string {
			$params = [
				'channel_name'        => $channel_name,
				'description'         => $description,
				'read_only'           => $read_only,
				'read_only_privacy'   => $read_only_privacy,
				'password'            => $password,
				'languages'           => $languages,
				'hashtags'            => $hashtags,
				'geoTag'              => $geoTag,
				'base64_avatar_image' => $base64_avatar_image,
				'hide_in_UI'          => $hide_in_UI
			];
			$response = $this->api_query("createChannel", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function modifyChannel($channelid, $description = null, $read_only = null, $read_only_privacy = null, $languages = null, $hashtags = null, $geoTag = null, $base64_avatar_image = null, $hide_in_UI = null): string {
			$params = [
				'channelid'   => $channelid
			];
			if($description != null) {
				$params['description'] = $description;
			}
			if($read_only != null) {
				$params['read_only'] = $read_only;
			}
			if($read_only_privacy != null) {
				$params['read_only_privacy'] = $read_only_privacy;
			}
			if($languages != null) {
				$params['languages'] = $languages;
			}
			if($hashtags != null) {
				$params['hashtags'] = $hashtags;
			}
			if($geoTag != null) {
				$params['geoTag'] = $geoTag;
			}
			if($base64_avatar_image != null) {
				$params['base64_avatar_image'] = $base64_avatar_image;
			}
			if($hide_in_UI != null) {
				$params['hide_in_UI'] = $hide_in_UI;
			}
			$response = $this->api_query("modifyChannel", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function modifyChannelDescription($channelid, $description = null): string {
			return $this->modifyChannel($channelid, $description);
		}

		public function modifyChannelReadOnly($channelid, $read_only = null): string {
			return $this->modifyChannel($channelid, null, $read_only);
		}

		public function modifyChannelReadOnlyPrivacy($channelid, $read_only_privacy = null): string {
			return $this->modifyChannel($channelid, null, null, $read_only_privacy);
		}

		public function modifyChannelLanguages($channelid, $languages = null): string {
			return $this->modifyChannel($channelid, null, null, null, $languages);
		}

		public function modifyChannelHashtags($channelid, $hashtags = null): string {
			return $this->modifyChannel($channelid, null, null, null, null, $hashtags);
		}

		public function modifyChannelGeoTag($channelid, $geoTag = null): string {
			return $this->modifyChannel($channelid, null, null, null, null, null, $geoTag);
		}

		public function modifyChannelAvatar($channelid, $base64_avatar_image = null): string {
			return $this->modifyChannel($channelid, null, null, null, null, null, null, $base64_avatar_image);
		}

		public function modifyChannelHideInUI($channelid, $hide_in_UI = null): string {
			return $this->modifyChannel($channelid, null, null, null, null, null, null, null, $hide_in_UI);
		}

		public function deleteChannel($channelid): bool {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query("deleteChannel", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getChannelSystemInfo(): array {
			$response = $this->api_query("getChannelSystemInfo");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function unsCreateRecordRequest($nick, $validUnilDate = "2048-12-02", $isPrimary = false, $channelId = null): string {
			$params = [
				'nick'      => $nick,
				'valid'     => $validUnilDate,
				'isPrimary' => $isPrimary
			];
			if($channelId != null) {
				$params['channelId'] = $channelId;
			}
			$response = $this->api_query("unsCreateRecordRequest", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function unsModifyRecordRequest($nick, $validUnilDate = null, $isPrimary = null, $channelId = null): string {
			$params = [
				'nick' => $nick
			];
			if($validUnilDate != null) {
				$params['valid'] = $validUnilDate;
			}
			if($isPrimary != null) {
				$params['isPrimary'] = $isPrimary;
			}
			$response = $this->api_query("unsModifyRecordRequest", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function unsDeleteRecordRequest($nick): string {
			$params = [
				'nick' => $nick
			];
			$response = $this->api_query("unsDeleteRecordRequest", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function unsSearchByPk($pk, $query_filter = null): array {
			$params = [
				'filter' => $pk
			];
			$response = $this->api_query("unsSearchByPk", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function unsSearchByNick($nick, $query_filter = null): array {
			$params = [
				'filter' => $nick
			];
			$response = $this->api_query("unsSearchByNick", $params, $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getUnsSyncInfo(): array {
			$response = $this->api_query("getUnsSyncInfo");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function unsRegisteredNames($query_filter = null): array {
			$response = $this->api_query("unsRegisteredNames", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function summaryUnsRegisteredNames($date_from, $date_to, $query_filter = null): array {
			$params = [
				'fromDate' => $date_from,
				'toDate'   => $query_filter
			];
			$response = $this->api_query("unsRegisteredNames", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function clearTrayNotifications(): bool {
			$response = $this->api_query("clearTrayNotifications");
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getNetworkConnections($query_filter = null): array {
			$response = $this->api_query("getNetworkConnections", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function getProxyMappings($query_filter = null): array {
			$response = $this->api_query("getProxyMappings", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function createProxyMapping($srcHost, $srcPort = 80, $dstHost = "127.0.0.1", $dstPort = 80, $enabled = true): int {
			$params = [
				'srcHost' => $srcHost,
				'srcPort' => $srcPort,
				'dstHost' => $dstHost,
				'dstPort' => $dstPort,
				'enabled' => $enabled
			];
			$response = $this->api_query("createProxyMapping", $params);
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}

		public function enableProxyMapping($mappingId): bool {
			$params = [
				'mappingId' => $mappingId
			];
			$response = $this->api_query("enableProxyMapping", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function disableProxyMapping($mappingId): bool {
			$params = [
				'mappingId' => $mappingId
			];
			$response = $this->api_query("disableProxyMapping", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function removeProxyMapping($mappingId): bool {
			$params = [
				'mappingId' => $mappingId
			];
			$response = $this->api_query("removeProxyMapping", $params);
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function lowTrafficMode(): bool {
			$response = $this->api_query("lowTrafficMode");
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function setLowTrafficMode($enabled = true): bool {
			$params = [
				'enabled' => $enabled
			];
			$response = $this->api_query("setLowTrafficMode");
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getWhoIsInfo($pkOrNick): array {
			$params = [
				'owner' => $pkOrNick
			];
			$response = $this->api_query("getWhoIsInfo", $params);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function isUserMyContact($pkOrNick): bool {
			$whois_info = $this->getWhoIsInfo($pkOrNick);
			$general = $whois_info['general'];
	
			$is_known = false;
			for($i = 0; $i < count($general); $i++) {
				$line = $general[$i];
				if($line['name'] == 'You have this Public Key in your contact list' || $line['name'] == 'В вашем списке контактов есть этот Public Key') {
					if($line['value'] == 'Yes' || $line['value'] == 'Да') {
						$is_known = true;
					} else {
						$is_known = false;
					}
					break;
				}
			}
			return $is_known;
		}

		public function requestTreasuryInterestRates(): bool {
			$response = $this->api_query("requestTreasuryInterestRates");
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getTreasuryInterestRates(): array {
			$response = $this->api_query("getTreasuryInterestRates");
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function requestTreasuryTransactionVolumes(): bool {
			$response = $this->api_query("requestTreasuryTransactionVolumes");
			if(! $this->checkResultContains($response)) {
				return false;
			}
			return $response['result'];
		}

		public function getTreasuryTransactionVolumes($query_filter = null): array {
			$response = $this->api_query("getTreasuryTransactionVolumes", [], $query_filter);
			if(! $this->checkResultContains($response)) {
				return [];
			}
			return $response['result'];
		}

		public function ucodeEncode($hex_code, $size_image = 128, $coder = 'BASE64', $format = "JPG"): string {
			if(!ctype_xdigit($hex_code)) {
				$this->error = "hex_code must be in hexadecimal representation";
				return '';
			}
			switch($coder) {
				default:
					$coder = 'BASE64'; break;
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
				'hex_code'   => $hex_code,
				'size_image' => $size_image,
				'coder'      => $coder,
				'format'     => $format
			];
			$response = $this->api_query("ucodeEncode", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function ucodeDecode($base64_image): array {
			$params = [
				'base64_image' => $base64_image
			];
			$response = $this->api_query("ucodeDecode", $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			return $response['result'];
		}

		public function getWebSocketState(): int {
			$response = $this->api_query("getWebSocketState");
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}

		public function setWebSocketState($enabled = "false", $port = "226748"): int {
			$params = [
				'enabled' => (string) $enabled,
				'port'    => $port
			];
			$response = $this->api_query("setWebSocketState");
			if(! $this->checkResultContains($response)) {
				return 0;
			}
			return $response['result'];
		}

		public function checkClientConnection(): bool {
			$response = $this->getSystemInfo();
			//return isset($response['result']);
			return $this->checkResultContains($response);
		}
	}
	