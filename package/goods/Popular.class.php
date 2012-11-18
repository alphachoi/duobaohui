<?php
/**
 * 逛宝贝 热榜 的海报相关描述的文件
 * @author zhengxuan <xuanzhegn@meilishuo.com>
 */
namespace Snake\Package\Goods;
USE Snake\Package\Goods\Helper\RedisPopularHelper;

/**
 *
 * 逛宝贝 热榜 相关描述类 
 * @author zhengxuan <xuanzheng@meilishuo.com>
 *
 */
class Popular {

	private $offset = 0;
	private $pageSize = 20;
	private $tids = array();
	private $totalNum = 0;
	private $pageKey = 'pop24';
	private $plan = NULL;
	private $placeAb = FALSE;
	static  $pageKeyOpt = array('pop24', 'pop7');
	
	/**
	 * new一个Popular class之后需要set相关数据，offset－从哪，pageSize－取多少
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @param int $offset 从哪开始取数据
	 * @param int $pageSize 取多少tid
	 * @return bool TRUE 现阶段只有返回TRUE和不返回两种
	 * @access public
	 * @todo 这样写感觉不是很好
	 */
	public function setData($pageKey = 'pop24', $offset = 0, $pageSize = 20) {
		$this->setPageKey($pageKey);
		$this->setOffset($offset);
		$this->setPageSize($pageSize);	
		return TRUE;
	}

	static function popularJudge($pageKey) {
		if (!in_array($pageKey, self::$pageKeyOpt)) {
			return FALSE;
		}
		return TRUE;
	} 

	private function setPageKey($pageKey) {
		if ($pageKey == 'popular') {
			$pageKey = 'pop7';
		}
		else {
			$pageKey = 'pop24';
		}
		$this->pageKey = $pageKey;
		return TRUE;
	}

	public function setPlan($plan = FALSE) {
		$this->plan = $plan;
		return TRUE;
	}

	private function setOffset($offset) {
		$this->offset = $offset;
		return TRUE;
	}

	private function setPageSize($pageSize) {
		$this->pageSize = $pageSize;	
		return TRUE;
	}

	private function getTopTwitter() {
		$topPopTwitter = new TopPopTwitter();	
		return  $topPopTwitter->getTopTids($this->placeAb);
	}

	public function setAb($ab) {
		$this->placeAb = $ab;	
		return TRUE;
	}

	public function setTids() {

		if ($this->pageKey == 'pop24') {
			$topTids = $this->getTopTwitter();
		}
		else{
			$topTids = array();	
		}
		$topNum = count($topTids);
		$topTids = array_slice($topTids, $this->offset * $this->pageSize, $this->pageSize);

		if ($this->offset - (int)($topNum / $this->pageSize) > 0) {
			$this->offset = $this->offset -  (int)($topNum / $this->pageSize);
		}
		else {
			$this->offset = 0;
		}

		if ($this->pageSize - count($topTids)> 0) {
			$this->pageSize = $this->pageSize - count($topTids);
		}
		else {
			$this->pageSize = 0;
		}

		$redisKey = $this->getKey($this->pageKey);
		$tids = RedisPopularHelper::getTwittersInRedis($redisKey, $this->offset, $this->pageSize);
		$this->tids = $tids;
		if (is_array($topTids) && is_array($this->tids)) {
			$this->tids = array_merge($topTids, $this->tids);
		}
		return TRUE;
	} 

	public function setTotalNum() {
		$redisKey = $this->getKey($this->pageKey);
		$total = RedisPopularHelper::getNumInRedis($redisKey);
		$this->totalNum = $total;
		return TRUE;
	}

	/**
	 * 取得热榜推的总数
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @return int $totalNum
	 */
	public function getTotalNum() {
		return $this->totalNum;
	}

	/**
	 * 取得热榜的推
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 */
	public function getTids() {
		return $this->tids;
	}

	private function getKey() {
		$redisKey = RedisPopularHelper::getRedisKey($this->pageKey);
		return $redisKey;
	}

	public function removeTid($tids = array()) {
		if (empty($tids)) {
			return FALSE;
		}
		$keyName = $this->getKey();
		return RedisPopularHelper::removeTidInRedis($tids, $keyName);
	}


}
