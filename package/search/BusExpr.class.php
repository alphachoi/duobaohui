<?php
namespace Snake\Package\Search;

/**
 * rankModel中商业价值的定义
 * @package search 
 * @author weiwang
 * @since 2012.08.13
 */
class BusExpr extends DecorateExpr{

	/**
	 * 是否有淘客 0/1
	 *
	 * @var int 
	 * @access public
	 */
	public $commission = 0;

	/**
	 * 销售量
	 *
	 * @var int 
	 * @access public
	 */
	public $saleVolume = 0;

	/**
	 * 商家等级
	 *
	 * @var int 
	 * @access public
	 */
	public $level = 0;


	/**
	 * 获取淘客对应的rank值
	 *
	 * @return float
	 * @access public
	 */	
	public function getCommissionValue() {
		$commissionValue = 1;
		if ($this->commission != 1) {
			$commissionValue = 0.35;
		}
		return $commissionValue;
	}

	/**
	 * 获取销售量对应的rank
	 *
	 * @return float
	 * @access public 
	 */	
	public function getSaleVolumeValue() {
		$saleVolumeValue = 1;
		if ($this->saleVolume <= 144) {
			$saleVolumeValue = 0.1 + pow($this->saleVolume, 1/2) / 12 * 0.9;	
		}
		return $saleVolumeValue;
	}

	/**
	 * 获取店家等级对应的rank
	 *
	 * @return float
	 * @access public 
	 */	
	public function getLevelValue() {
		$levelValue = 1;
		if ($this->level <= 8) {
			$levelValue = 0.5;
		}
		return $levelValue;
	}

	
	/**
	 * 获取相应公式的值
	 *
	 * @return float
	 * @access public
	 */	
	public function getValue() {
		//$cutTime = strtotime('2012-07-26 10:00:00');
		$commissionValue = $this->getCommissionValue();
		$saleVolumeValue = $this->getSaleVolumeValue();
		$levelValue = $this->getLevelValue();
		return 0.3 * pow($commissionValue * $saleVolumeValue * $levelValue, 1/3);
	}

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		$busModulus = $this->busModulus;
		$this->expr = "$busModulus * pow( IF(commission == 1, 1, 0.35) * IF( sale_volume > 144, 1, 0.1 + pow( sale_volume, 1/2) / 12 * 0.9) * IF( level > 8, 1, 0.5), 1/3)";

		parent::compose();
		return $this->expr;
	}

}
