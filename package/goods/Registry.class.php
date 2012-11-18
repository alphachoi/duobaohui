<?php
namespace Snake\Package\Goods;

class Registry {
	private static $instance;
	private $request;
	private function __construct() {
	}	

	public static function instance() {
		if(!isset(self::$instance)) {
			self::$instance = new self();
		}		
		return self::$instance;
	}

	public function getRequest() {
		return $this->request;
	}

	//public function setRequest(Request $request) {
	public function setRequest($request) {
		$this->request = $request;	
		return TRUE;
	}
}
