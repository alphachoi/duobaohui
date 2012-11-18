<?php
namespace Snake\Package\Goods;

class AttrClickAbtest extends Abtest {

	const SYMBOL = "AttrClick";

	function isAbtest() {
		if (parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}
} 
