<?php
namespace Snake\Package\Goods;

class TestMoreinfo extends Abtest {

	const SYMBOL = "moreinfo";


	private $testTids = array(
	);

	public function isAbtest() {
		if (parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}

	public function getTestTids($testName, $frame, $page) {
		$tids = array();
		if ($testName == self::SYMBOL && $page == 0 && $frame < 3 && $frame >= 0) {
			$tids = $this->testTids;
		}	
		if (date("Y-m-d") >= '2012-11-11') {
			return array();
		}
		return $tids;
	}

	public function judgeTidInTetst($tid = 0) {
		if (date("Y-m-d") >= '2012-11-11') {
			return array();
		}
		return in_array($tid, $this->testTids);	
	}
} 
