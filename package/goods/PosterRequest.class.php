<?php
namespace Snake\Package\Goods;

class PosterRequest extends Request {
	protected $twitterId = 0;
	protected $userId = 0;
	protected $wordId  = 0;
	protected $wordName = '';
	protected $orderby = 'weight';
	protected $page = 0;
	protected $frame = 0;
	protected $offset = 0;
	protected $price = '';
	protected $filterWord = '';
	protected $pageSize = 20;
	protected $maxFrame = 6;
	protected $cpcLanding = FALSE;


	protected $error = FALSE;
	protected $errorCode = 400;
	protected $errorMessage = '';

	
	const isShowClose = 0;
	const isShowLike = 1;


	
	public	function checkRequest() {
		$this->checkPage(); 
		$this->checkFrame();
		$this->checkWordId();
		return TRUE;
	}

	public function error() {
		return $this->error;	
	} 

	public function getErrorData() {
		if ($this->error) {
			return array(
					'errorCode' => $this->errorCode,
					'errorMessage' => $this->errorMessage,
				);	
		}
		return FALSE;
	}

	protected function checkWordId() {
		if ($this->wordId < 0) {
			$this->setError(400, "request error wordId");	
		}		
		return TRUE;
	}

	protected function checkFrame() {
		if ($this->frame < 0) {
			$this->setError(400, "request error frame");	
		}		
		return TRUE;
	}

	protected function checkPage() {
		if ($this->page < 0) {
			$this->setError(400, "request error page");	
		}		
		return TRUE;
	}

	protected function setError($code, $message) {
		$this->error = TRUE;
		$this->errorCode = (int)$code;
		$this->errorMessage = $messsage;
		return TRUE;
	}

	public function setUserId($userId = 0) {
		$this->userId = (int)$userId;
		return TRUE;
	}
	
	public function setPageSize($pageSize = 20) {
		$this->pageSize = (int)$pageSize;
	}

	public function setMaxFrame($maxFrame = 6) {
		$this->maxFrame = (int)$maxFrame;
	}

	protected function setTwitterId($twitterId = 0) {
		$this->twitterId = (int)$twitterId;
		return TRUE;	
	}

	protected function setFilterWord($filterWord = '') {
		$this->filterWord = htmlspecialchars_decode(urldecode($filterWord));
		return TRUE;
	}

	public function setWordId($wordId = 0) {
		$this->wordId = (int)$wordId;
		return TRUE;
	}

	public function setWordName($wordName = '') {
		$this->wordName = htmlspecialchars_decode(urldecode($wordName));
		return TRUE;
	}

	public function setFrame($frame = 0) {
		$this->frame = (int)$frame;
		return TRUE;
	}

	public function setPage($page = 0) {
		$this->page = (int)$page;
		return TRUE;
	}

	public function setPrice($price = 'all') {
		if ($price == 'all') {
			return FALSE;
		}
		$priceRe = explode("~",$price);
		$from = (float)$priceRe[0]; 
		$to = isset($priceRe[1])? (float)$priceRe[1] : NULL; 
		if (!isset($from) || !isset($to)) {
			return FALSE;
		}
		$this->price = array('from' => $from, 'to' => $to);
		return TRUE;
	}

	public function setOrderby($orderby = 'weight') {
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

	public function setCpcLanding($bool = FALSE) {
		$this->cpcLanding = $bool;    
		return TRUE;
	}  

	public function getWordId() {
		return $this->wordId;
	}

	public function getUserId() {
		return $this->userId;
	}

	public function getFrame() {
		return $this->frame;
	}

	public function getPage() {
		return $this->page;
	}

	public function getPageSize() {
		return $this->pageSize;
	}

	public function getWordName() {
		return $this->wordName;
	}

	public function getOrderBy() {
		return $this->orderby;
	}

	public function getPrice() {
		return $this->price;
	}

	public function getOffset() {
		return $this->frame + $this->page * $this->maxFrame; 
	}


}
