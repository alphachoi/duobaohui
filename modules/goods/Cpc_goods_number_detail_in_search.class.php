<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\CpcGoods;
Use Snake\Package\Goods\Registry;
Use Snake\Package\Goods\PosterRequest;

class Cpc_goods_number_detail_in_search extends \Snake\Libs\Controller{
	private $posterRequest = NULL;
	private $response = array('error');
	
	public function run() {
		$this->initialize();
		$this->setRequest($this->posterRequest);
		if (!$this->checkRequest($this->posterRequest)) {
			return $this->response;
		}

		$this->getResultFromSearch();
	}

	private function getResultFromSearch() {
		$cpcGoodsHelper = new CpcGoods();
		$cpcGoodsHelper->setVerifyFilter(array(1,2,5));
		$result = $cpcGoodsHelper->search();	
		$this->view = $result;
		return TRUE;
	}

	private function checkRequest($request) {
		$request->checkRequest();
		if ($request->error()) {
			$error = $request->getErrorData();
			self::setError(400, $error['errorCode'], $error['errorMessage']);
			return FALSE;
		}
		return TRUE;
	}

	private function initialize() {
		$this->setRegistry();
		return TRUE;
	}

	private function setRegistry() {
		$registry = Registry::instance();
		$registry->setRequest(new PosterRequest());
		$this->posterRequest = $registry->getRequest();
		return TRUE;	
	}

	private function setRequest($request) {
		$request->setWordName($this->request->REQUEST['word_name']);
	}

}
