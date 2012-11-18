<?php
namespace Snake\Package\Goods;

Use Snake\Package\Search\Search;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Recommend\Recommend;
Use Snake\Package\Twitter\Twitter;
Use Snake\Package\Group\Groups;
Use Snake\Package\Group\GroupTwitters;
Use Snake\Package\Goods\Abtest;
Use Snake\Package\Goods\TopAttrTwitter;

class Attribute extends AttributeBase {


	private $wordId = NULL;
	private $swid = 0;
	private $wordName = NULL;
	private $wordInfo = NULL;
	private $price = NULL;
	//置顶推(销售推广置顶)
	private $twitterId = NULL;
	//ABTEST,
	private $testData = array();
	private $testTids = array();
	
	//置顶推(前置顶)
	private $topTids = array();	
	private $plan = FALSE;


	public function __construct() {
	}

	public function setData($wordId = 0, $wordName = '', $offset, $orderby = 'weight', $price = '', $filterWord, $pageSize, $twitterId = 0) {
		$this->setWordId($wordId);
		$this->setBrandName($wordName);
		$this->setOffset($offset);
		$this->setPrice($price);
		$this->setTwitterId($twitterId);
		$this->setOrderby($orderby);
		$this->setFilterWord($filterWord);
		$this->setPageSize($pageSize);
	}


	private function setTopTwitter($aid = 0) {
		if (empty($aid)) {
			return FALSE;	
		}
		$topTids = $this->getTopTwitter($aid, $this->plan);		
		
		$topTidNum = count($topTids);
		
		$offsetNum = (int)($topTidNum / $this->pageSize);

		$topTids = array_slice($topTids, $this->offset * $this->pageSize, $this->pageSize);
		if (!empty($topTidNum)) {
			$this->offset = ($this->offset - $offsetNum) >= 0 ? $this->offset - $offsetNum : 0;
		}
		if (!empty($topTids)) {
			$pageSize = ($this->pageSize - count($topTids)) >= 0 ? $this->pageSize - count($topTids) : 0;
			//$this->setOffset(0);
			$this->setPageSize($pageSize);
			$this->topTids = $topTids;
		}
		
		return TRUE;
	}

	public function setPlan($plan = FALSE) {
		$this->plan = $plan;
		return TRUE;
	}

	public function setTestData($testData) {
		$this->testData = $testData;
		return TRUE;
	}

	public function setBrandName($wordName) {
		$this->wordName = $wordName;	
	}

	public function setTwitterId($twitterId) {
		$this->twitterId = $twitterId;	
		return TRUE;
	}

	public function setPrice($price) {
		$this->price = $price;
		return TRUE;
	}

	public function setOrderby($orderby) {
		$this->orderby = $orderby;
		return TRUE;	
	}

	public function setFilterWord($filterWord) {
		$this->filterWord = $filterWord;
		return TRUE;
	}

	public function setPageSize($pageSize) {
		$this->pageSize = $pageSize;
		return TRUE;
	}

	public function setWordId($wordId) {
		$this->wordId = $wordId;
		return TRUE;
	}

	public function setOffset($offset) {
		$this->offset = $offset;
		return TRUE;	
	}

	public function dataProcess() {
		if (!$this->_init()) {
			return FALSE;
		}
		if (!$this->setTidsAndTotalNum()) {
			return FALSE;
		}
		return TRUE;
	}

	private function _init() {
		$this->setPriceFilter();
		$this->setVerifyFilter();
		$this->setWordInfo();
		$this->setAttrs();
		$this->setExcludeIds();
		$this->setTopTwitter($this->swid);
		return TRUE;
	}

	private function setPriceFilter() {
		if ($this->price != "all") {
			$priceRe = explode("~",$this->price);
			$from = (float)$priceRe[0]; 
			$to = isset($priceRe[1])? (float)$priceRe[1] : NULL; 
			if (isset($from) && isset($to)) {
				$this->filter['goods_price'] = array( 'field'  => 'goods_price', 'from' => $from, 'to' => $to);
			}
		}
		return TRUE; 
	}

	private function setVerifyFilter() {
		$this->filter['verify_stat'] = array('field' => 'verify_stat', 'from' => 1, 'to' => 2 );
		return TRUE;
	}

	private function setWordInfo() {
		$params = array();
		$params['isuse']   = 1;
		if (!empty($this->wordId)) {
			$params['word_id'] = $this->wordId;
		}
		else if (!empty($this->wordName)) {
			$params['word_name'] = $this->wordName;
		}
		else {
			return array();
		} 
		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute-zx*/ word_id,word_name,same_to");
		if (empty($wordInfo)) {
			return array();
		}
		$this->wordId = $wordInfo[0]['word_id'];
		$this->swid = $this->wordId;
		if (!empty($wordInfo[0]['same_to'])) {
			$this->wordId = $wordInfo[0]['same_to'];
			$params = array();
			$params['word_id'] = $this->wordId;
			$params['isuse']   = 1;
			$wordInfo = AttrWords::getWordInfo($params, "/*Attribute-zx*/ word_id,word_name,same_to");
		}
		$this->wordInfo = $wordInfo;
		return TRUE;
	}

	private function setAttrs() {
		$attrs = array();
		$attrs = AttrWords::getSearchString ($this->wordId, $this->wordInfo[0]['word_name'], $this->filterWord);
		if (empty($attrs)) {
			return FALSE;
		}
		$this->attrs = $attrs;
		return TRUE;
	}

	private function setExcludeIds() {
		$excludeIds = $this->getBlackTids($this->wordId);
		$this->excludeIds = $excludeIds;
		return TRUE;
	}

	private function getTopTwitter($aid = 0, $plan = FALSE) {
		if ( is_array($this->price) || !empty($this->filterWord) || $this->orderby != 'weight') {
			return array();
		}
		$topTwitterHelper = new TopAttrTwitter();
		$topTids = $topTwitterHelper->getAttrTopTwitter($aid, $plan);	
		return $topTids;	
	}

	private function setTidsAndTotalNum() {
		$func = "getGoodsByAttrFromSearch";
		$searchRes = parent::$func();
		if ($searchRes != false && $searchRes['total'] < 1200)  {
			unset($this->filter['verify_stat']);
			$searchRes = parent::$func();
		}
		if (empty($searchRes['matches']) && $this->swid != 34359 && $this->swid != 34515) {
			return FALSE;
		}
		$tids = \Snake\Libs\Base\Utilities::DataToArray($searchRes['matches'], 'attrs');
		$this->tids = \Snake\Libs\Base\Utilities::DataToArray($tids, 'twitter_id');
		$this->totalNum = min($searchRes['total'], $searchRes['total_found']);
		return TRUE;
	}

	private function mergeTopTids($topTids = array(), $tids = array()) {
		if (empty($topTids) & !is_array($topTids)) {
			return $tids;
		}
		$tids = array_merge($topTids, $tids);
		return $tids;
	}

	public function getTids() {

		$this->tids = $this->mergeTopTids($this->topTids, $this->tids);

		//push twitter_id
		if (!empty($this->twitterId) && $this->offset == 0) {
			$tidDiff = array_diff($this->tids, array($this->twitterId));
			array_unshift($tidDiff, $this->twitterId);
			$this->tids = $tidDiff;
		}

		//两个test
		$this->abtest1();
		$this->abtest2();

		//test 的tids1
		if (!empty($this->testTids['ntids1']) && is_array($this->testTids['ntids1'])) {
			$testTidDiff = array_diff($this->tids, $this->testTids['ntids1']);	
			$this->tids = array_merge($this->testTids['ntids1'], $testTidDiff);
		}

		if (!empty($this->testTids['ntids2']) && is_array($this->testTids['ntids2'])){
			$groupNum = count($this->testTids['ntids2']);

			if (isset($this->testData['group_frame']) && $groupNum > $gstart * $getLength) {
				$groupTids = array_slice($this->testTids['ntids2'], $this->testData['group_frame'] * $this->pageSize, $this->pageSize);	
				if (!empty($groupTids)) {
					$this->tids = $groupTids;	
				}
			}
		}


		return $this->tids;	
	}

	public function getTotalNum() {
		$topTids = $this->getTopTwitter($this->swid);
		$this->totalNum += count($topTids);
		return $this->totalNum;
	}

	private function getBlackTids(){
		$param['data_type'] = "1";
		$param['word_id'] = $this->wordId;
		$blacklist = AttrWords::getAttrBlackList($param, "/*Attribute-zx*/twitter_id");
		$excludeIds = \Snake\Libs\Base\Utilities::DataToArray($blacklist, 'twitter_id');
		return $excludeIds;
	}

	/**
	 *
	 * @param $attrInfo中需要包括attr的相关信息(必须包括attrid和attrtitle)
	 * @param $page
	 * @param $pageSize
	 * @param $orderby
	 * @param $exludeBlacklist	排除黑名单中的推（已作废，现在改成按宝贝取了）
	 */ 
	public static function getGoodsByAttrIds($attrInfo, $page = 0, $pageSize = 20, $orderby = 'random', $exludeBlacklist = true, $extBlackList = array()) {
		$searchObj = new Search();
		$max = 20;
		$sc = $searchObj->_getSphinxClient();
		$sc->SetLimits($page * $pageSize, $pageSize, $max);

		//设置排序
		$searchTime = time();
		$begin_time = $searchTime - 100 * 24 * 3600;
		if ($orderby == 'random') {
			$sc->SetSortMode( SPH_SORT_EXTENDED, '@random');
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
		} 
		else if ($orderby == 'id') {
			$sc->SetSortMode( SPH_SORT_EXTENDED, '@id DESC' );
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
		} 
		else {
			$sc->SetSortMode( SPH_SORT_EXTENDED, 'rank_like DESC, @weight DESC ,@id DESC' );
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
		}

		//搜索最新一个宝贝用Memcache缓存
		if ($orderby == 'weight') {
			$cacheObj = Memcache::instance();
		}

		$ret = array();
		foreach ($attrInfo as $k => $v) {
			if (isset($cacheObj)) {
				$gids = $cacheObj->get('AGID_' . $v['word_id']);
				if (!empty($gids['gid'])) {
					$tmpGid = $gids['gid'];
					shuffle($tmpGid);
					$gids['gid'] = $tmpGid;
					$gid = array_slice($gids['gid'], 0, $pageSize);
					$ret[$v['word_id']]['gid'] = $gid;
					$ret[$v['word_id']]['total'] = $gids['total'];
					continue;
				}
			}
			$attrs = array();
			$attrs[] = $v['word_name'];
			//过滤黑名单中的推
			if (!empty($extBlackList)) {
				$sc->SetFilter('goods_id_attr', $extBlackList, true);
			}
			$sc->SetLimits($page * $pageSize, $max, $max);
			//设置关键词和搜索方式
			$keywords = implode(')|(', $attrs);
			$keywords = "(" . $keywords . ")";
			$sc->SetMatchMode( SPH_MATCH_BOOLEAN );

			$searchRes = $searchObj->queryViaValidConnection($keywords, 'goods_id_dist');
			if (isset($searchRes['matches'])) {
				$gids = \Snake\Libs\Base\Utilities::DataToArray($searchRes['matches'], 'id');
				if (empty($gids)) {
					continue;
				}
				$ret[$v['word_id']]['gid'] = $gids;
				$ret[$v['word_id']]['total'] = max( array(
					$searchRes['total'],
					$searchRes['total_found']
				));
				if (isset($cacheObj)) {
					$cacheObj->set( "AGID_{$v['word_id']}" , $ret[$v['word_id']], 3600 * 2);	//10小时过期
				}
				$ret[$v['word_id']]['gid'] = array_slice($gids, 0, $pageSize);
			}
		}
		return $ret;
	}

	public static function getTwittersByAttrIds($attrInfo, $page = 0, $pageSize = 20, $orderby = 'random', $exludeBlacklist = true, $extBlackList = array()) {
		if (empty($attrInfo)) {
			return array();
		}
		$searchObj = new Search();
		$max = 20;
		$sc = $searchObj->_getSphinxClient();
		$sc->SetLimits($page * $pageSize, $pageSize, $max);

		//设置排序
		$searchTime = time();
		$begin_time = $searchTime - 100 * 24 * 3600;
		if ($orderby == 'random') {
			$sc->SetSortMode( SPH_SORT_EXTENDED, '@random');
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
		} 
		else if ($orderby == 'id') {
			$sc->SetSortMode( SPH_SORT_EXTENDED, '@id DESC' );
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
		} 
		else {
			$sc->SetSortMode(SPH_SORT_EXTENDED, 'rank_like DESC, @weight DESC ,@id DESC');
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
		}

		//搜索最新一个宝贝用Memcache缓存
		if ($orderby == 'weight') {
			$cacheObj = Memcache::instance();
		}

		$ret = array();
		foreach ($attrInfo as $k => $v) {
			if (isset($cacheObj)) {
				$tids = $cacheObj->get('AGID_'.$v['word_id']);
				if (!empty($tids['tid'])) {
					$tmpTid = $tids['tid'];
					shuffle($tmpTid);
					$tids['tid'] = $tmpTid;
					$tid = array_slice($tids['tid'], 0, $pageSize);
					$ret[$v['word_id']]['tid'] = $tid;
					$ret[$v['word_id']]['total'] = $tids['total'];
					continue;
				}
			}
			$attrs = array();
			$attrs[] = $v['word_name'];
			//过滤黑名单中的推
			if (!empty($extBlackList)) {
				$sc->SetFilter('goods_id_attr', $extBlackList, true);
			}
			$sc->SetLimits($page * $pageSize, $max, $max);
			//设置关键词和搜索方式
			$keywords = implode(')|(', $attrs);
			$keywords = "(" . $keywords . ")";
			$sc->SetMatchMode(SPH_MATCH_BOOLEAN);

			$searchRes = $searchObj->queryViaValidConnection($keywords, 'goods_id_dist');
			if (isset($searchRes['matches'])) {
				$tidsTmp = array();
				//$tids = \Snake\Libs\Base\Utilities::DataToArray($searchRes['matches'], 'id');
				foreach ($searchRes['matches'] as $res) {
					$tidsTmp[] = $res['attrs']['twitter_id'];
				}
				$tids = $tidsTmp;
				if (empty($tids)) {
					continue;
				}
				$ret[$v['word_id']]['tid'] = $tids;
				$ret[$v['word_id']]['total'] = max( array(
					$searchRes['total'],
					$searchRes['total_found']
				));
				if (isset($cacheObj)) {
					$cacheObj->set("AGID_{$v['word_id']}" , $ret[$v['word_id']], 3600 * 2);	//10小时过期
				}
				$ret[$v['word_id']]['tid'] = array_slice($tids, 0, $pageSize);
			}
		}
		return $ret;
	}

	private function getCpcTwitterByName($wordName = '') {
		if (empty($wordName)) {
			return empty($wordName);
		}
		$registry = Registry::instance();
		$registry->setRequest(new PosterRequest());
		$request = $registry->getRequest();
		$request->setWordName($wordName);
		$cpcHelper = new CpcGoods();	
		$cpcHelper->setVerifyFilter();
		$result = $cpcHelper->search();	
		$tids = array();
		if (!empty($result['matches'])) {
			foreach ($result['matches'] as $attrs) {
				$tids[] = $attrs['attrs']['twitter_id'];	
			}	
		}
		return array_slice($tids, 0, 20);
	}

	public function getRecommendKeywords($wordId, $num = 20) {
		$recommendHelper = new Recommend();		
		$reAttrWords = $recommendHelper->getReAttrByAid($wordId, 0, $num);

		$wordIds = array();
		if (empty($reAttrWords)) {
			return array();
		}
		foreach ($reAttrWords as $word) {
			$wordIds[] = $word['word_id'];	
		}
		$params = array();
		$params['word_id'] = $wordIds;
		$params['isuse']   = 1;
		$wordInfoTmp = AttrWords::getWordInfo($params, "/*Attribute-zx*/ word_id,word_name,label_id");

		$wordInfo = array();
		if (empty($wordInfoTmp)) {
			return array();	
		}
		foreach ($wordInfoTmp as $word) {
			if (AttrWords::IsBrandWordsByLabel($word['label_id'])) {
				$word['isBrand'] = TRUE;	
			}
			else {
				$word['isBrand'] = FALSE;
			}

			$wordInfo[$word['word_id']] = $word;
		}
		$wanted = array();

		foreach ($wordIds as $word) {
			if (empty($wordInfo[$word])) {
				continue;
			}
			$tmp = array();
			$tmp['word_id'] = $wordInfo[$word]['word_id'];
			$tmp['word_name'] = $wordInfo[$word]['word_name'];
			$tmp['isBrand'] = $wordInfo[$word]['isBrand'];
			$wanted[] = $tmp;
		} 

		return $wanted;
	}

	public function getTwitterAndPicByAttr($wordId) {
		$recommendHelper = new Recommend();		
		$reAttrWords = $recommendHelper->getReAttrByAid($wordId);

		$wordIds = array();
		if (empty($reAttrWords)) {
			return array();
		}
		foreach ($reAttrWords as $word) {
			$wordIds[] = $word['word_id'];	
		}
		$params = array();
		$params['word_id'] = $wordIds;
		$params['isuse']   = 1;
		$wordInfoTmp = AttrWords::getWordInfo($params, "/*Attribute-zx*/ word_id,word_name,label_id");

		$wordInfo = array();
		if (empty($wordInfoTmp)) {
			return array();	
		}
		foreach ($wordInfoTmp as $word) {
			if (AttrWords::IsBrandWordsByLabel($word['label_id'])) {
				$word['isBrand'] = TRUE;	
			}
			else {
				$word['isBrand'] = FALSE;
			}

			$wordInfo[$word['word_id']] = $word;
		}

		$wanted = array();
		foreach ($wordInfo as $reWord) {
			$tmp = array();
			$tmp['word_id'] = $reWord['word_id'];
			$tmp['word_name'] = $reWord['word_name'];
			$tmp['twitter_id'] = $wordsMap[$reWord['word_id']];
			$tmp['isBrand'] = $reWord['isBrand'];
			$wanted[] = $tmp;
		}

		return $wanted;
	}

	public function getNineGroupByAttr($wordInfo, $userId, $magFavor = '') {
		$params = array();
		$params['isuse'] = 1;
		if (!empty($wordInfo['word_id'])) {
			$params['word_id'] = $wordInfo['word_id'];
		}
		else if (!empty($wordInfo['word_name'])) {
			$params['word_name'] = $wordInfo['word_name'];
		}
		else {
			return array();
		}

		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute-zx*/word_id,word_name");
		$wordId = $wordInfo[0]['word_id'];
		if (empty($wordId)) {
			$wordId = -1;	
		}

		$groupHelper = new Groups();
		$groups = $groupHelper->getPopularGroupForAttr($wordId, $userId, $magFavor);

		return $groups;
	}


	/**
	 * getMatchByAttr
	 *
	 * 通过属性词id获取推荐搭配
	 *
	 * @param int $wordId
	 * @return array
	 *
	 * TODO:由于or功能没有完善，取得时候需要word1和word2都取一边
	 * 
	 * 推荐搭配type_id＝6
	 */
	public function getMatchByAttr($wordInfo) {
		$params = array();
		$params['isuse'] = 1;
		if (!empty($wordInfo['word_id'])) {
			$params['word_id'] = $wordInfo['word_id'];
		}
		else if (!empty($wordInfo['word_name'])) {
			$params['word_name'] = $wordInfo['word_name'];
		}
		else {
			return array();
		}
		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute_match-zx*/word_id,word_name,label_id");
		$wordId = $wordInfo[0]['word_id'];
		$wordInfos = AttrWords::getRelativeWordsInfosByAttr($wordId, 6);

		if (empty($wordInfos) && !empty($wordInfo)) {
			$recommendHelper = new Recommend();		
			$recommendAids = $recommendHelper->getReAttrByAidIfNoMatch($wordInfo[0]['word_id'], 10);
			$params = array();
			$params['word_id'] = $recommendAids;
			$wordInfos = AttrWords::getWordInfo($params, "/*Attribute_match-zx*/word_id,word_name");
		}

		if (!empty($wordInfos)) {
			shuffle($wordInfos);	
		}

		//多取两个attr
		if (!empty($wordInfos) && is_array($wordInfos)) {
			$wordInfosTmp = array_slice($wordInfos, 0, 10);
		}
		else {
			$wordInfosTmp = array();
		}

		//change data keys   上面的wordInfos 清空，下面继续用
		$wordInfos = array();
		if (empty($wordInfosTmp)) {
			return array();	
		}
		foreach ($wordInfosTmp as $wordTmp) {
			if (AttrWords::IsBrandWordsByLabel($wordTmp['label_id'])) {
				$wordTmp['isBrand'] = TRUE;	
			}
			else {
				$wordTmp['isBrand'] = FALSE;
			}
			//改版新加,unset label_id
			unset($wordTmp['label_id']);
			$wordInfos[$wordTmp['word_id']] = $wordTmp;	
		}

		$matchInfo = array_slice($wordInfos, 0, 6);

		return $matchInfo;
	}

	/**
	 * getBastStyleByAttr
	 *
	 * 获取最佳款式 通过 属性词 
	 *
	 * @param int wordId
	 * @param int num
	 * @return array
	 * @todo attrWeight 无or  要word1,word2取两遍
	 */
	public function getBestStyleByAttr($wordId, $num = 4) {
		$wordInfos = AttrWords::getRelativeWordsInfosByAttr($wordId, 5);
		if (empty($wordInfos)) {
			return array();
		}
		$wordInfos = array_slice($wordInfos, 0, $num);
		foreach ($wordInfos as &$wordInfo) {
			if (AttrWords::IsBrandWordsByLabel($wordInfo['label_id'])) {
				$wordInfo['isBrand'] = TRUE;	
			}	
			else {
				$wordInfo['isBrand'] = FALSE;
			} 
			unset($wordInfo['label_id']);
		}
		return $wordInfos;
	}

	/**
	 * getRecommendBrand
	 * 
	 * 通过一个品牌词 获取 推荐品牌 
	 * 
	 * @param int wordId
	 * @return array
	 * @todo 这类的函数需要放在这里么
	 *
	 */

	public function getRecommendBrand($wordId) {
		$wordInfosTmp = AttrWords::getRelativeWordsInfosByAttr($wordId, 7);
		$wordInfos = array();
		if (empty($wordInfosTmp)) {
			return array();
		}
		foreach ($wordInfosTmp as $wordInfo) {
			if (AttrWords::IsBrandWordsByLabel($wordInfo['label_id'])) {
				$wordInfos[] = $wordInfo;
			}	
		}
		return $wordInfos;
	}

	/*
	 * abtest1 for market tid置顶
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 */

	private function abtest1 () {
		if ($offset != 0 || !empty($filter['goods_price']) ) {
			return array();	
		}			
		$cookieKey = $this->wordId . "ntid";

		if (!empty($this->testData['ntid'])) {
			/*if ($this->isAbtest(2, 1) || (isset($this->testData['see']) && $this->testData['see'] == 'ntid')) {
				$ntids = explode(",", $this->testData['ntid']);	
				if (!empty($ntids)) {
					$cache = Memcache::instance();
					$cache->set($cookieKey, $ntids, 12 * 3600);
					setcookie($cookieKey, 1, NULL, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
				}
				//$group_id = $_GET['g']
			}*/
		}
		elseif (isset($_COOKIE[$cookieKey]) && $_COOKIE[$cookieKey] == 1) {
			$cache = Memcache::instance();
			$ntids = $cache->get($cookieKey);
		}
		if (!empty($ntids)) {
			$this->testTids['ntids1'] = $ntids; 
		}
		return TRUE;
	}

	private function abtest2() {
		if (!empty($filter['goods_price'])) {
			return array();
		}
		$cookieKey = $this->swid . "group";
		if ($this->testData['group'] && !empty($this->testData['group'])) {
			if (TRUE || (isset($this->testData['see']) && $this->testData['see'] == 'group')) {
				$groupId = $this->testData['group'];
				if (!empty($groupId)) {
					setcookie($cookieKey, $groupId, time() + 60 * 30, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
					$cache = Memcache::instance();
					$ntids = $cache->get($cookieKey);
				}
			}
		}
		elseif (isset($_COOKIE[$cookieKey])) {
			$groupId = intval($_COOKIE[$cookieKey]);
			$cache = Memcache::instance();
			$ntids = $cache->get($cookieKey);
		}
		if (!empty($ntids) && isset($this->testData['group_frame']) ) {
			$this->testTids['ntids2'] = $ntids;    
		}
		return TRUE;
	}




	public function getCpcNine($wordNames) { 
		$wordInfo['word_name'] = $wordNames;
		$params = array();
		$params['isuse'] = 1;
		if (!empty($wordInfo['word_name'])) {
			$params['word_name'] = $wordInfo['word_name'];
		}
		else {
			return array();
		}
		$wordInfos = AttrWords::getWordInfo($params, "/*Attribute_match-zx*/word_id,word_name,label_id");

        $wordInfoTmp = array();
		foreach ($wordInfos as $winfo) {
			$cpcTwitters = $this->getCpcTwitterByName($winfo['word_name'], 0, 1, 'weight');
			if (!empty($cpcTwitters)) {
				$matchTwitters[$winfo['word_id']] = $cpcTwitters;
			}
            $wordInfoTmp[$winfo['word_id']] = $winfo;
		}
        $wordInfos = $wordInfoTmp;

		$twitterIds = array();
		$tidMapAttr = array();
		if (empty($matchTwitters)) {
			return array();
		}
		foreach ($matchTwitters as $attr => $attrTwitters) {
			$twitterIds = array_merge($twitterIds, $attrTwitters);
			foreach ($attrTwitters as $tid) {
				$tidMapAttr[$tid] = $attr;
			}
		}
		if (!empty($twitterIds)) {
			$twitterHelper = new Twitter();
			$pictures = $twitterHelper->getPicturesByTids($twitterIds, "b");
		}
		$matchInfo = array();
		if (empty($pictures)) {
			return array();
		}
   
        $attrNine = array();
        foreach($matchTwitters as $aid => $tids) {
            $nineTmp = array();
			switch($aid) {
			case 33895:
				$nineTmp['number'] = 391150;
				break;
			case 35483:
				$nineTmp['number'] = 212699;
				break;
			case 33957:
				$nineTmp['number'] = 350813;
				break;
			case 34295:
				$nineTmp['number'] = 835602;
				break;
			case 37815:
				$nineTmp['number'] = 1226026;
				break;
			}
            $nineTmp['name'] = $wordInfos[$aid]['word_name'];
            $nineTmp['word_id'] = $wordInfos[$aid]['word_id'];
            $nineTmp['pics'] = array();
            foreach ($tids as $tid) {
                if(count($nineTmp['pics']) >= 9) {
                    break;
                }
                if(!empty($pictures[$tid])) {
                    $nineTmp['pics'][] = $pictures[$tid]['n_pic_file'];
                    unset($pictures[$tid]);
                }
            }
            if (count($nineTmp['pics']) < 4) {
                continue;
            }
            $attrNine[] = $nineTmp;
        }
       
		return $attrNine;

	}





}


