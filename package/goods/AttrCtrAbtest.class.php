<?php
namespace Snake\Package\Goods;

class AttrCtrAbtest extends Abtest{
	
	const SYMBOL = "AttrCtr";

	function isAbtest() {
		if (parent::abtestForNewUser()) {
			return TRUE;
		}
		return FALSE;
	}
	
}
