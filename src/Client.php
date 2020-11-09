<?php
	namespace UtopiaLib;

	class Client implements ClientInterface {
		public $error = ''; //last error
		public $last_response = ''; //for debug

		protected $api_port    = 22659;
		protected $api_host    = '';
		protected $api_token   = '';
		protected $api_version = '1.0';
		protected $client   = null; //Graze\GuzzleHttp\JsonRpc\Client
		protected $is_debug = false;

		public function __construct($token = '', $host = 'http://127.0.0.1', $port = 22659) {
			$this->api_token = $token;
			$this->api_port  = $port;
			$this->api_host  = $host;
			$this->init();
		}

		protected function getApiUrl() {
			return $this->api_host . ':' . $this->api_port . '/api/' . $this->api_version;
		}

		protected function api_query($method = "getSystemInfo", $params = [], $filter = null): array {
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

		protected function guzzleQuery($url, $query_body) {
			$response = $this->client->request("POST", $url, [
				'json' => $query_body
			]);
			return $response->getBody()->getContents();
		}

		protected function init() {
			$this->client = new \GuzzleHttp\Client([
				'timeout' => 2.0
			]);
		}

		protected function checkResultContains($response = []): bool {
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

		protected function checkResultBool($value): bool {
			if(!is_bool($value)) {
				$this->error = "'result' contains " . gettype($value) . " expected bool";
				if($this->is_debug) {
					throw new \RuntimeException($this->error);
				}
				return false;
			}
			return true;
		}

		function checkResultVar($response = '', $byDefault = '') {
			if(! $this->checkResultContains($response)) {
				return $byDefault;
			}
			return $response['result'];
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
			//if(! $this->checkResultContains($response)) {
			//	return [];
			//}
			//return $response['result'];
			return $this->checkResultVar($response, []);
		}

		public function getContacts($search_filter = '', $query_filter = null): array {
			$params = [
				'filter' => $search_filter
			];
			$response = $this->api_query('getContacts', $params, $query_filter);
			return $this->checkResultVar($response, []);
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
			return $this->checkResultVar($response, '');
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
			return $this->checkResultVar($response, '');
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
			return $this->checkResultVar($response, []);
		}

		public function getStickerNamesByCollection($collection_name = 'Default Stickers'): array {
			$params = [
				'collection_name' => $collection_name
			];
			$response = $this->api_query('getStickerNamesByCollection', $params);
			return $this->checkResultVar($response, []);
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
			return $this->checkResultVar($response, '');
		}

		public function sendInstantBuzz($pkOrNick = '', $comments = "test"): int {
			$params = [
				'to'       => $pkOrNick,
				'comments' => $comments
			];
			$response = $this->api_query("sendInstantBuzz", $params);
			return $this->checkResultVar($response, 0);
		}

		public function sendInstantInvitation($pkOrNick, $channelid, $description, $comments): int {
			$params = [
				'to'          => $pkOrNick,
				'channelid'   => $channelid,
				'description' => $description,
				'comments'    => $comments
			];
			$response = $this->api_query("sendInstantInvitation", $params);
			return $this->checkResultVar($response, 0);
		}

		public function removeInstantMessages($pk = ''): bool {
			$params = [
				'hex_contact_public_key' => $pkOrNick
			];
			$response = $this->api_query("sendInstantInvitation", $params);
			return $this->checkResultVar($response, false);
		}

		public function getContactMessages($pk, $query_filter = null): array {
			$params = [
				'pk' => $pk
			];
			$response = $this->api_query("getContactMessages", $params, $query_filter);
			return $this->checkResultVar($response, []);
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
			return $this->checkResultVar($response, false);
		}

		public function sendManyEmailMessages($pk_arr = [], $subject = "test message", $body = "message content"): bool {
			$params = [
				'to'      => $pk_arr,
				'subject' => $subject,
				'body'    => $body
			];
			$response = $this->api_query("sendEmailMessage", $params, $query_filter);
			return $this->checkResultVar($response, false);
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
			return $this->checkResultVar($response, '');
		}

		public function getEmailFolder($folderType = 1, $search_filter = '', $query_filter = null): array {
			$params = [
				'folderType' => Utilities::filterFolderType($folderType),
				'filter'     => $search_filter
			];
			$response = $this->api_query("getEmailFolder", $params, $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function getEmails($folderType = 1, $search_filter = '', $query_filter = null): array {
			$params = [
				'folderType' => Utilities::filterFolderType($folderType),
				'filter'     => $search_filter
			];
			$response = $this->api_query("getEmails", $params, $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function getEmailById($id = 33): array {
			$params = [
				'id' => $id
			];
			$response = $this->api_query("getEmailById", $params);
			return $this->checkResultVar($response, []);
		}

		public function deleteEmail($id = 33): bool {
			$params = [
				'id' => $id
			];
			$response = $this->api_query("deleteEmail", $params);
			return $this->checkResultVar($response, false);
		}

		public function sendReplyEmailMessage($id = 33, $body = "my message", $subject = "uMail subject"): bool {
			$params = [
				'id'      => $id,
				'body'    => $body,
				'subject' => $subject
			];
			$response = $this->api_query("sendReplyEmailMessage", $params);
			return $this->checkResultVar($response, false);
		}

		public function sendForwardEmailMessage($id = 33, $pkOrNick = '', $body = "my message", $subject = "uMail subject"): bool {
			$params = [
				'id'      => $id,
				'to'      => $pkOrNick,
				'body'    => $body,
				'subject' => $subject
			];
			$response = $this->api_query("sendForwardEmailMessage", $params);
			return $this->checkResultVar($response, false);
		}

		public function getFinanceSystemInformation(): array {
			$response = $this->api_query("getFinanceSystemInformation");
			return $this->checkResultVar($response, []);
		}

		public function getBalance(): float {
			$response = $this->api_query("getBalance");
			return $this->checkResultVar($response, 0);
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
			return $this->checkResultVar($response, []);
		}

		public function getCards(): array {
			$response = $this->api_query("getCards");
			return $this->checkResultVar($response, []);
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
			return $this->checkResultVar($response, '');
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
			return $this->checkResultVar($response, false);
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
			return $this->checkResultVar($response, false);
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
			return $this->checkResultVar($response, false);
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
			return $this->checkResultVar($response, false);
		}

		public function statusHistoryMining(): int {
			$response = $this->api_query("statusHistoryMining");
			return $this->checkResultVar($response, 0);
		}

		public function getMiningBlocks($query_filter = null): array {
			$response = $this->api_query("getMiningBlocks", [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function getMiningInfo(): array {
			$response = $this->api_query("getMiningBlocks");
			return $this->checkResultVar($response, []);
		}

		public function getVouchers($query_filter = null): array {
			$response = $this->api_query("getVouchers");
			return $this->checkResultVar($response, []);
		}

		public function createVoucher($amount = 1): string {
			if(!is_float($amount)) {
				return '';
			}
			$params = [
				'amount' => $amount
			];
			$response = $this->api_query("createVoucher", $params);
			return $this->checkResultVar($response, '');
		}

		public function useVoucher($voucherid = ''): string {
			if($voucherid == '') {
				return '';
			}
			$params = [
				'voucherid' => $voucherid
			];
			$response = $this->api_query('useVoucher', $params);
			return $this->checkResultVar($response, '');
		}

		public function deleteVoucher($voucherid = ''): string {
			if($voucherid == '') {
				return '';
			}
			$params = [
				'voucherid' => $voucherid
			];
			$response = $this->api_query('deleteVoucher', $params);
			return $this->checkResultVar($response, '');
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
			$response = $this->api_query('getInvoices', $params);
			return $this->checkResultVar($response, []);
		}

		public function getInvoiceByReferenceNumber($referenceNumber = null): string {
			$params = [];
			if($referenceNumber != null) {
				$params['referenceNumber'] = $referenceNumber;
			}

			$response = $this->api_query('getInvoiceByReferenceNumber', $params);
			return $this->checkResultVar($response, []);
		}

		public function getTransactionIdByReferenceNumber($referenceNumber = null) {
			$params = [];
			if($referenceNumber != null) {
				$params['referenceNumber'] = $referenceNumber;
			}
			$response = $this->api_query('getTransactionIdByReferenceNumber', $params);
			return $this->checkResultVar($response, []);
		}

		public function sendInvoice($cardid = '', $amount = 1, $comment = ''): string {
			$params = [
				'cardid'  => $cardid,
				'amount'  => $amount,
				'comment' => $comment
			];
			$response = $this->api_query('sendInvoice', $params);
			return $this->checkResultVar($response, '');
		}

		public function acceptInvoice($invoiceid = ''): string {
			$params = [
				'invoiceid' => $invoiceid
			];
			$response = $this->api_query('acceptInvoice', $params);
			return $this->checkResultVar($response, '');
		}

		public function declineInvoice($invoiceid = ''): string {
			$params = [
				'invoiceid' => $invoiceid
			];
			$response = $this->api_query('declineInvoice', $params);
			return $this->checkResultVar($response, '');
		}

		public function cancelInvoice($invoiceid = '') {
			$params = [
				'invoiceid' => $invoiceid
			];
			$response = $this->api_query('cancelInvoice', $params);
			return $this->checkResultVar($response, '');
		}

		public function requestUnsTransfer($name, $hexNewOwnerPk): string {
			$params = [
				'name'          => $name,
				'hexNewOwnerPk' => $hexNewOwnerPk
			];
			if(! ctype_xdigit($hexNewOwnerPk)) {
				$this->error = 'expected hex parameter (requestUnsTransfer method)';
				return '';
			}
			$response = $this->api_query('requestUnsTransfer', $params);
			return $this->checkResultVar($response, '');
		}

		public function acceptUnsTransfer($requestId = '123'): string {
			$params = [
				'requestId' => $requestId
			];
			$response = $this->api_query('acceptUnsTransfer', $params);
			return $this->checkResultVar($response, '');
		}

		public function declineUnsTransfer($requestId = '123'): string {
			$params = [
				'requestId' => $requestId
			];
			$response = $this->api_query('declineUnsTransfer', $params);
			return $this->checkResultVar($response, '');
		}

		public function incomingUnsTransfer($query_filter = null): array {
			$response = $this->api_query('incomingUnsTransfer', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function outgoingUnsTransfer($query_filter = null): array {
			$response = $this->api_query('outgoingUnsTransfer', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function storageWipe(): bool {
			$response = $this->api_query('storageWipe');
			return $this->checkResultVar($response, []);
		}

		public function sendAuthorizationRequest($pk, $message = 'auth request'): bool {
			$params = [
				'pk'      => $pk,
				'message' => $message
			];
			$response = $this->api_query('sendAuthorizationRequest', $params);
			return $this->checkResultVar($response, false);
		}

		public function acceptAuthorizationRequest($pk, $message = 'request accepted'): bool {
			$params = [
				'pk'      => $pk,
				'message' => $message
			];
			$response = $this->api_query('acceptAuthorizationRequest', $params);
			return $this->checkResultVar($response, false);
		}

		public function rejectAuthorizationRequest($pk, $message = 'request rejected'): bool {
			$params = [
				'pk'      => $pk,
				'message' => $message
			];
			$response = $this->api_query('rejectAuthorizationRequest', $params);
			return $this->checkResultVar($response, false);
		}

		public function deleteContact($pk): bool {
			$params = [
				'pk' => $pk
			];
			$response = $this->api_query('deleteContact', $params);
			return $this->checkResultVar($response, false);
		}

		public function getChannels($search_filter = '', $channel_type = 0, $query_filter = null): array {
			$params = [
				'filter'       => $search_filter,
				'channel_type' => $channel_type
			];
			$response = $this->api_query('getChannels', $params, $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function sendChannelMessage($channelid, $message = 'test message'): string {
			$params = [
				'channelid' => $channelid,
				'message'   => $message
			];
			$response = $this->api_query('sendChannelMessage', $params);
			return $this->checkResultVar($response, '');
		}

		public function sendChannelPicture($channelid, $base64_image = '', $filename_image = ''): string {
			$params = [
				'channelid'      => $channelid,
				'base64_image'   => $base64_image,
				'filename_image' => $filename_image
			];
			$response = $this->api_query('sendChannelPicture', $params);
			return $this->checkResultVar($response, '');
		}

		public function joinChannel($channelid, $password = ''): bool {
			$params = [
				'ident'    => $channelid,
				'password' => $password
			];
			$response = $this->api_query('joinChannel', $params);
			return $this->checkResultVar($response, false);
		}

		public function leaveChannel($channelid): bool {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query('leaveChannel', $params);
			return $this->checkResultVar($response, false);
		}

		public function getChannelMessages($channelid, $query_filter = null) {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query('getChannelMessages', $params, $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function getChannelInfo($channelid): array {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query('getChannelInfo', $params);
			return $this->checkResultVar($response, []);
		}

		public function getChannelModerators($channelid): array {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query('getChannelModerators', $params);
			return $this->checkResultVar($response, []);
		}

		public function getChannelContacts($channelid): array {
			$params = [
				'channelid' => $channelid
			];
			$response = $this->api_query('getChannelContacts', $params);
			return $this->checkResultVar($response, []);
		}

		public function getChannelModeratorRight($channelid, $moderator = '1') {
			$params = [
				'channelid' => $channelid,
				'moderator' => $moderator
			];
			$response = $this->api_query('getChannelModeratorRight', $params);
			return $this->checkResultVar($response, []);
		}

		public function createChannel($channel_name = 'my channel', $description = '', $read_only = '', $read_only_privacy = '', $password = '', $languages = '', $hashtags = '', $geoTag = '', $base64_avatar_image = '', $hide_in_UI = ''): string {
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
			$response = $this->api_query('createChannel', $params);
			return $this->checkResultVar($response, '');
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
			$response = $this->api_query('modifyChannel', $params);
			return $this->checkResultVar($response, '');
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
			$response = $this->api_query('deleteChannel', $params);
			return $this->checkResultVar($response, false);
		}

		public function getChannelSystemInfo($channelId = ''): array {
			$params = [
				'channelId' => $channelId
			];
			$response = $this->api_query('getChannelSystemInfo', $params);
			return $this->checkResultVar($response, []);
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
			$response = $this->api_query('unsCreateRecordRequest', $params);
			return $this->checkResultVar($response, '');
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
			$response = $this->api_query('unsModifyRecordRequest', $params);
			return $this->checkResultVar($response, '');
		}

		public function unsDeleteRecordRequest($nick): string {
			$params = [
				'nick' => $nick
			];
			$response = $this->api_query('unsDeleteRecordRequest', $params);
			return $this->checkResultVar($response, '');
		}

		public function unsSearchByPk($pk, $query_filter = null): array {
			$params = [
				'filter' => $pk
			];
			$response = $this->api_query('unsSearchByPk', $params, $query_filter);
			return $this->checkResultVar($response, '');
		}

		public function unsSearchByNick($nick, $query_filter = null): array {
			$params = [
				'filter' => $nick
			];
			$response = $this->api_query('unsSearchByNick', $params, $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function getUnsSyncInfo(): array {
			$response = $this->api_query('getUnsSyncInfo');
			return $this->checkResultVar($response, []);
		}

		public function unsRegisteredNames($query_filter = null): array {
			$response = $this->api_query('unsRegisteredNames', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function summaryUnsRegisteredNames($date_from, $date_to, $query_filter = null): array {
			$params = [
				'fromDate' => $date_from,
				'toDate'   => $query_filter
			];
			$response = $this->api_query('unsRegisteredNames', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function clearTrayNotifications(): bool {
			$response = $this->api_query('clearTrayNotifications');
			return $this->checkResultVar($response, false);
		}

		public function getNetworkConnections($query_filter = null): array {
			$response = $this->api_query('getNetworkConnections', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function getProxyMappings($query_filter = null): array {
			$response = $this->api_query('getProxyMappings', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function createProxyMapping($srcHost, $srcPort = 80, $dstHost = '127.0.0.1', $dstPort = 80, $enabled = true): int {
			$params = [
				'srcHost' => $srcHost,
				'srcPort' => $srcPort,
				'dstHost' => $dstHost,
				'dstPort' => $dstPort,
				'enabled' => $enabled
			];
			$response = $this->api_query('createProxyMapping', $params);
			return $this->checkResultVar($response, 0);
		}

		public function enableProxyMapping($mappingId): bool {
			$params = [
				'mappingId' => $mappingId
			];
			$response = $this->api_query('enableProxyMapping', $params);
			return $this->checkResultVar($response, false);
		}

		public function disableProxyMapping($mappingId): bool {
			$params = [
				'mappingId' => $mappingId
			];
			$response = $this->api_query('disableProxyMapping', $params);
			return $this->checkResultVar($response, false);
		}

		public function removeProxyMapping($mappingId): bool {
			$params = [
				'mappingId' => $mappingId
			];
			$response = $this->api_query('removeProxyMapping', $params);
			return $this->checkResultVar($response, false);
		}

		public function lowTrafficMode(): bool {
			$response = $this->api_query('lowTrafficMode');
			return $this->checkResultVar($response, false);
		}

		public function setLowTrafficMode($enabled = true): bool {
			$params = [
				'enabled' => $enabled
			];
			$response = $this->api_query('setLowTrafficMode');
			return $this->checkResultVar($response, false);
		}

		public function getWhoIsInfo($pkOrNick): array {
			$params = [
				'owner' => $pkOrNick
			];
			$response = $this->api_query('getWhoIsInfo', $params);
			return $this->checkResultVar($response, []);
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
			$response = $this->api_query('requestTreasuryInterestRates');
			return $this->checkResultVar($response, false);
		}

		public function getTreasuryInterestRates(): array {
			$response = $this->api_query('getTreasuryInterestRates');
			return $this->checkResultVar($response, []);
		}

		public function requestTreasuryTransactionVolumes(): bool {
			$response = $this->api_query('requestTreasuryTransactionVolumes');
			return $this->checkResultVar($response, false);
		}

		public function getTreasuryTransactionVolumes($query_filter = null): array {
			$response = $this->api_query('getTreasuryTransactionVolumes', [], $query_filter);
			return $this->checkResultVar($response, []);
		}

		public function ucodeEncode($hex_code, $size_image = 128, $coder = 'BASE64', $format = 'JPG'): string {
			if(!ctype_xdigit($hex_code)) {
				$this->error = 'hex_code must be in hexadecimal representation';
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
					$format = 'JPG'; break;
				case 'JPG': break;
				case 'PNG': break;
			}
			$params = [
				'hex_code'   => $hex_code,
				'size_image' => $size_image,
				'coder'      => $coder,
				'format'     => $format
			];
			$response = $this->api_query('ucodeEncode', $params);
			return $this->checkResultVar($response, '');
		}

		public function ucodeDecode($base64_image): array {
			$params = [
				'base64_image' => $base64_image
			];
			$response = $this->api_query('ucodeDecode', $params);
			return $this->checkResultVar($response, '');
		}

		public function getWebSocketState(): int {
			$response = $this->api_query('getWebSocketState');
			return $this->checkResultVar($response, 0);
		}

		public function setWebSocketState($enabled = 'false', $port = '226748'): int {
			$params = [
				'enabled' => (string) $enabled,
				'port'    => $port
			];
			$response = $this->api_query('setWebSocketState');
			return $this->checkResultVar($response, 0);
		}

		public function checkClientConnection(): bool {
			$response = $this->getSystemInfo();
			return $this->checkResultContains($response);
		}

		public function getNetworkSummary(): array {
			$response = $this->api_query('getNetworkConnections');
			if(! $this->checkResultContains($response)) {
				return [];
			}
			if(! isset($response['result']['summary'])) {
				return [];
			} else {
				return $response['result']['summary'];
			}
		}

		function getNetworkSummaryBool($var_name = 'NAT_detection'): bool {
			$network_summary = $this->getNetworkSummary();
			if($network_summary == []) {
				return false;
			}
			if(!isset($network_summary[$var_name])) {
				return false;
			} else {
				switch($network_summary[$var_name]) {
					default:
						return false;
					case 1:
						return true;
				}
			}
		}

		public function isCryptonEngineReady(): bool {
			return $this->getNetworkSummaryBool('crypton_engine_status');
		}

		public function isNATDetectionON(): bool {
			return $this->getNetworkSummaryBool('NAT_detection');
		}

		public function isUPNPDetectionON(): bool {
			return $this->getNetworkSummaryBool('UPNP_detection');
		}

		public function isChannelDatabaseReady(): bool {
			return ! $this->getNetworkSummaryBool('channel_database_sync_status');
		}

		public function getTransfersFromManager(): array {
			$response = $this->api_query('getTransfersFromManager');
			return $this->checkResultVar($response, []);
		}

		public function getFilesFromManager(): array {
			$response = $this->api_query('getFilesFromManager');
			return $this->checkResultVar($response, []);
		}

		public function abortTransfers($transfer_id): bool {
			$params = [
				'transferId' => $transfer_id
			];
			$response = $this->api_query('abortTransfers', $params);
			return $this->checkResultVar($response, false);
		}

		public function hideTransfers($transfer_id): bool {
			$params = [
				'transferId' => $transfer_id
			];
			$response = $this->api_query('hideTransfers', $params);
			return $this->checkResultVar($response, false);
		}

		public function getFile($file_id): string {
			$params = [
				'fileId' => $file_id
			];
			$response = $this->api_query('getFile', $params);
			if(! $this->checkResultContains($response)) {
				return '';
			}
			if(!isset($response['result']['body'])) {
				return '';
			}
			return $response['result']['body'];
		}

		public function deleteFile($file_id): bool {
			$params = [
				'transferId' => $transfer_id
			];
			$response = $this->api_query('file_id', $params);
			return $this->checkResultVar($response, false);
		}

		public function sendFileByMessage($pubkey, $file_id = 55779): bool {
			$params = [
				'to'     => $pubkey,
				'fileId' => $file_id
			];
			$response = $this->api_query('sendFileByMessage', $params);
			return $this->checkResultVar($response, false);
		}

		public function uploadFile($base64, $filename = 'file.ext'): int {
			$params = [
				'fileDataBase64' => $base64,
				'fileName'       => $filename
			];
			$response = $this->api_query('uploadFile', $params);
			return $this->checkResultVar($response, 0);
		}

		public function getCardInfo($cardID): array {
			$response = $this->api_query('getCards');
			if(! $this->checkResultContains($response)) {
				return [];
			}
			$cards_arr = $response['result'];
			if(count($cards_arr) == 0) {
				return [];
			}
			for($i = 0; $i < count($cards_arr); $i++) {
				$card_info = $cards_arr[$i];
				if($card_info['cardid'] == $cardID) {
					return $card_info;
				}
			}
			return [];
		}

		public function getChannelDecription($channelid): string {
			$channel_info = $this->getChannelInfo();
			if(isset($channel_info['description'])) {
				return $channel_info['description'];
			}
			return '';
		}

		public function getChannelOwnerPubkey($channelid): string {
			$channel_info = $this->getChannelInfo();
			if(isset($channel_info['owner'])) {
				return $channel_info['owner'];
			}
			return '';
		}

		public function getChannelTitle($channelid): string {
			$channel_info = $this->getChannelInfo();
			if(isset($channel_info['title'])) {
				return $channel_info['title'];
			}
			return '';
		}

		public function getChannelType($channelid): string {
			$channel_info = $this->getChannelInfo();
			if(isset($channel_info['type'])) {
				return $channel_info['type'];
			}
			return '';
		}

		public function getNetworkChannelsCount(): int {
			$channels_systemInfo = $this->getChannelSystemInfo();
			return $channels_systemInfo['network_channels'];
		}

		public function getTotalChannelsCount(): int {
			$channels_systemInfo = $this->getChannelSystemInfo();
			return $channels_systemInfo['total_channels'];
		}

		public function getLastDownloadedChannelTitle(): string {
			$channels_systemInfo = $this->getChannelSystemInfo();
			return $channels_systemInfo['last_downloaded_channel'];
		}

		public function getMyPubkey(): string {
			return $this->getOwnContact()['pk'];
		}

		public function getMyNick(): string {
			return $this->getOwnContact()['nick'];
		}

		public function getMyAvatarHash(): string {
			return $this->getOwnContact()['avatarMd5'];
		}

		public function isPOSenabled(): bool {
			return $this->getFinanceSystemInformation()['PoS'];
		}

		public function findChannelsByPubkey($pubkey): array {
			return $this->getWhoIsInfo($pubkey)['channels'];
		}

		public function isNetworkEnabled(): bool {
			return $this->getSystemInfo()['result']['networkEnabled'];
		}
		
		public function getReleaseNotes(): array {
			return $this->checkResultVar($this->api_query('getReleaseNotes'), []);
		}
		
		public function getSettingInfo($settingId = ''): array {
			return $this->checkResultVar(
				$this->api_query('getReleaseNotes', ['settingId' => $settingId]), []
			);
		}
		
		public function setSettingInfo($settingId = '', $newValue = ''): bool {
			return $this->checkResultVar(
				$this->api_query(
					'setSettingInfo', [
						'settingId' => $settingId,
						'newValue'  => $newValue
					]
				), false
			);
		}
		
		public function pinInstantMessage($to = '', $messageId = '', $pin = true): int {
			return $this->checkResultVar(
				$this->api_query(
					'pinInstantMessage', [
						'to'        => $to,
						'messageId' => $messageId,
						'pin'       => $pin
					]
				), 0
			);
		}
		
		public function getPinnedMessages($to = ''): array {
			return $this->checkResultVar(
				$this->api_query(
					'getPinnedMessages', [
						'to' => $to
					]
				), []
			);
		}
		
		public function bookmarkInstantMessage($messageId = 11, $comments = ''): int {
			return $this->checkResultVar(
				$this->api_query(
					'bookmarkInstantMessage', [
						'messageId' => $messageId,
						'comments'  => $comments
					]
				), 0
			);
		}
		
		public function acceptAttachment($emailId = '100', $fileId = '100'): bool {
			return $this->checkResultVar(
				$this->api_query(
					'acceptAttachment', [
						'emailId' => $emailId,
						'fileId'  => $fileId
					]
				), false
			);
		}
		
		public function abortAttachment($emailId = '100', $fileId = '100'): bool {
			return $this->checkResultVar(
				$this->api_query(
					'abortAttachment', [
						'emailId' => $emailId,
						'fileId'  => $fileId
					]
				), false
			);
		}
		
		public function acceptFileMessage($messageId = '100'): bool {
			return $this->checkResultVar(
				$this->api_query(
					'acceptFileMessage', [
						'messageId' => $messageId
					]
				), false
			);
		}
		
		public function abortFileMessage($messageId = '100'): bool {
			return $this->checkResultVar(
				$this->api_query(
					'abortFileMessage', [
						'messageId' => $messageId
					]
				), false
			);
		}
		
		public function emptyEmailsTrash(): bool {
			return $this->checkResultVar(
				$this->api_query('emptyEmailsTrash'), false
			);
		}
		
		public function setChannelAsBookmarked($channelid = '', $bookmarked = true): bool {
			return $this->checkResultVar(
				$this->api_query('setChannelAsBookmarked', [
					'channelid'  => $channelid,
					'bookmarked' => $bookmarked
				]), false
			);
		}
		
		public function getChannelBannedContacts($channelid = ''): array {
			return $this->checkResultVar(
				$this->api_query('getChannelBannedContacts', [
					'channelid' => $channelid
				]), []
			);
		}
		
		public function applyChannelBannedContacts($channelid = '', $newList = '[]'): array {
			//newList examaple:
			//"[{"hash":"04E06F309930BD34B2FE1E95C852E6FF","nick":"mick"},{"hash":"BD287E72AB20D619737478D24D28949E","nick":"spammer"}]"
			return $this->checkResultVar(
				$this->api_query('applyChannelBannedContacts', [
					'channelid' => $channelid,
					'newList'   => $newList
				]), []
			);
		}
	}
