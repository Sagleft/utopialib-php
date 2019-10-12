<?php
	namespace UtopiaLib;
	
	class Utilities {
		function isJson($string = ""): bool {
			return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
		}
		
		function json_decode_nice($json = "", $assoc = FALSE){ 
			$json = str_replace(["\n", "\r"], "", $json);
			$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json);
			$json = preg_replace('/(,)\s*}$/', '}', $json);
			return json_decode($json, $assoc);
		}
		
		function filterFolderType($folderType = 1) {
			switch($folderType) {
				default:
					$folderType = 1; break;
				case 1:
					//inbox
					break;
				case 2:
					//drafts
					break;
				case 4:
					//sent
					break;
				case 8:
					//outbox
					break;
				case 16:
					//trash
					break;
			}
			return $folderType;
		}
	}
	