<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\Attribute;
Use \Snake\Package\Goods\Popular;
Use \Snake\Package\Goods\Tag;
Use \Snake\Package\Goods\Newest;
Use \Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\AttrCtrJudge;
Use Snake\Package\Goods\AttrTwitterCtr1;
Use Snake\Package\Group\GroupTwitters;
Use Snake\Package\Goods\FirstFrameRule;
Use Snake\Package\Goods\AttrClickAbtest;
Use Snake\Package\Goods\TestMoreinfo;
Use Snake\Package\Goods\TestMoresimilar;
Use Snake\Package\Ip\IpTest;

class Attribute_poster extends \Snake\Libs\Controller{

	private $userId = 0;
	private $wordId  = 0;
	private $wordName = '';
	private $orderby = 'weight';
	private $page    = 0;
	private $frame   = 0;
	private $offset = 0;
	private $price   = NULL;
	private $filterWord = NULL;
	private $twitterId = NULL;
	private $testData = array();
	private $inTest = '';
	private $testUseCache = FALSE;
	private $useCacheForPosters = TRUE;
	private $plan = NULL;
	private $ab = NULL;

	const maxFrame = FRAME_SIZE_MAX; 
	const pageSize = WIDTH_PAGE_SIZE;
	const isShowClose = 0;
	const isShowLike = 1;

	public function abJudge() {
		$request = isset($this->request->REQUEST['placeab']) ? (int)$this->request->REQUEST['placeab'] : FALSE; 
		if ($request === FALSE) {
			$iptest = new IpTest(3, array(0));
			$iptest->setPlace("GUANGDONG");
			$iptest->setIp(\Snake\Libs\Base\Utilities::getClientIP());
			$this->ab = $iptest->isAbtest();
		}   
		else {
			if ($request == 0) {
				$this->ab = TRUE;   
			}   
			else {
				$this->ab = FALSE;
			}   
		}   
		return $this->ab;
	}

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->offset = $this->frame + $this->page * self::maxFrame; 

		$big = isset($this->request->REQUEST['big']) ? $this->request->REQUEST['big'] : 0;
		$showprice = isset($this->request->REQUEST['showprice']) ? $this->request->REQUEST['showprice'] : 0;

		//cache
		$cacheHelper = Memcache::instance();
		$key = "{$this->price}_{$this->offset}_" . self::pageSize . "_{$this->wordId}_{$this->wordName}_{$this->orderby}_{$this->filterWord}_{$this->twitterId}_{$this->inTest}_{$this->ab}_{$big}" . AttrCtrJudge::isAttrCtr($this->wordId);
		$cacheMd5 = md5($key);
		$cacheKeyForPosters = "CacheKey:Attribute_poster:{$cacheMd5}";
		$responsePosterData = $cacheHelper->get($cacheKeyForPosters);

		//非登陆用户 && cache有返回的 用cache
		if ($showprice) {
			$this->useCacheForPosters = FALSE;
		}
		$useCache = $this->useCacheForPosters && empty($this->userId) && !empty($responsePosterData) && empty($this->testData) && (empty($this->inTest) || $this->testUseCache) && ($this->wordName != 'new');
		if ($useCache) {
			$this->view = $responsePosterData;
			return TRUE;
		}
		else {
			$tidsAndNumData = $this->getTidsAndTotalNum();
			$tids = $tidsAndNumData['tids'];
			$totalNum = $tidsAndNumData['totalNum'];

			if (empty($tids)) {
				if (empty($responsePosterData)) {
					$responsePosterData = array('tInfo' => array(), 'totalNum' => 0);
				}
				$this->view = $responsePosterData;
				return TRUE;
			}

			$posterObj = new Poster();
			$posterObj->isShowLike(self::isShowLike);
			$posterObj->isShowClose(self::isShowClose);
			if ($showprice) {
				$posterObj->isShowPrice(1);
				$posterObj->isShowTime(1);
			}
			if ($big) {
				$posterObj->setShowPic("t");
			}
			$posterObj->setVariables($tids, $this->userId);
			$poster = $posterObj->getPoster();

			if ('' !== $this->inTest && "hot" === $this->wordName && 0 == $this->page && 0 == $this->frame) {
				$poster = $this->abtestPoster($poster);
			}
			else {
				//硬规则
				$rule = new FirstFrameRule($poster, $this->offset); 
				$poster = $rule->firstFrameAdjust();
			}


			//打tag
			$poster = Tag::addTagWzz($poster, $this->frame, $this->page);
			$responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum);

			if (empty($this->userId) && !empty($responsePosterData['tInfo']) && (empty($this->inTest) || $this->testUseCache) && $this->useCacheForPosters) {
				$cacheHelper->set($cacheKeyForPosters, $responsePosterData, 600);
			}

			$this->view = $responsePosterData;
			return TRUE;
		}
	} 

	private function abtestPoster($poster = array()) {
		if (empty($poster) || FALSE == $this->ab) {
			return $poster;
		}	
		if ($this->inTest === 'moresimilar') {
			$tidsGetter = new 	TestMoresimilar(10, array(9));
			$similarTids = $tidsGetter->getTestTids($this->inTest, $this->frame, $this->page);
			foreach ($poster as $key => $p) {
				if (!in_array($p['twitter_id'],$similarTids)) {
					continue;
				}	
				$img = $tidsGetter->getImgByTid($p['twitter_id']);
				if (empty($img)) {
					continue;
				}
				$poster[$key]['show_pic'] = $img['pic'];
				$poster[$key]['poster_width'] = $img['w'];
				$poster[$key]['poster_height'] = $img['h'];
				if (!empty($p['url'])) {
					$poster[$key]['url'] = "{$p['url']}&ump={$this->inTest}";
				}	
				else {
					if (strpos($p['url'], "?" !== FALSE)) {
						$poster[$key]['url'] = $p['url'] . "&ump={$this->inTest}";
					}
					else {
						$poster[$key]['url'] = "/share/{$p['twitter_id']}?ump={$this->inTest}";
					}
				}
			}	
		}
		return $poster;
	}

	private function getTidsAndTotalNum() {
		if ($this->wordName == 'popular' || $this->wordName == 'hot') {
			$popularObj = new Popular();
			$popularObj->setData($this->wordName, $this->offset, self::pageSize);
			//$popularObj->setPlan($this->plan);
			//$popularObj->setAb($this->ab);
			$popularObj->setTids();
			$popularObj->setTotalNum();
			$tids = $popularObj->getTids();

			$moresimilar = new TestMoresimilar(10, array(8));
			$similarTids = $moresimilar->getTestTids($this->inTest, $this->frame, $this->page);
			if (!empty($tids)) {
				$tidsDiff = array_diff($tids, $similarTids);
				shuffle($similarTids);
				$tids = array_merge($similarTids, $tidsDiff);
			}
			$totalNum = $popularObj->getTotalNum();
		}
		else if ($this->wordName == "new") {
			$newest = new Newest($this->offset, self::pageSize);
			$newest->setTids();
			$newest->setTotalNum();
			$tids = $newest->getTids();
			$totalNum = $newest->getTotalNum();
		}
		else{
			if ( empty($this->inTest) && AttrCtrJudge::isAttrCtr($this->wordId) && $this->orderby == 'weight' && $this->price == 'all' && empty($this->filterWord)) {
				$tidsAndTotalNum = $this->getTidsAndTotalFromCtr1();
			}
			else {
				$tidsAndTotalNum = $this->getTidsAndTotalFromAttr();
			}
			$tids = $tidsAndTotalNum['tids'];
			$totalNum = $tidsAndTotalNum['totalNum'];
		}
		return array('tids' => $tids, 'totalNum' => $totalNum);
	}

	private function getTidsAndTotalFromAttr() {
		$attributeObj = new Attribute();
		$attributeObj->setData($this->wordId, $this->wordName, $this->offset, $this->orderby, $this->price, $this->filterWord, self::pageSize, $this->twitterId);
		$attributeObj->setTestData($this->testData);

		if (!$attributeObj->dataProcess()) {
			if (empty($responsePosterData)) {
				$responsePosterData = array('tInfo' => FALSE, 'totalNum' => FALSE);
			}
			$this->view = $responsePosterData;
			return TRUE;
		}
		$tids = $attributeObj->getTids();
		$totalNum = $attributeObj->getTotalNum();
		return array('tids' => $tids, 'totalNum' => $totalNum); 
	}

	private function getTidsAndTotalFromCtr1() {
		$attrTwitterCtr1Obj = new AttrTwitterCtr1();
		$attrTwitterCtr1Obj->setOffset($this->offset);
		$attrTwitterCtr1Obj->setPageSize(self::pageSize);
		$attrTwitterCtr1Obj->setWordId($this->wordId, $this->wordName);
		$attrTwitterCtr1Obj->setOrderBy($this->orderby);
		$tidsAndTotalNum = $attrTwitterCtr1Obj->getTidsAndTotalNum();
		return $tidsAndTotalNum;
	}




	private function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}   
		if (!$this->setOrderby()) {
			return FALSE;   
		}
		if (!$this->setBrandName()) {
			return FALSE;
		}
		if (!$this->setPrice()) {
			return FALSE;
		}
		if (!$this->setFrame()) {
			return FALSE;
		}
		if (!$this->setFilterWord()) {
			return FALSE;
		}
		if  (!$this->setPage()) {
			return FALSE;
		}
		$this->setTwitterId();
		$this->setUserId();
		$this->setTestData();

		return TRUE;
	}

	private function setTwitterId() {
		$twitterId = intval($this->request->REQUEST['twitter']);
		if (empty($twitterId)) {
			return FALSE;
		}
		$this->twitterId = $twitterId;
		return TRUE;    
	}


	private function setFilterWord() {
		$filterWord = htmlspecialchars_decode(urldecode($this->request->REQUEST['filterword']));
		$this->filterWord = $filterWord;    
		return TRUE;
	}


	private function setWordId() {
		$wordId = intval($this->request->REQUEST['word']);
		if (!empty($wordId) && !is_numeric($wordId)) {
			$this->errorMessage(400, 'word is not number');
			return FALSE;
		}
		if ($this->wordId < 0) {
			$this->errorMessage(400, 'bad word');
			return FALSE;
		}
		$this->wordId = $wordId;
		return TRUE;
	}

	private function setBrandName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			$this->wordName = $wordName;    
		}
		return TRUE;
	}


	private function setFrame() {
		$frame = intval($this->request->REQUEST['frame']);
		if (!isset( $frame) || !is_numeric($frame)) {
			$this->errorMessage(400, 'bad frame');
			return FALSE;
		}
		if ($frame < 0) {
			$this->errorMessage(400, 'out of frame');
			return FALSE;
		}
		$this->frame = $frame;
		return TRUE;
	}


	private function setPage() {
		$page = intval($this->request->REQUEST['page']);
		if (!isset($page) || !is_numeric($page)) {
			$this->errorMessage(400, 'bad page');
			return FALSE;
		}
		if ($page < 0)  {
			$this->errorMessage(400, 'page is negative');
			return FALSE;
		}
		$this->page = $page;
		return TRUE;
	}

	private function setPrice() {
		$price = $this->request->REQUEST['price'];
		$this->price = $price;
		return TRUE;
	}


	private function setOrderby() {
		$orderby = $this->request->REQUEST['section'];
		if (!empty($orderby)) {
			if ($orderby == 'new') {
				$this->orderby = 'id';
			}
			else {
				$this->orderby = 'weight';
			}
		}
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}


	/**
	 * 属性页的test之一,置顶推广属性时传的tids
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @access private
	 */
	private function setTestData() {
		$testData = array();

		$cookieKeyNtids = $this->wordId . "ntid";
		$cookieKeyGroup = $this->wordId . "group";
		if (!empty($this->request->REQUEST['ntid']) || (isset($_COOKIE[$cookieKeyNtids]) && $_COOKIE[$cookieKeyNtids] == 1 )) {
			$this->testData['ntid'] = $this->request->REQUEST['ntid'];
			$this->inTest = 'ntid';
		}
		else if ((!empty($this->request->REQUEST['group']) || isset($_COOKIE[$cookieKeyGroup])) ) {
			$this->testData['group'] = intval($this->request->REQUEST['group']);    
			if (!empty($this->testData['group'])) {
				$groupHelper = new GroupTwitters();
				$ntids = $groupHelper->getGroupTwittersByGroupIdsNoCache(array($this->testData['group']), array('twitter_id', 'group_id'), 0, 40);
				$ntids = \Snake\Libs\Base\Utilities::DataToArray($ntids, "twitter_id");

				$cacheHelper = Memcache::instance();
				$cacheHelper->set($cookieKeyGroup, $ntids, 3600);
			}

			$groupTidNum = count($ntids);
			if ($groupTidNum >= 20) {
				$groupFrame = 2;    
			}
			else if ($groupTidNum < 20 && $groupTidNum > 0) {
				$groupFrame = 1;
			}
			else {
				$groupFrame = 0;    
			}

			$this->frame -= $groupFrame;
			if ($this->frame < 0 && $this->page == 0 ) {
				$this->testData['group_frame'] = $this->frame + $groupFrame;    
				$this->frame = 0;
			}
			$this->inTest = 'group';
			$this->testUseCache = FALSE;
		}


		if (!empty($this->request->REQUEST['see'])) {
			$this->testData['see'] = $this->request->REQUEST['see'];
			$this->inTest = 'see';
		}
		//$this->setAttrPosterPlan();
		//ab判断
		//$this->abJudge();
		$this->moreImgTestJudger();
		return TRUE;
	}

	private function setAttrPosterPlan() {
		$plan = isset($this->request->REQUEST['clickplan']) ? (int)$this->request->REQUEST['clickplan'] : FALSE; 
		if ($plan === FALSE || $plan > 3 || $plan < 0) {
			$planChoose1 = new AttrClickAbtest(4,array(0));
			$planChoose2 = new AttrClickAbtest(4,array(1));
			$planChoose3 = new AttrClickAbtest(4,array(2));
			if($planChoose1->isAbtest()) {
				$this->plan = 1;
			}
			else if($planChoose2->isAbtest()) {
				$this->plan = 2;
			}
			else if($planChoose3->isAbtest()) {
				$this->plan = 3;
			}
		}   
		else {
			$this->plan = $plan;
		}

	}

	private function moreImgTestJudger() {
		if ('hot' !== $this->wordName) {
			return FALSE;
		}
		$test1 = isset($this->request->REQUEST['moreinfo']) ? (int)$this->request->REQUEST['moreinfo'] : FALSE; 
		$test2 = isset($this->request->REQUEST['moresimilar']) ? (int)$this->request->REQUEST['moresimilar'] : FALSE; 

		$moreinfoA = new TestMoreinfo(10, array(6));
		$moreinfoB = new TestMoreinfo(10, array(7));
		if (1 == $test1 || (FALSE === $test1 && $moreinfoA->isAbtest())) {
			$mi = TRUE;
		}
		else if (2 == $test1 || (FALSE === $test1 && $moreinfoB->isAbtest())) {
			$mi = FALSE;
		}
		if  (isset($mi) && FALSE === $test2) {
			$this->ab = $mi;
			$this->inTest = 'moreinfo';
			return TRUE;
		}

		$moresimilarA = new TestMoresimilar(10, array(8));
		$moresimilarB = new TestMoresimilar(10, array(9));
		if (1 == $test2 || (FALSE === $test2 && $moresimilarA->isAbtest())) {
			$ms = TRUE;
		}
		else if (2 == $test2 ||(FALSE === $test2 && $moresimilarB->isAbtest())) {
			$ms = FALSE;
		}
		if  (isset($ms)) {
			$this->ab = $ms;
			$this->inTest = 'moresimilar';
		}
		return TRUE;
	}

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}
}

