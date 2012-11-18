<?php
namespace Snake\Package\Goods;

class ShareGroupRecommendAbtest extends Abtest{
	
	const SYMBOL = "ShareGroupRecommend";

	function isAbtest() {
		if (parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}
	
}
