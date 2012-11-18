<?php
namespace Snake\Package\Goods;
Use Snake\Package\Base\IdentityObject;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Base\DomainObjectAssembler; 
Use Snake\Package\Goods\Helper\RedisAttrClickHelper; 

/**
 * 
 * 属性按ctr1排序测试的tids，数据库相关操作
 *
 * 新来用户的属性页 属性页海报墙
 *
 * @author Xuan Zheng
 * @package 宝库
 */
class AttrTwitterCtr1 {
	private $wordId = 0;
	private $offset = 0;
	private $pageSize = 20;
	private $orderBy = 'ctr1';
	private $tids = array();
	private $totalNum = array();

	/**
	 * 从数据库中取tids
	 * @param array 查询条件
	 * @param string 想要的查询结果
	 * @return array tids
	 */
	private function getTids ($param, $sqlCol = "*") {
		if (empty($param['word_id'])) {
			return array();
		}
		$sqlCol = explode(",", $sqlCol);

		$identityObject = new IdentityObject();
		$identityObject->field('aid')->eq($param['word_id']);
		if (!empty($param['orderby'])) {
			$identityObject->orderby($param['orderby']);
		}
		if (!empty($param['limit'])) {
			$limit = "{$param['limit']['start']},{$param['limit']['limit']}";
			$identityObject->limit($limit);
		}
		$identityObject->col($sqlCol);
		$domainObjectAssembler = new DomainObjectAssembler(AttrTwitterCtr1PersistenceFactory::getFactory('\Snake\Package\Goods\AttrTwitterCtr1PersistenceFactory'));
		$attrTwitterCtr1Collection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($attrTwitterCtr1Collection->valid()) {
			$attrTwitterCtr1Obj = $attrTwitterCtr1Collection->next();
			$twitterIds[] = $attrTwitterCtr1Obj->getTwitterIds();
		}
		return $twitterIds;
	}

	/**
	 * set 偏移量 offset
	 * @param int 
	 * @return boolean
	 */
	public function setOffset($offset = 0) {
		$this->offset = (int)$offset;	
		return TRUE;
	}


	/**
	 * set page size
	 * @param int 
	 * @return TRUE
	 */
	public function setPageSize($pageSize = 0) {
		$this->pageSize = (int)$pageSize;
		return TRUE;
	}

	/**
	 * set word id
	 * @param int 
	 * @return boolean
	 */
	public function setwordId($wordId, $wordName) {
		$wordId = (int)$wordId;
		if (empty($wordId) && empty($wordName)) {
			return FALSE;
		}
		$params = array();
		$params['isuse'] = 1;
		if (!empty($wordId)) {
			$params['word_id'] = $wordId;
		}
		else if (!empty($wordName)) {
			$params['word_name'] = $wordName;
		}
		else {
			return FALSE;
		}
		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute-zx*/ word_id,word_name,same_to");
		if (empty($wordInfo)) {
			return FALSE;
		}
		if (!empty($wordInfo[0]['same_to'])) {
			$this->wordId = $wordInfo[0]['same_to'];
		}
		else {
			$this->wordId = $wordInfo[0]['word_id'];
		}
		return TRUE;
	}

	/**
	 * set orerby
	 * @param string default ctr1
	 * @return boolean
	 */
	public function setOrderBy($orderBy = 'ctr1') {
		$this->orderBy = $orderBy;
		return TRUE;
	}

	/**
	 * 获取twitter ids
	 * @return array tids
	 */
	private function getTwitterIds() {
		$cacheHelper = Memcache::instance();
		$cacheKey = "ATTRCTR1:word_id:{$this->wordId}_orderby:{$this->orderBy}_limit:{$this->offset}_{$this->pageSize}";
		$tidsCache = $cacheHelper->get($cacheKey);
		$tids = $tidsCache;
		if (empty($tidsCache)) {
			$tids = RedisAttrClickHelper::getTwittersInRedis($this->wordId, $this->offset * $this->pageSize, $this->pageSize);
		}

		if(empty($tidsCache) && empty($tids)) {
			$params = array();
			$params['word_id'] = $this->wordId;
			$params['orderby'] = "ctr1_opt desc";
			$params['limit'] = array('start' => $this->offset * $this->pageSize, 'limit' => $this->pageSize);
			$tidsTmp = $this->getTids($params, "/*attrTwitterCtr1-zx*/tid");
			$tids = \Snake\Libs\Base\Utilities::DataToArray($tidsTmp, 'tid');
		}
		if (empty($tidsCache) && !empty($tids)) {
			$cacheHelper->set($cacheKey, $tids, 7200);
		}
//		$this->tids = $tids;
		return $tids;
	}

	/**
	 * 获取total number
	 * @return int
	 */
	public function getTotalNum() {
		$cacheHelper = Memcache::instance();
		$cacheKey = "ATTRCTR1:TotalNum:word_id:{$this->wordId}";
		$totalNumCache = $cacheHelper->get($cacheKey);
		$totalNum = $totalNumCache;
		if (empty($totalNum)) {
			$totalNum = RedisAttrClickHelper::getNumInRedis($this->wordId);
		}
		if (empty($totalNum)) {
			$params = array();
			$params['word_id'] = $this->wordId;
			$totalNumTmp= $this->getTids($params, "/*attrTwitterCtr1-zx*/count(tid) num");
			$totalNum = $totalNumTmp[0]['num'];
			$this->totalNum = $totalNum;	
		}
		if (empty($totalNumCache) && !empty($totalNum)) {
			$cacheHelper->set($cacheKey, $totalNum, 7200);
		}
		return $totalNum;
	}

	/**
	 * 获取tids && total num
	 * @return array array('tids' => $tids, 'totalNum' => $totalNum)
	 */
	public function getTidsAndTotalNum() {
		$tids = $this->getTwitterIds();
		$totalNum = $this->getTotalNum();
		return array('tids' => $tids, 'totalNum' => $totalNum);
	}
}
