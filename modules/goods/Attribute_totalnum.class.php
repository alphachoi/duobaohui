<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\Attribute;
Use Snake\Package\Goods\AttrCtrAbtest;
Use Snake\Package\Goods\AttrTwitterCtr1;
Use \Snake\Package\Goods\Popular;
Use Snake\Package\Goods\AttrCtrJudge;
Use \Snake\Package\Goods\Newest;
Use \Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache;

class Attribute_totalnum extends \Snake\Libs\Controller{

	private $wordId  = 0;
	private $wordName = '';
	private $orderby = 'weight';
	private $page	 = 0;
	private $frame	 = 0;
	private $offset = 0;
	private $price	 = NULL;
	private $filterWord = NULL;
	private $useCacheForPosters = TRUE;
	private $attrCTRTest = array(34619,37691,36055,34538,35205);

	const maxFrame = FRAME_SIZE_MAX; /////
	const pageSize = WIDTH_PAGE_SIZE;/////const
	const isShowClose = 1;
	const isShowLike = 1;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$this->offset = $this->frame + $this->page * self::maxFrame; 
		//cache
		$cacheHelper = Memcache::instance();
		$cacheMd5 = md5("{$this->price}_{$this->offset}_" . self::pageSize . "_{$this->wordId}_{$this->wordName}_{$this->orderby}_{$this->filterWord}_" . AttrCtrJudge::isAttrCtr());
		$cacheKeyForPosters = "CacheKey:Attribute_totalnum:{$cacheMd5}";
		$responseTotalNumData = $cacheHelper->get($cacheKeyForPosters);

		//非登陆用户 && cache有返回的 用cache
		if ($this->useCacheForPosters && !empty($responseTotalNumData)) {
			$this->view = $responseTotalNumData;
			return TRUE;
		}
		else {
			$totalNum = $this->getTotalNum();
			$responseTotalNumData = array('totalNum' => $totalNum);
			if (!empty($responseTotalNumData['totalNum']) ) {
				$cacheHelper->set($cacheKeyForPosters, $responseTotalNumData, 600);
			}

			$this->view = $responseTotalNumData;
			return TRUE;
		}
	} 

	private function getTotalNum() {
		if ($this->wordName == 'popular' || $this->wordName == 'hot') {
			$popularObj = new Popular();
			$popularObj->setData($this->wordName, 0, 0);
			$popularObj->setTotalNum();
			$totalNum = $popularObj->getTotalNum();
		}
		else if ($this->wordName == 'new'){
			$newest = new Newest(0, 0);
			$newest->setTotalNum();
			$totalNum = $newest->getTotalNum();
		}
		else {
			if (empty($this->inTest) && AttrCtrJudge::isAttrCtr() && $this->orderby == 'weight' && $this->price == 'all'  && empty($this->filterWord) ) {
				$totalNum = $this->getTotalNumFromCtr1();
			}   
			else {
				$totalNum = $this->getTotalNumFromAttr();
			}   
		}
		return $totalNum;
	}

	private function getTotalNumFromCtr1() {
		$attrTwitterCtr1Obj = new AttrTwitterCtr1();
		$attrTwitterCtr1Obj->setOffset($this->offset);
		$attrTwitterCtr1Obj->setPageSize(self::pageSize);
		$attrTwitterCtr1Obj->setWordId($this->wordId, $this->wordName);
		$attrTwitterCtr1Obj->setOrderBy($this->orderBy);
		$tidsAndTotalNum = $attrTwitterCtr1Obj->getTotalNum();
		return $tidsAndTotalNum;

	}

	private function getTotalNumFromAttr() {
			$attributeObj = new Attribute();
			$attributeObj->setData($this->wordId, $this->wordName,$this->offset, $this->orderby, $this->price, $this->filterWord, self::pageSize, 0);
			$attributeObj->dataProcess();
			$totalNum = $attributeObj->getTotalNum();
			return $totalNum;
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
		if ($frame < 0 || $frame >= FRAME_SIZE_MAX) {
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
		$orderby = 'weight';
		if (isset($this->request->REQUEST['section'])) {
			$orderby = $this->request->REQUEST['section'];
		}
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

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}
}
