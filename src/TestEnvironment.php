<?php
  class TestEnvironment {
    public function __construct() {
      $dotenv = \Dotenv::create(__DIR__ . "/../");
    	//load environment variables
    	$dotenv->load();
    }
  }
