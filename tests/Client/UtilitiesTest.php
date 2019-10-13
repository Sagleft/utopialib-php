<?php
	use PHPUnit\Framework\TestCase;
	use UtopiaLib\Utilities as Utils;
	
	class UtilitiesTest extends TestCase {
		
		public function testUtilsIsJSON() {
			$json = '{"paper": "A4", "count": 5}';
			$status_success = Utils::isJson($json);
			$this->assertTrue($status_success);
			
			$status_success = Utils::isJson("X25");
			$this->assertTrue(!$status_success);
		}
		
		public function testUtilsJson2Obj2Json() {
			$json = '{"daemon":"sarangai","tag":"graphite"}';
			$obj = Utils::json_decode_nice($json);
			$new_json = json_encode($obj);
			//echo $new_json;
			$this->assertTrue($new_json == $json);
		}
		
		public function testUtilsParseFinanceQueryFilters() {
			$filters = "ALL_CARDS,INCOMING_CARDS,ACTIVATED_VOUCHERS";
			$parsed_filters = Utils::parseFinanceQueryFilters($filters);
			$parsed_array = explode(',', $parsed_filters);
			$status_success = $parsed_filters != "" && count($parsed_array) == 3;
			$this->assertTrue($status_success);
		}
		
		public function testUtilsFilterHexColor() {
			$color = "#dfdfdf";
			$color_filtered = Utils::filterHEXColor($color);
			$this->assertTrue($color_filtered == $color);
			
			$color_filtered = Utils::filterHEXColor("#dxcdcd");
			$this->assertTrue($color_filtered != $color);
		}
	}
	