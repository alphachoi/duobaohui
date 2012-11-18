<?php
namespace Snake\Package\Goods;


Use Snake\Package\Verify\TwitterVerify;
class MatchShow {

	private $offset = 0;
	private $pageSize = 20;

	function __construct($offset = 0, $pageSize = 20) {
		$this->offset = (int)$offset;
		$this->pageSize = (int)$pageSize;	
	}

	private function tidsDataProcess ($offset = 0, $pageSize = 20) {
		if ($offset == 0) { 
			$params = array();
			$params['orderby'] = "order_no asc";
			$params['limit'] = "0, 32";
			$tidFromGoodsReport = GoodsReport::getGoodsReport($params, "/*MatchShow-zx*/twitter_id"); 
		}
		$params = array();
		$params['verify_stat'] = 1;
		$params['orderby'] = 'twitter_id desc';
		$offNum = $offset * $pageSize;
		$params['limit'] = "{$offNum}, {$pageSize}";
		$tidFromTwitterVerify = TwitterVerify::getTwitterVerify($params, "/*MatchShow-zx*/twitter_id");
		if (!empty($tidFromGoodsReport)) {
			$tidsTmp = array_merge($tidFromGoodsReport, $tidFromTwitterVerify);
		}
		else {
			$tidsTmp = $tidFromTwitterVerify;
		}
		$tids = array();
		if (!empty($tidsTmp)) {
			foreach ($tidsTmp as $tid) {
				array_push($tids,$tid['twitter_id']);
			} 
		}
		return $tids;
	}

	private function totalNumDataProcess() {
		$totalNum = 10800;
//		$params = array();
//		$params['verify_stat'] = 1;
//		$totalNumTmp= TwitterVerify::getTwitterVerify($params, "/*MatchShow-zx*/count(twitter_id) totalnum");
//		$totalNum = $totalNumTmp['totalnum'];
		return $totalNum;
	}

	public function getTids() {
		$tids = $this->tidsDataProcess($this->offset, $this->pageSize);
		return $tids;
	} 

	public function getTotalNum() {
		$totalNum = $this->totalNumDataProcess();	
		return $totalNum;
	}















} 
