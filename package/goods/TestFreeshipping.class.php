<?php
namespace Snake\Package\Goods;

class TestFreeshipping extends Abtest {

	const SYMBOL = "freeshipping";


	private $testTids = array(
		556373665,
		596511317,
		611124879,
		567015091,
		490881654,
		525135073,
		436026400,
		440865764,
		376612180,
		543787320,
		592667151,
		601298087,
		611573489,
		522702539,
		597829595,
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
			$tids = array_slice($this->testTids, $frame * 5, 5);
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
