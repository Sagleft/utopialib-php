<?php
	namespace UtopiaLib;
	
	class Filter {
		public $sortBy = ""; //string
		public $offset = ""; //int to string
		public $limit  = ""; //int to string
		
		public function __construct($sortBy = "", $offset = "", $limit = "") {
			$this->sortBy = $sortBy;
			$this->offset = $offset;
			$this->limit  = $limit;
		}
	}
	