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
		
		function parseFinanceQueryFilters($filters = "ALL_TRANSFERS"): string {
			$delimiter = ',';
			$filters_accepted = [
				"ALL_CARDS",
				"INCOMING_CARDS",
				"OUTGOING_CARDS",
				"CREATED_CARDS",
				"DELETED_CARDS",
				"ALL_TRANSFERS",
				"INCOMING_TRANSFERS",
				"OUTGOING_TRANSFERS",
				"ALL_REQUESTS",
				"AWAITING_REQUESTS",
				"AUTHORIZED_REQUESTS",
				"DECLINED_REQUESTS",
				"CANCELED_REQUESTS",
				"EXPIRED_REQUESTS",
				"ALL_APPROVED_REQUESTS",
				"CREATED_VOUCHERS",
				"CREATED_VOUCHERS_BATCH",
				"ACTIVATED_VOUCHERS",
				"DELETED_VOUCHERS",
				"ALL_VOUCHERS",
				"ALL_MINING",
				"ALL_INTEREST",
				"ALL_FEE",
				"ALL_UNS_RECORDS",
				"UNS_UNS_REGISTRATION",
				"UNS_UNS_CHANGED",
				"UNS_UNS_TRANSFERRED",
				"UNS_UNS_DELETED",
				"ALL_TRANSACTIONS"
			];
			$filters_arr = explode($delimiter, $filters);
			$result_filters = [];
			for($i = 0; $i < count($filters_arr); $i++) {
				$test_filter = $filters_arr[$i];
				if(in_array($test_filter, $filters_accepted)) {
					$result_filters[] = $test_filter;
				}
			}
			if($result_filters == []) {
				return "";
			} else {
				return implode($delimiter, $result_filters);
			}
		}
		
		function filterHEXColor($color = "#ffffff"): string {
			$color_default = "#ffffff";
			if($color == "") {
				return $color_default;
			}
			
			$hex = str_replace('#', '', $color);
			if(!ctype_xdigit($hex)) {
				return $color_default;
			}
			$hex = str_pad($hex, 6, "0");
			return "#" . $hex;
		}
	}
	