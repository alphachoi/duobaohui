<?php

namespace Snake\Modules\Goods;

Use \Snake\Package\Goods\Catalog;
Use \Snake\Package\Goods\Tag;
Use \Snake\Package\Goods\Registry;
Use \Snake\Package\Goods\PosterCatalogRequest;
Use \Snake\Package\Goods\AdjustTwitterPosition;
Use \Snake\Package\Goods\FirstFrameRule;
Use \Snake\Package\Manufactory\Poster;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Ip\IpTest;
Use \Snake\Package\Goods\TestFreeshipping;
Use \Snake\Package\Goods\TestSpecialoffer;

/**
 * 
 * 类目海报强的数据接口
 * 
 * 前端调用获取类目海报墙数据的接口
 *
 * @author Wei Wang 
 * @author Xuan Zheng
 * @package 宝库
 * @request_url http://snake.meilishuo.com/goods/catalog_poster?cata_id=2000000000000&section=hot
 * @request_method GET
 * @request_param cata_id :类目id							
 * @request_param word :属性词 default 0                    
 * @request_param frame :贞数 default 0                     	
 * @request_param page :页数 default 0                      
 * @request_param section :排序 (new or hot) default 'hot'    
 * @request_param price :价格filter default 'all'           
 */
class Catalog_poster extends \Snake\Libs\Controller {
	
	/**
	 * 海报每页的最大贞数
	 * @const FRAME_SIZE_MAX  ( 6 在dolphin.config.php中)
	 */
	const maxFrame = FRAME_SIZE_MAX; 

	
	/**
	 * 海报每页的最大海报数
	 * @const WIDTH_PAGE_SIZE ( 120 在dolphin.config.php中)
	 */
	const pageSize = WIDTH_PAGE_SIZE;

	/**
	 * 是否显示删除
	 * @const 0 
	 */
	const isShowClose = 0;


	/**
	 * 是否显示喜欢
	 * @const 0 
	 */
	const isShowLike = 1;

	/**
	 * 海报请求的RequestObject
	 * @var object
	 */
	private $posterRequest = NULL;

	/**
	 * 海报默认返回数据格式<br/>
	 * @var array
	 */
	private $responsePosterData = array('tInfo' => array(), 'totalNum' => 0);

	/**
	 * cache句柄
	 * @var object
	 */
	private $cacheHelper = NULL;

	/**
	 * cache的开关
	 * @var boolean
	 */
	private $cacheSwitch = TRUE;

	/**
	 * AB
	 */
	private $ab = NULL;


	/**
	 * 初始化cache句柄和requestObject
	 * @access private
	 * @param NULL
	 * @return TRUE  (类的cacheHelper和posterRequest会被set)
	 */
	private function initialize() {
		$this->setRegistry();
		$this->cacheHelper = Memcache::instance();
		return TRUE;
	}

	/**
	 * 初始化requestObject
	 * @access private
	 * @param NULL
	 * @return TRUE  (类的posterRequest会被set)
	 */
	private function setRegistry() {
		$registry = Registry::instance();
		$registry->setRequest(new PosterCatalogRequest());
		$this->posterRequest = $registry->getRequest();
		return TRUE;
	}


	/**
	 * 将request set进posterRequest 
	 * @access private
	 * @param object (posterRequest)
	 * @return TRUE  
	 */
	private function setRequest($request) {
		$request->setPage($this->request->REQUEST['page']);
		$request->setFrame($this->request->REQUEST['frame']);
		$request->setWordId($this->request->REQUEST['word']);
		$request->setCataId($this->request->REQUEST['cata_id']);
		$request->setOrderBy($this->request->REQUEST['section']);
		$request->setPrice($this->request->REQUEST['price']);
		$request->setShowPrice($this->request->REQUEST['showprice']);
		$request->setMaxFrame(self::maxFrame);
		$request->setPageSize(self::pageSize);
		$request->setUserId($this->userSession['user_id']);
		return TRUE;
	}
		

	/**
	 * 监测函数,监测请求的合法性
	 * @access private
	 * @param object (posterRequest)
	 * @return boolean
	 */
	private function checkRequest($request) {
		$request->checkRequest();
		if ($request->error()) {
			$error = $request->getErrorData();
			self::setError(400, $error['errorCode'], $error['errorMessage']);
			return FALSE;
		}
		return TRUE;
	}

	public function abJudge($request) {
		$wordAd = $request->getWordId();
		$priceAd = $request->getPrice();
		$cid = $request->getCataId();
		$orderby = $request->getOrderBy();
		if (2000000000000 !== $cid || !empty($wordAd) || !empty($priceAd) || 'weight' !== $orderby) {
			return FALSE;
		}

		$test = isset($this->request->REQUEST['freeshipping']) ? (int)$this->request->REQUEST['freeshipping'] : FALSE; 
		$test2 = isset($this->request->REQUEST['specialoffer']) ? (int)$this->request->REQUEST['specialoffer'] : FALSE; 

		$freeshippingA = new TestFreeshipping(10, array(3));
		$freeshippingB = new TestFreeshipping(10, array(4));
		if (1 == $test || (FALSE === $test && $freeshippingA->isAbtest())) {
			$fs = TRUE;
		}
		else if (2 == $test || (FALSE === $test && $freeshippingB->isAbtest())) {
			$fs = FALSE;
		}
		if  (isset($fs) && FALSE === $test2) {
			$request->setAb($fs);
			$request->setTestName('freeshipping');
			return TRUE;
		}

		$specialofferA = new TestSpecialoffer(10, array(5));
		$specialofferB = new TestSpecialoffer(10, array(6));
		if (1 == $test2 || (FALSE === $test2 && $specialofferA->isAbtest())) {
			$so = TRUE;
		}
		else if (2 == $test2 ||(FALSE === $test2 && $specialofferB->isAbtest())) {
			$so = FALSE;
		}
		if  (isset($so)) {
			$request->setAb($so);
			$request->setTestName('specialoffer');
		}

		return TRUE;
	}

	/**
	 * 接口(一系列艰苦的心路历程)
	 * @access public
	 * @param NULL
	 * @return boolean
	 */
	public function run() {
		$this->initialize();
		$this->setRequest($this->posterRequest);


		if (!$this->checkRequest($this->posterRequest)) {
			$this->view = $this->responsePosterData;
			return FALSE;	
		}
		$this->abJudge($this->posterRequest);

		$this->dataInCache();
		$this->getData();	
		$this->view = $this->responsePosterData;
		return TRUE;
	}

	/**
	 * 判断是否用cache的方法,如果用cache则set responsePosterData 
	 * @access private 
	 * @param NULL
	 * @return boolean
	 */
	private function dataInCache() {
		$useCache = FALSE;
		$cacheKey = $this->getCacheKey();
		$responsePosterDataFromCache = $this->cacheHelper->get($cacheKey);
		$readCache = !empty($responsePosterDataFromCache) && $this->cacheSwitch;
		if ($readCache) {
			$useCache = TRUE;
			$this->responsePosterData = $responsePosterDataFromCache;
		}
		return $useCache;
	}

		
	private function adjustTids($tids = array()) {
		$wordAd = $this->posterRequest->getWordId();
		$priceAd = $this->posterRequest->getPrice();
		$orderby = $this->posterRequest->getOrderBy();

		if (empty($wordAd) && empty($priceAd) && 'id' !== $orderby) {
				

//			$adjustHelper = new AdjustTwitterPosition();
//			$tids = $adjustHelper->adjustTidsInCata( $tids, $this->posterRequest);
			$testName = $this->posterRequest->getTestName();
			$frame = $this->posterRequest->getFrame();
			$page = $this->posterRequest->getPage();
			$testTids = array();
			if('freeshipping' === $testName) {
				$testTidsHelper = new TestFreeshipping(10, array(3));	
				$testTids = $testTidsHelper->getTestTids($testName, $frame, $page);
			}
			else if('specialoffer' === $testName) {
				$testTidsHelper = new TestSpecialoffer(10, array(5));	
				$testTids = $testTidsHelper->getTestTids($testName, $frame, $page);
			}
			if (empty($testTids)) {
				return $tids;
			}
			$num = count($tids);
			$tmp = array();
			$tidsDiff = array_diff($tids, $testTids);
			$i = 0;
			if ( !empty($tidsDiff) ) {
				while ($num >= 0) {
					array_push($tmp, array_pop($tidsDiff));	
					if (!empty($testTids) && $i == 0) {
						array_push($tmp, array_pop($testTids));
					}
					$i++;
					if (3 < $i) {
						$i = 0;
					}
					$num--;
				}
				$tids = array_reverse($tmp);
			}
			else {
				$tids = $testTids;	
			}
		}
		return $tids;
	}



	/**
	 * 获取海报数据, 并set responsePosterData
	 * @access private 
	 * @param NULL
	 * @return boolean
	 */
	private function getData() {
		$uid = $this->posterRequest->getUserId();
//		$adjusted = AdjustTwitterPosition::adjustTidSetCacheJudge($this->posterRequest->getOffset(), $this->posterRequest->getPageSize(), $this->posterRequest->getCataId());
//		$useCache = empty($uid) && !empty($this->responsePosterData['tInfo']) && !$adjusted;
		$useCache = empty($uid) && !empty($this->responsePosterData['tInfo']);
		if ($useCache) {
			return FALSE;
		}
		$catalogObj = new Catalog();
		$catalogObj->search();
		$tids = $catalogObj->getTids();

		$tids = $this->adjustTids($tids);

		$totalNum = $catalogObj->getTotalNum();
		$poster = $this->getPosters($tids, $this->posterRequest->getUserId());
		
//		//硬规则
//		$rule = new FirstFrameRule($poster, $this->posterRequest->getOffset()); 
//		$poster = $rule->firstFrameAdjust();

		//tag tag
		$frame = $this->posterRequest->getFrame();
		$page = $this->posterRequest->getPage();
		$poster = Tag::addTagWzz($poster, $frame, $page);

//		if (empty($wordAd) && empty($priceAd)) {
//			$plan = $this->posterRequest->getTopPlan();
//			$poster = $this->topPlanUrl($plan, $poster);
//		}

		$poster = $this->posterAb($poster);

		$this->responsePosterData['tInfo'] = $poster;
		$this->responsePosterData['totalNum'] = $totalNum;
		$this->setCache();
		return TRUE;
	}

	private function posterAb($poster) {
		$testName = $this->posterRequest->getTestName();
		$ab = $this->posterRequest->getAb();
		$frame = $this->posterRequest->getFrame();
		$page = $this->posterRequest->getPage();
		$testTids = array();
		if('freeshipping' === $testName) {
			$testTidsHelper = new TestFreeshipping(10, array(3));	
			$testTids = $testTidsHelper->getTestTids($testName, $frame, $page);
		}
		else if('specialoffer' === $testName) {
			$testTidsHelper = new TestSpecialoffer(10, array(5));	
			$testTids = $testTidsHelper->getTestTids($testName, $frame, $page);
		}
		else {
			return $poster;
		}
		$pTmp = array();

        foreach ($poster as $p) {
            $pTmp[$p['twitter_id']] = $p;
        }
        foreach ($testTids as $t) {
            if (!isset($pTmp[$t]) || !$ab) {
                continue;
            }
           if (!empty($pTmp[$t]['url'])) {
                $pTmp[$t]['url'] = "{$pTmp[$t]['url']}&ump={$testName}";
            }
            else {
                if (strpos($pTmp[$t]['url'], "?" !== FALSE)) {
                    $pTmp[$t]['url'] = $pTmp[$t]['url'] . "&ump={$testName}";
                }
                else {
                    $pTmp[$t]['url'] = "/share/{$pTmp[$t]['twitter_id']}?ump={$testName}";
                }
            }
            $pTmp[$t]['ump'] = $testName;
        }

		$poster = array_values($pTmp);
		return $poster;
	}


	private function topPlanUrl($plan, $poster) {
		if (empty($plan)) {
			return $poster;
		}
		foreach ($poster as $k=>$p) {
			if (!empty($p['url'])) {
				$poster[$k]['url'] = "{$p['url']}&ptop={$plan}";
			}	
			else {
				if (strpos($p['url'], "?" !== FALSE)) {
					$poster[$k]['url'] = $p['url'] . "&ptop={$plan}";
				}
				else {
					$poster[$k]['url'] = "/share/{$p['twitter_id']}?ptop={$plan}";
				}
			}
		}	
		return $poster;
	}

	/**
	 * 将responsePosterData set 进cache
	 * @access private 
	 * @param NULL
	 * @return boolean
	 */
	private function setCache() {
		$setCacheOk = FALSE;
		$userId = $this->posterRequest->getUserId();
//		$adjusted = AdjustTwitterPosition::adjustTidSetCacheJudge($this->posterRequest->getOffset(), $this->posterRequest->getPageSize(), $this->posterRequest->getCataId());
//		$setCache = !empty($this->responsePosterData['tInfo']) && empty($userId) && $this->cacheSwitch && !$adjusted;
		$setCache = !empty($this->responsePosterData['tInfo']) && empty($userId) && $this->cacheSwitch;
		if ($setCache) {
			$cacheKey = $this->getCacheKey();
			$setCacheOk = $this->cacheHelper->set($cacheKey, $this->responsePosterData, 600);
		}
		//var_dump($cacheKey);
		return $setCacheOk;
	}

	/**
	 * 取得类目页面cacheKey 
	 * @access private 
	 * @param NULL
	 * @return string cache key
	 */
	private function getCacheKey() {
		$cacheKeySuffixArray = (array)$this->posterRequest;
		foreach ($cacheKeySuffixArray as $key => $content) {
			if (is_array($content)) {
				$content = implode(",", $content);	
			}
			$cacheKeySuffix .= "{$key}:{$content}_";
		}
		$cacheKeySuffix = md5($cacheKeySuffix);
		$cacheKey = "CACHE_CATALOG_POSTER:" . $cacheKeySuffix;
		return $cacheKey;
	}

	/**
	 * 通过twitter ids 获取poster的接口
	 * @access private 
	 * @param array twitter ids 
	 * @param int user ids 
	 * @return array posters
	 */
	private function getPosters($tids = array(), $userId = 0) { 


		$posterObj = new Poster();
		$posterObj->isShowLike(self::isShowLike);
		$posterObj->isShowClose(self::isShowClose);
		if (isset($this->request->REQUEST['showprice'])) {
			$posterObj->isShowPrice(1);
			$posterObj->isShowTime(1);
		}
		$posterObj->setVariables($tids, $userId);
		$poster	= $posterObj->getPoster();


		$cid = $this->request->REQUEST['cata_id'];
		$name = "poster_cata_userId";
		$log = new \Snake\Libs\Base\SnakeLog($name, 'normal');
		$tidNum = count($tids);
		$num = count($poster);
		$log->w_log("{$tidNum}\t{$num}\t{$cid}\t{$userId}");


		if (empty($poster)) {
			$poster = array();
		}
		return $poster;
	}
}
