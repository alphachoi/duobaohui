<?php
namespace Snake\Package\Goods;

class AttributeTest extends Abtest{
	
	const SYMBOL= "attribute";

	function isAbtest($request) {
		$boo = parent::abtest($request);
		return $boo;
	}
	
}
