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
			* @param string $query_filter - (optional) filter which can be applied to ANY method returning an array
			* @return array (of int)
		*/
		public function getEmailFolder($folderType = 1, $search_filter = "", $query_filter = null): array;
		
		/**
			* returns to the Response block the list of detailed of uMail emails in the selected folder by using specified search filter. The method is called by using the FolderType parameters, which pass on the number of the folder from which the list should be taken (numbers of the folders 1-Inbox, 2-Drafts, 4-Sent, 8-Outbox, 16-Trash) and it is possible to specify the Filter parameter, which passes on the text value for the search of emails in uMail (has to contain the full or partial match with the Public Key, Nickname or the text of email).
			
			* @param int $folderType - 1, 2, 4, 8 or 16.
			* @param string $search_filter - (optional) partial matching of the Public Key, Nickname or text in uMail
			* @param string $query_filter - (optional) filter which can be applied to ANY method returning an array
			* @return array (of int)
		*/
		public function getEmails($folderType = 1, $search_filter = "", $query_filter = null): array;
	}
	