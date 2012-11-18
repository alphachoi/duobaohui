<?php
namespace Snake\Package\Goods;

class Abtest {

	private $mode = 0;

	protected $remainder = array(0);

    public function __construct($mode = 0, $remainder = array()) {
		$this->mode = $mode;
		if (!empty($remainder)) {
			$this->remainder = $remainder;
		}
	}
		
	protected function abtest() {
		$sessionId = $_COOKIE['MEILISHUO_GLOBAL_KEY'];
		$frontTwoWords = substr($sessionId, 0, 2);
		$remainder = hexdec($frontTwoWords) % $this->mode;
		if (in_array($remainder, $this->remainder)) {
			return TRUE;
		}
		return FALSE;
	}

	protected function abtestForNewUser() {
		$sessionId = $_COOKIE['MEILISHUO_GLOBAL_KEY'];
		if (substr($sessionId, -15, 6) != date("ymd")) {
			return FALSE;
		}
		$frontTwoWords = substr($sessionId, 0, 2);
		$remainder = hexdec($frontTwoWords) % $this->mode;
		if (in_array($remainder, $this->remainder)) {
			return TRUE;
		}
		return FALSE;
	
	}
	
}
