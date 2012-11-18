<?php
namespace Snake\Package\Goods;
/**
 * @author xuanzheng@meilishuo.com
 */
/**
 * Guang页面最新的海报墙
 * @author xuanzheng@meilishuo.com
 *
 */
Use Snake\Package\Goods\Helper\RedisNewestHelper;
Use Snake\Libs\Cache\Memcache;

class Newest {

	private	$offset = 0;
	private $pageSize = 0;
	private $tids = array();
	private $totalNum = 0;
	private $firstViewTid = NULL;
	private $viewTraceHelper = NULL;

	public function __construct($offset = 1, $pageSize = 20) {
		$this->pageSize = $pageSize;
		$this->offset = $offset;
		$this->viewTraceHelper = new NewestViewTrace();	
		if ($this->offset != 0) {
			$this->firstViewTid = $this->viewTraceHelper->getFirstTidFromCache();
			if (empty($this->firstViewTid)) {
				$this->firstViewTid = 0;
			}
		}
	}


	public function setTotalNum() {
			
		if (!empty($this->firstViewTid)) {
			$this->totalNum = RedisNewestHelper::getNumInRedisByScore($this->firstViewTid);
		}
		else{
			$this->totalNum = RedisNewestHelper::getNumInRedis();
		}

		return TRUE;
	}

	public function setTids() {
		///从redis里取Tids
		$twDataByRedis = array();
		$tids = $this->getTidsFromRedis($this->offset, $this->pageSize);
		if(empty($tids)){
			///从twitter_white_list取tids
			$twDataBySql = TwitterWhiteList::getWhiteListTids($this->offset, $this->pageSize);
			$tids = \Snake\Libs\Base\Utilities::DataToArray($twDataBySql, 'twitter_id');
		}
		$this->tids = $tids;
		return TRUE;
	}

	private function getTidsFromRedis($offset, $pageSize) {
		if (!empty($this->firstViewTid)) {
			$totalTids = RedisNewestHelper::getTwitterIdsInRedisByFirstTid($this->firstViewTid);
			$tids = array();
			if (!empty($totalTids) && $offset * $pageSize < count($totalTids)) {
				$tids = array_slice($totalTids, $offset * $pageSize, $pageSize);
			}
		}
		else {
			$tids = RedisNewestHelper::getTwittersInRedis($offset, $pageSize);	
		}
		return $tids;
	}

	public function getTids() {
		if ($this->offset == 0 ) {
			$this->putTrace($this->tids[0]);
		}
		return $this->tids;
	}

	public function getTotalNum() {
		return $this->totalNum;
	} 
	
	private function putTrace($tid = 0) {
		return $this->viewTraceHelper->putFirstTidIntoCache($tid);
	}
}
