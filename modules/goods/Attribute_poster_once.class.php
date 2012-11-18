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

class Attribute_poster_once extends \Snake\Libs\Controller{

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
	private $attrCTRTest = array(34295,34404,34476,37691,36251,36188,34538,34572,35131,35080,37343,34189);
	
	const maxFrame = 1; 
	const pageSize = 11999;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$this->offset = $this->frame + $this->page * self::maxFrame; 

		$big = isset($this->request->REQUEST['big']) ? $this->request->REQUEST['big'] : 0;
		$showprice = isset($this->request->REQUEST['showprice']) ? $this->request->REQUEST['showprice'] : 0;

		$tidsAndNumData = $this->getTidsAndTotalNum();
		$responsePosterData = array();
		$responsePosterData['tids'] = $tidsAndNumData['tids'];
		$responsePosterData['totalNum'] = $tidsAndNumData['totalNum'];
		$this->view = $responsePosterData;
		return TRUE;
	} 

	private function getTidsAndTotalNum() {
		$tidsAndTotalNum = $this->getTidsAndTotalFromAttr();
		$tids = $tidsAndTotalNum['tids'];
		$totalNum = $tidsAndTotalNum['totalNum'];
		return array('tids' => $tids, 'totalNum' => $totalNum);
	}

	private function getTidsAndTotalFromAttr() {
		$attributeObj = new Attribute();
		$attributeObj->setData($this->wordId, $this->wordName, $this->offset, $this->orderby, $this->price, $this->filterWord, self::pageSize, $this->twitterId);

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
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}
}
