<?php
	use PHPUnit\Framework\TestCase;
	use UtopiaLib\Utilities as Utils;
	
	class UtilitiesTest extends TestCase {
		
		public function testUtilsIsJSON() {
			$json = '{"paper": "A4", "count": 5}';
			$status_success = Utils::isJson($json);
			$this->assertTrue($status_success);
		}
		
		public function testUtilsJson2Obj2Json() {
			$json = '{"daemon":"sarangai","tag":"graphite"}';
			$obj = Utils::json_decode_nice($json);
			$new_json = json_encode($obj);
			//echo $new_json;
			$this->assertTrue($new_json == $json);
		}
	}
	