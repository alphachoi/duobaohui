<?php
namespace Snake\Package\Search;

/**
 * 搜索杂志社的rank
 * @package search 
 * @author huazhulin
 * @since 2012.08.15
 */
class SearchGroupExpr extends SearchExpr{


	function __construct(){
	}

	private function _getSearchTimeValue() {
		$searchTime = time();
		$result = "0.5 * pow(1 + 10 * (($searchTime - created) / 3600), -1/2)";
		return $result;
	}

	private function _getTwitterNumberAndFollowersValue() {
		return "pow(LOG10(last_twitter_number*0.1+followers+1),1/3)*IF(last_twitter_number<10,0.1,1)";
	}
	
	public function getExpr() {
		$twitterAndFollowersValue = $this->_getTwitterNumberAndFollowersValue();
		$timeValue = $this->_getSearchTimeValue();
		$expr = $twitterAndFollowersValue . '+' . $timeValue;
		$this->expr = $expr;
		return TRUE;
		//$expr = pow(LOG10(last_twitter_number*0.1+followers+1),1/3)*IF(last_twitter_number<10,0.1,1) + 0.5*pow( 1 + 10* (( $searchTime - created) / 3600), -1/2);
	}

	public function getValue() {
		return $this->expr;
	}

}


