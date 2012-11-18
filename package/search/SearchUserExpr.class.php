<?php
namespace Snake\Package\Search;

/**
 * rankModel中用户价值的定义
 * @package search 
 * @author weiwang
 * @since 2012.08.02
 */
class SearchUserExpr extends SearchExpr{

	/**
	 * 审核状态
	 *
	 * @var int 
	 * @access public 
	 */
	public $verifyStat = 0;

	/**
	 * 推的创建时间
	 *
	 * @var int 
	 * @access public
	 */
	public $twitterCreateTime = 0;

	/**
	 * 小红心
	 *
	 * @var int 
	 * @access public
	 */
	public $rankLike = 0;

	function __construct(){
	}

	/**
	 * 获取审核状态对应的数值
	 *
	 * @return float
	 * @access public
	 */	
	public function getVerifyStatValue() {
		switch($this->verifyStat) {
			case 0: $verifyStatValue = 0.2; break;
			case 1: $verifyStatValue = 0.4; break; 
			case 2: $verifyStatValue = 1; break; 
			default: $verifyStatValue = 0.2;
		}
		return $verifyStatValue;
	}

	/**
	 * 获取小红心对应的数值
	 *
	 * @return float
	 * @access public 
	 */	
	public function getRankLikeValue() {
		$rankLikeValue = 1;
		if ($this->rankLike < 1000) {
			$rankLikeValue = 0.1 + pow($this->rankLike, 1/3) / 10 * 0.9;	
		}
		return $rankLikeValue;
	}

	/**
	 * 根据小红心的rank值获取相应的小红心数
	 *
	 * @param float $rankLikeValue rank值
	 * @return float
	 * @access public 
	 */	
	public function getOppositeRankLike($rankLikeValue) {
		if ($rankLikeValue > 1) {
			return 1000;
		}
		return pow(($rankLikeValue - 0.1) / 0.9 * 10, 3);
	}

	/**
	 * 获取时间因素对应的数值
	 *
	 * @return float
	 * @access public
	 */	
	public function getTimeValue() {
		$searchTime = time();
		$timeValue = pow( 1 + 0.014 * (( $searchTime - $this->twitterCreateTime) / 3600), -1/2);
		return $timeValue;
	}

	/**
	 * 获取相应公式的值
	 *
	 * @return float
	 * @access public
	 */	
	public function getValue() {
		//$cutTime = strtotime('2012-07-26 10:00:00');
		$verifyStatValue = $this->getVerifyStatValue();
		$rankLikeValue = $this->getRankLikeValue();
		$timeValue = $this->getTimeValue();
		$userModulus = $this->userModulus;
		return $userModulus * pow($verifyStatValue * $rankLikeValue * $timeValue, 1/3);
	}
	/**
	 * 获取类目用户价值相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getCpcCatalogExpr() {
		$verifyStatExpr = $this->getVerifyStatExpr();
		$rankLikeExpr = $this->getRankLikeExpr("catalog_count_like");
		$timeExpr = $this->getTimeExpr();
		return "pow( 
			$verifyStatExpr *
			$rankLikeExpr *
			$timeExpr, 1/3)";	

		/*return "pow( 
			(IF( verify_stat == 0, 0.2, 0) + IF( verify_stat == 1, 0.4, 0 ) + IF( verify_stat == 2, 1, 0) ) *
			IF(catalog_count_like> 1000, 1, 0.1 + pow(catalog_count_like, 1/3) / 10 * 0.9) *
			pow( 1 + 0.014 * (( $searchTime - twitter_create_time) / 3600), -1/2), 1/3
		) 
		";*/	
	}
	
	/**
	 * 获取审核状态相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getVerifyStatExpr() {
		return "(IF( verify_stat == 0, 0.2, 0) + IF( verify_stat == 1, 0.4, 0 ) + IF( verify_stat == 2, 1, 0))";
	}

	/**
	 * 获取喜欢数相应公式
	 *
	 * @return string
	 * @param string $column 小红心的列名 
	 * @access public
	 */	
	public function getRankLikeExpr($column = "rank_like") {
		return "IF($column > 1000, 1, 0.1 + pow($column , 1/3) / 10 * 0.9)";
	}

	/**
	 * 获取时间衰减公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getTimeExpr() {
		$searchTime = time();
		return "pow( 1 + 0.014 * (( $searchTime - twitter_create_time) / 3600), -1/2)";
	}

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		/*return "pow( 
			( IF( verify_stat == 0, 0.2, 0) + IF( verify_stat == 1, 0.4, 0 ) + IF( verify_stat == 2, 1, 0) ) *
			IF(rank_like > 1000, 1, 0.1 + pow(rank_like, 1/3) / 10 * 0.9) *
			pow( 1 + 0.014 * (( $searchTime - twitter_create_time) / 3600), -1/2), 1/3
		) 
		";*/	
		$verifyStatExpr = $this->getVerifyStatExpr();
		$rankLikeExpr = $this->getRankLikeExpr();
		$timeExpr = $this->getTimeExpr();
		$userModulus = $this->userModulus;
		return "$userModulus * pow( 
			$verifyStatExpr *
			$rankLikeExpr *
			$timeExpr, 1/3)";	
	}


}
