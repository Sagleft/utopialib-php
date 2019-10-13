<?php
	namespace UtopiaLib;
	
	interface ClientInterface {
		/**
			* information about current packaging version of the Utopia application.
			
			* @return array
		*/
		public function getSystemInfo(): array;
		
		/**
			* profile status.
			
			* @return array
		*/
		public function getProfileStatus(): array;
		
		/**
			* sets the new status, as well as the mood message in the Utopia Ecosystem.
			
			* @param string $status - "Available", "Away", "DoNotDisturb", "Invisible", "Offline"
			* @param string $mood - (optional) contains mood message text (up to 130 symbols)
			* @throws RuntimeException
			* @return true, if the request was successful, otherwise false.
		*/
		public function setProfileStatus($status, $mood): bool;
		
		/**
			* returns information about yourself.
			
			* @return array
		*/
		public function getOwnContact(): array;
		
		/**
			* returns to the Response field the list of contacts, it is possible to search by full or partial matching of the Public Key and Nickname.
			
			* @param string $search_filter - (optional) partial matching of the Public Key and Nickname
			* @param Filter $query_filter - (optional) filter which can be applied to ANY method returning an array
			* @throws RuntimeException
			* @return contact data array
		*/
		public function getContacts($search_filter = "", $query_filter = null): array;
		
		/**
			* returns to the Response field the avatar of the selected user in the base64 or hex format. As a parameter the method uses Public Key of the contact.
			
			* @param string $pk - contact Public Key
			* @param string $coder - "BASE64" or "HEX"
			* @param string $format - "JPG" or "PNG"
			* @throws RuntimeException
			* @return string
		*/
		public function getContactAvatar($pk, $coder, $format): string;
		
		/**
			* returns to the Response field the avatar of the selected channel in the base64 or hex format.
			
			* @param string $channelid - Utopia channel ID
			* @param string $coder - "BASE64" or "HEX"
			* @param string $format - "JPG" or "PNG"
			* @throws RuntimeException
			* @return string
		*/
		public function getChannelAvatar($channelid, $coder, $format);
		
		/**
			* creates group or transfers selected contact into the group in the contact list. The method is called by using the Public Key parameters, which pass the Public Key of the contact (Public Key can be recognized by using the getContacts method) and Group Name, which passes the group name for creation or transfer (up to 32 symbols).
			
			* @param string $pk - Public Key of the contact
			* @param string $groupName - Group Name
			* @throws RuntimeException
			* @return bool
		*/
		public function setContactGroup($pk, $groupName): bool;
		
		/**
			* sets the selected value for the Nickname field for the selected contact. The method is called by using the Public Key parameters, which pass on the Public Key for the contact (Public Key can be recognized by using the getContacts method) and New Nick, which passes on the new Nickname (up to 32 symbols).
			
			* @param string $pk - Public Key of the contact
			* @param string $newNick - New Nick
			* @throws RuntimeException
			* @return bool
		*/
		public function setContactNick($pk, $newNick): bool;
		
		/**
			* sends personal message(IM) to the selected contact from the contact list. The method is called by using the To parameter, that passes on the Public Key or Nickname to whom the message would be sent (Public Key can be recognized by using the getContacts method) and Text, which contains the text of the message.
			
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $message - message
			* @throws RuntimeException
			* @return int - message ID
		*/
		public function sendInstantMessage($pkOrNick, $message): int;
		
		/**
			* sends quote personal message(IM) to the selected contact from the contact list on message by id_message.
			
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $text - message
			* @param int $id_message - message ID
			* @throws RuntimeException
			* @return int
		*/
		public function sendInstantQuote($pkOrNick, $text, $id_message): int;
		
		/**
			* sends sticker personal message(IM) to the selected contact from the contact list a sticker from collection by name.
			
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $collection - sticker pack ID
			* @param string $name - sticker name
			* @throws RuntimeException
			* @return int
		*/
		public function sendInstantSticker($pkOrNick, $collection, $name): int;
		
		/**
			* returns collection names of stickers.
			
			* @throws RuntimeException
			* @return an array with sticker pack names
		*/
		public function getStickerCollections(): array;
		
		/**
			* returns available names from corresponded collection.
			
			* @param string $collection_name - sticker pack collection name
			* @throws RuntimeException
			* @return an array with sticker names
		*/
		public function getStickerNamesByCollection($collection_name = "Default Stickers"): array;
		
		/**
			* returns available names from corresponded collection.
			
			* @param string $collection_name - sticker pack collection name
			* @throws RuntimeException
			* @return string - encoded image
		*/
		public function getImageSticker($collection_name = "Default Stickers", $sticker_name = "airship", $coder = "BASE64"): string;
		
		/**
			* sends buzz personal message(IM) to the selected contact from the contact list with comments.
			
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $comments - comments
			* @throws RuntimeException
			* @return int
		*/
		public function sendInstantBuzz($pkOrNick = "", $comments = "test"): int;
		
		/**
			* sends sends invitation personal message(IM) to the selected contact from the contact list with description and comments on channel_id.
			
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $channelid - Utopia channel ID
			* @param string $description - request description
			* @param string $comments - your comments
			* @throws RuntimeException
			* @return int
		*/
		public function sendInstantInvitation($pkOrNick, $channelid, $description, $comments): int;
		
		/**
			* removes all personal message(IM) of the selected contact from the contact list.
			
			* @param string $pk - Public Key of the contact
			* @throws RuntimeException
			* @return int
		*/
		public function removeInstantMessages($pk = ""): bool;
		
		/**
			* returns in the Response block the history of communication from personal chat with selected contact. The method is called by using the Public Key parameter, that passes on the Public Key of the contact (Public Key can be recognized by using the getContacts method).
			
			* @param string $pk - Public Key of the contact
			* @throws RuntimeException
			* @return array
		*/
		public function getContactMessages($pk, $query_filter = null): array;
		
		/**
			* sends uMail to the selected contact in the Utopia network.
			
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $subject - message subject
			* @param string $body - message
			* @throws RuntimeException
			* @return array
		*/
		public function sendEmailMessage($pkOrNick, $subject = "test message", $body = "message content"): bool;
		
		/**
			* sends cryptons transfer for the specified amount to the contact or to the card.
			
			* @param string $cardid - crypto card ID
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $amount - payment amount
			* @param string $fromCard - your crypto card ID
			* @throws RuntimeException
			* @return string
		*/
		public function sendPayment($cardid = "", $pkOrNick = "", $amount = 1, $comment = "", $fromCard = ""): string;
		
		/**
			* returns to the Response block the list of identifications of uMail emails in the selected folder by using specified search filter. The method is called by using the FolderType parameters, which pass on the number of the folder from which the list should be taken (numbers of the folders 1-Inbox, 2-Drafts, 4-Sent, 8-Outbox, 16-Trash) and it is possible to specify the Filter parameter, which passes on the text value for the search of emails in uMail (has to contain the full or partial match with the Public Key, Nickname or the text of email).
			
			* @param int $folderType - 1, 2, 4, 8 or 16.
			* @param string $search_filter - (optional) partial matching of the Public Key, Nickname or text in uMail
			* @param Filter $query_filter - (optional) filter which can be applied to ANY method returning an array
			* @throws RuntimeException
			* @return array (of int)
		*/
		public function getEmailFolder($folderType = 1, $search_filter = "", $query_filter = null): array;
		
		/**
			* returns to the Response block the list of detailed of uMail emails in the selected folder by using specified search filter. The method is called by using the FolderType parameters, which pass on the number of the folder from which the list should be taken (numbers of the folders 1-Inbox, 2-Drafts, 4-Sent, 8-Outbox, 16-Trash) and it is possible to specify the Filter parameter, which passes on the text value for the search of emails in uMail (has to contain the full or partial match with the Public Key, Nickname or the text of email).
			
			* @param int $folderType - 1, 2, 4, 8 or 16.
			* @param string $search_filter - (optional) partial matching of the Public Key, Nickname or text in uMail
			* @param Filter $query_filter - (optional) filter which can be applied to ANY method returning an array
			* @throws RuntimeException
			* @return array (of int)
		*/
		public function getEmails($folderType = 1, $search_filter = "", $query_filter = null): array;
		
		/**
			* returns the information based on the selected email in uMail. The method is called by using the Id parameter, which passes on the id of the email (id of the email can be found by using getEmailFolder method).
			
			* @param int $id - id of the email
			* @return array
		*/
		public function getEmailById($id = 33): array;
		
		/**
			* deletes email in uMail. First deletion will move email to the Trash, subsequent will remove from the database.
			
			* @param int $id - id of the email
			* @throws RuntimeException
			* @return bool
		*/
		public function deleteEmail($id = 33): bool;
		
		/**
			* creates response email in uMail for the incoming email and sends it to the contact with new message. The method is called by using the Id parameters, which pass on the id of the email (id of the email can be found by using getEmailFolder method) and Body, which passes on the text of the email in uMail. In the Response field the status of completion of the operation is displayed.
			
			* @param int $id - id of the email
			* @param string $body - message content
			* @param string $subject - message subject
			* @throws RuntimeException
			* @return bool
		*/
		public function sendReplyEmailMessage($id = 33, $body = "my message", $subject = "uMail subject"): bool;
		
		/**
			* creates response email for an incoming email in uMail and sends it to the selected contact with the new message. The method is called by using the 'Id' parameter, which passes on the id of the email (id of the email can be found by using getEmailFolder method); 'To', which passes on the Public Key or Nickname of the user to which the email will be sent; and 'Body', which passes on the text in uMail. In the Response field the status of completion of the operation is displayed.
			
			* @param int $id - id of the email
			* @param string $pkOrNick - Public Key or nick name of the contact
			* @param string $body - message content
			* @param string $subject - message subject
			* @throws RuntimeException
			* @return bool
		*/
		public function sendForwardEmailMessage($id = 33, $pkOrNick = "", $body = "my message", $subject = "uMail subject"): bool;
		
		/**
			* returns in the Response field the information about Utopia financial system (information about fees and limits). Method is called without using any parameters.
			
			* @throws RuntimeException
			* @return bool
		*/
		public function getFinanceSystemInformation(): array;
		
		/**
			* allows to receive the history of financial transactions based on the specifications in the parameters of the filter.
			
			* @throws RuntimeException
			* @return array
		*/
		public function getFinanceHistory($filters = "ALL_TRANSFERS", $referenceNumber = "", $toDate = "", $fromDate = "", $batchId = "", $fromAmount = "", $toAmount = "", $query_filter = null): array;
		
		/**
			* returns in the Response field the current list of cards and their detailed information from uWallet. Method is called without using any parameters.
			
			* @throws RuntimeException
			* @return array
		*/
		public function getCards(): array;
		
		/**
			* sends the request for creation of new card in uWallet. The method is called by using the following parameters: Name, which passes on the name of the new card (can contain between 1 and 32 symbols), Color, which passes on the color of the card ( in RGB format, for example '#FFFFFF') and also can specify the First 4 numbers of the card for customization ( it is possible to change only 4 first symbols, can contain symbols (A-F) and numbers (0-9)). In the Response field the status of completion of the operation is displayed.
			
			* @param stirng $name - card name
			* @param string $name - card color
			* @param string $numbers - 4 numbers for customization
			* @throws RuntimeException
			* @return string
		*/
		public function addCard($name = "new card", $color = "#FFFFFF", $numbers = "0000"): string;
		
		/**
			* deletes the existing card from uWallet. The amount from card will be returned to the main balance. The following parameter is specified: CardId, which passes on the card number ( CardId can be found by using the getCards method). In the Response field the status of completion of the operation is displayed.
			
			* @param string $cardId - crypto card ID
			* @throws RuntimeException
			* @return bool
		*/
		public function deleteCard($cardId): bool;
		
		/**
			* turns on the mining in the Utopia client (mining is available only for x64 client). As a parameter the Status (true/false) is specified, which turns on or off the mining process. In the Response field the status of completion of the operation is displayed.
			
			* @param bool $enabled - set mining status
			* @throws RuntimeException
			* @return bool
		*/
		public function enableMining($enabled = true): bool;
		
		/**
			* turns on and off the daily interest on the remaining irreducible account balance. As a parameter, one of the two statuses, true or false is selected. In the Response field the status of completion of turning on or off the operation is displayed.
			
			* @param bool $enabled - set mining status
			* @throws RuntimeException
			* @return bool
		*/
		public function enableInterest($enabled = true): bool;
		
		/**
			* changes the option of the automatic reading of the mining history from the financial server. As a parameter of the method, the status of true or false is specified. In the Response field the status of completion of turning on or off the operation is displayed.
			
			* @param bool $enabled - set mining status
			* @throws RuntimeException
			* @return bool
		*/
		public function enableHistoryMining($enabled = true): bool;
		
		/**
			* returns in the Response block the status of mining history poll. Method is called without using any parameters.
			Meaning of different states:
				0 = STATE_EMPTY
				1 = STATE_IN_PROGRESS
				2 = STATE_RECEIVED_RESPONSE
			
			* @throws RuntimeException
			* @return bool
		*/
		public function statusHistoryMining(): int;
	}
	