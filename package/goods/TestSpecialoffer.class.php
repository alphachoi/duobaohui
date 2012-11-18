<?php
namespace Snake\Package\Goods;

class TestSpecialoffer extends Abtest {

	const SYMBOL = "specialoffer";


	private $testTids = array(
		583903419, 608873934, 608309861, 608310359, 587452977,
		544685241, 609495299, 583818553, 527912329, 591790575,
		583462967, 505398503, 577378371, 562286189, 595207021,
	);

	private $priceIndex = array(
		583903419=>"¥199",
		608873934=>"¥168",
		608309861=>"¥188",
		608310359=>"¥115",
		587452977=>"¥208",
		544685241=>"¥168",
		609495299=>"¥36",
		583818553=>"¥59",
		527912329=>"¥168",
		591790575=>"¥73.5",
		583462967=>"¥332",
		505398503=>"¥166",
		577378371=>"¥80",
		562286189=>"¥89",
		595207021=>"¥49",
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
			return FALSE;
		}
		return in_array($tid, $this->testTids);	
	}

	public function getSpecialByTid($tid = 0) {
		if (isset($this->priceIndex[$tid])) {
			return $this->priceIndex[$tid];
		}	
		return FALSE;
	}
} 
