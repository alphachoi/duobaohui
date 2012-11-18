<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\Attribute;
Use Snake\Package\Goods\Registry;
//Use Snake\Package\Goods\Request;
Use Snake\Package\Goods\PosterRequest;
Use Snake\Package\Goods\Popular;
Use Snake\Package\Goods\Tag;
Use Snake\Package\Goods\Newest;
Use Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\Abtest;
Use Snake\Package\Goods\CpcTest;
Use Snake\Package\Goods\AttributeTest;
Use Snake\Package\Goods\AttrCtrAbtest;
Use Snake\Package\Goods\AttrTwitterCtr1;
Use Snake\Package\Group\GroupTwitters;

class Attribute_poster extends \Snake\Libs\Controller{

	private $userId = 0;
	private $offset = 0;
	private $testData = array();
	private $inTest = '';
	private $testUseCache = FALSE;
	private $useCacheForPosters = TRUE;
	private $attrCTRTest = array(34619,37691,36055,34538,35205);

	private $posterRequest = NULL;
	private $responsePosterData = array('tInfo' => array(), 'totalNum' => 0);
	
	const pageSize = WIDTH_PAGE_SIZE;
	const isShowClose = 0;
	const isShowLike = 1;

	private function initialize() {
		$registry = Registry::instance();
		$registry->setRequest(new PosterRequest());
		$this->posterRequest = $registry->getRequest();
		return $this->posterRequest;
	}

	private function setPosterRequest($posterRequest) {
		$posterRequest->setWordId($this->request->REQUEST['word']);
		$posterRequest->setWordName($this->request->REQUEST['word_name']);
		$posterRequest->setOrderby($this->request->REQUEST['section']);
		$posterRequest->setPrice($this->request->REQUEST['price']);
		$posterRequest->setFrame($this->request->REQUEST['frame']);
		$posterRequest->setPage($this->request->REQUEST['page']);
		$posterRequest->setPageSize(self::pageSize);
		$posterRequest->setFilterWord($this->request->REQUEST['filterword']);
		$posterRequest->setTwitterId($this->request->REQUEST['twitter']);
		$posterRequest->setTestData($this->request->REQUEST['word']);
		$this->setUserId($this->userSession['user_id']);
		//$this->setTestData();
		return TRUE;
	}
	
	private function checkRequest($posterRequest) {
		$posterRequest->checkRequest();
		if ($posterRequest->error()) {
			$error = $posterRequest->getErrorData();
			self::setError(400, $error['errorCode'], $error['errorMessage']);
			return FALSE;
		}
		return TRUE;
	}

	public function run() {
		$this->initializeRequest();
		$this->setPosterRequest($this->posterRequest);
		if (!$this->checkRequest($this->posterRequest)) {
			return $this->responsePosterData;
		}

		$tidsAndNumData = $this->getTidsAndTotalNum();
		$tids = $tidsAndNumData['tids'];
		$totalNum = $tidsAndNumData['totalNum'];

		$poster = $this->getPoster($tids, $this->userId);
		if (!empty($poster)) {
			$this->responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum);
		}

		$this->view = $ths->responsePosterData;
		return TRUE;
	} 

	private getPoster($tids, $userId = $this->userId) {
		empty($tids) && return FALSE; 

		$big = isset($this->request->REQUEST['big']) ? $this->request->REQUEST['big'] : 0;
		$posterObj = new Poster();
		$posterObj->isShowLike(self::isShowLike);
		$posterObj->isShowClose(self::isShowClose);
		if ($big) {
			$posterObj->setShowPic("t");
		}
		$posterObj->setVariables($tids, $this->userId);
		$poster	= $posterObj->getPoster();
		$poster = Tag::addTagWzz($poster, $this->posterRequest->getFrame(), $this->posterRequest->getPage());
		return $poster;
	}

	private function getTidsAndTotalNum() {
		$fun = "getTidsAndTotalFromAttr";
		if ($this->wordName == 'popular' || $this->wordName == 'hot') {
			$fun = "getTidsAndTotalFromPopular";
		}
		else if ($this->wordName == "new") {
			$fun = "getTidsAndTotalFromNew";
		}
		else if (in_array($this->orderby,array('ctr1', 'ctr1_opt'))) {
			$fun = "getTidsAndTotalFromCtr1";
		}
		$tidsAndTotalNum = $this->$fun();
		$tids = $tidsAndTotalNum['tids'];
		$totalNum = $tidsAndTotalNum['totalNum'];
		return array('tids' => $tids, 'totalNum' => $totalNum);
	}

	private function getTidsAndTotalFromNew() {
		$newest = new Newest($this->offset, self::pageSize);
		$newest->setTids();
		$newest->setTotalNum();
		$tids = $newest->getTids();
		$totalNum = $newest->getTotalNum();
		return array('tids' => $tids, 'totalNum' => $totalNum);	
	}

	private function getTidsAndTotalFromPopular() {
		$popularObj = new Popular();
		$popularObj->setData($this->wordName, $this->offset, self::pageSize);
		$popularObj->setTids();
		$popularObj->setTotalNum();
		$tids = $popularObj->getTids();
		$totalNum = $popularObj->getTotalNum();
		return array('tids' => $tids, 'totalNum' => $totalNum);	
	}

	private function getTidsAndTotalFromAttr() {
		$attributeObj = new Attribute();
		$attributeObj->setData();
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
		$testData = array();

		if (!empty($this->request->REQUEST['bob']) && strpos($this->request->REQUEST['bob'], 'attr') !== FALSE) {
			$this->testData[CpcTest::SYMBOL] = isset($this->request->REQUEST[CpcTest::SYMBOL]) ? $this->request->REQUEST[CpcTest::SYMBOL] : CpcTest::SYMBOL;
			$this->inTest = CpcTest::SYMBOL;
		}

		$cookieKeyNtids = $this->wordId . "ntid";
		$cookieKeyGroup = $this->wordId . "group";
		if (!empty($this->request->REQUEST['ntid']) || (isset($_COOKIE[$cookieKeyNtids]) && $_COOKIE[$cookieKeyNtids] == 1 )) {
			$this->testData['ntid'] = $this->request->REQUEST['ntid'];
			$this->inTest = 'ntid';
		}
		else if (!empty($this->request->REQUEST['group']) || isset($_COOKIE[$cookieKeyGroup]) ) {
			$this->testData['group'] = intval($this->request->REQUEST['group']);	
			$groupHelper = new GroupTwitters();
			$ntids = $groupHelper->getGroupTwittersByGroupIdsNoCache(array($this->testData['group']), array('twitter_id', 'group_id'), 0, 40);
		    $ntids = \Snake\Libs\Base\Utilities::DataToArray($ntids, "twitter_id");

			$cacheHelper = Memcache::instance();
			$cacheHelper->set($cookieKeyGroup, $ntids, 3600);

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
		return TRUE;
	}

	private function setUserId($userId = 0) {
		$this->userId = (int)$userId;
		return TRUE;
	}
}

