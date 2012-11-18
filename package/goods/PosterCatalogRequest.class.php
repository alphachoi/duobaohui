<?php
namespace Snake\Package\Goods;

class PosterCatalogRequest extends PosterRequest {

	private $cataId = 2000000000000;
	private $showprice = 0;
	private $topPlan = '';
	private $ab = '';
	private $testName = '';

	public function setShowPrice($showprice) {
		$this->showprice = $showprice;	
	}

	public function setCataId($cataId = 2000000000000) {
		$this->cataId = (int)$cataId;
		return TRUE;
	}

	public function getCataId() {
		return $this->cataId;
	}

	public function checkCataId() {
		$isCatalogId = Catalog::isCatalogTab($cataId);
		if (!$isCatalogId) {
			parent::setError(400, 'cataId is not catalog');
		}
		return TRUE;
	}

	public function setTopPlan($plan = '') {
		$this->topPlan = trim($plan);	
		return TRUE;	
	}

	public function getTopPlan() {
		return $this->topPlan;
	}

	public function setAb($ab = FALSE) {
		$this->ab = $ab;
		return TRUE;
	}
	public function getAb() {
		return $this->ab;
	}

	public function setTestName($name = '') {
		$this->testName = $name;
		return TRUE;
	}
	public function getTestName() {
		return $this->testName;
	}
} 
