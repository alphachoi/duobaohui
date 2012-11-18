<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\Attribute;
Use \Snake\Package\Goods\Popular;
Use \Snake\Package\Goods\Tag;
Use \Snake\Package\Goods\Newest;
Use \Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\Abtest;
Use Snake\Package\Goods\CpcTest;
Use Snake\Package\Goods\CpcLandingJudge;
Use Snake\Package\Goods\AttributeTest;
Use Snake\Package\Goods\AttrCtrAbtest;
Use Snake\Package\Goods\AttrTwitterCtr1;
Use Snake\Package\Group\GroupTwitters;

class Attribute_landing_poster extends \Snake\Libs\Controller{

	private $userId = 0;
	private $wordId  = 0;
	private $wordName = '';
	private $orderby = 'weight';
	private $page	 = 0;
	private $frame	 = 0;
	private $offset = 0;
	private $price	 = NULL;
	private $filterWord = NULL;
	private $twitterId = NULL;
	private $testData = array();
	private $inTest = '';
	private $testUseCache = FALSE;
	private $useCacheForPosters = TRUE;
	private $attrCTRTest = array(34619,37691,36055,34538,35205);
	
	const maxFrame = 8; 
	const pageSize = WIDTH_PAGE_SIZE;
	const isShowClose = 0;
	const isShowLike = 1;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$this->offset = $this->frame + $this->page * self::maxFrame; 


		$big = isset($this->request->REQUEST['big']) ? $this->request->REQUEST['big'] : 0;
		//cache
		$cacheHelper = Memcache::instance();
		$cacheMd5 = md5("{$this->price}_{$this->offset}_" . self::pageSize . "_{$this->wordId}_{$this->wordName}_{$this->orderby}_{$this->filterWord}_{$this->twitterId}_{$this->inTest}_{$big}");
		$cacheKeyForPosters = "CacheKey:Attribute_landing_poster:{$cacheMd5}";
		$responsePosterData = $cacheHelper->get($cacheKeyForPosters);

		//非登陆用户 && cache有返回的 用cache
		$useCache = $this->useCacheForPosters && empty($this->userId) && !empty($responsePosterData) && (empty($this->inTest) || $this->testUseCache);
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
			if ($big) {
				$posterObj->setShowPic("t");
			}
			$posterObj->setVariables($tids, $this->userId);
			$poster	= $posterObj->getPoster();
			$poster = Tag::addTagWzz($poster, $this->frame, $this->page);
			$responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum);

			if (empty($this->userId) && !empty($responsePosterData['tInfo']) && (empty($this->inTest) || $this->testUseCache)) {
				$cacheHelper->set($cacheKeyForPosters, $responsePosterData, 300);
			}

			$this->view = $responsePosterData;
			return TRUE;
		}
	} 

	private function getTidsAndTotalNum() {
		if ($this->wordName == 'popular' || $this->wordName == 'hot') {
			$popularObj = new Popular();
			$popularObj->setData($this->wordName, $this->offset, self::pageSize);
			$popularObj->setTids();
			$popularObj->setTotalNum();
			$tids = $popularObj->getTids();
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
			if (in_array($this->orderby,array('ctr1', 'ctr1_opt'))) {
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
		$attrTwitterCtr1Obj->setWordId($this->wordId);
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
		if	(!$this->setPage()) {
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
		//一个test hack
		$this->orderby = $this->changeOrderByHack($this->orderby);
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	/**
	 * 属性词的一个小红心test
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @param int @orerBy 传入原orderBy
	 * @return int @orderBy 传出hack的orderBy
	 * @access private
	 */
	private function changeOrderByHack($orderBy) {
		$frm = isset($this->request->REQUEST['cof']) && $this->request->REQUEST['cof'] == 'frontier';    
		if ((!empty($frm) || !empty($_COOKIE['MEILISHUO_FRM_FRONTIER'])) && $orderBy == 'weight' ) {
			$sessionId = $_COOKIE['MEILISHUO_GLOBAL_KEY'];
			$remainder = ord(substr($sessionId, 0, 1)) % 2;
            if (substr($sessionId, -15, 6) == date("ymd") && $remainder === 0) {
				$time = $_SERVER['REQUEST_TIME'];
				$deadLine = $time + 3600;
				$content = $deadLine - $time;
				setcookie('MEILISHUO_FRM_FRONTIER', $content, $deadLine, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
				$orderBy = 'like';
			}
			if (substr($sessionId, -15, 6) == date("ymd")) {
				$this->inTest = 'attributeWeightTest';
			}
		} 
		if ( (in_array($this->wordId, $this->attrCTRTest)) && $orderBy == 'weight')  {
			$testLink = $this->request->REQUEST['attrCtr'];    

			$attrCtrAbtset0 = new AttrCtrAbtest(3, array(1));
			$attrCtrAbtset1 = new AttrCtrAbtest(3, array(2));

			$oorder = $orderBy;
			if ($attrCtrAbtset0->isAbtest()) {
				$orderBy = "ctr1";
			}
			else if ($attrCtrAbtset1->isAbtest()) {
				$orderBy = "ctr1_opt";	
			}
			if ($testLink == 2) {
				$orderBy = "ctr1";
			}
			else if ($testLink == 3) {
				$orderBy = "ctr1_opt";	
			}
			else if ($testLink == 1) {
				$orderBy = $oorder;
			}
		}
		return $orderBy;
	}


	/**
	 * 属性页的test之一,置顶推广属性时传的tids
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @access private
	 */
	private function setTestData() {
		CpcLandingJudge::setCpcLanding($this->frame);
		//$this->testData[CpcTest::SYMBOL] = 'attr';	
		//$this->inTest = CpcTest::SYMBOL;
		//$this->testUseCache = TRUE;
		return TRUE;
	}


	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		return TRUE;
	}
}
