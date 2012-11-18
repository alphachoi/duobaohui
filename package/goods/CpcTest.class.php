<?php
namespace Snake\Package\Goods;

class CpcTest extends Abtest{
	
	const SYMBOL = "cpc";

	function isAbtest($request) {
		if ($request == 'attr') {
			return TRUE;
		}
		if (isset($request) && in_array($request, $this->remainder)) {
			return TRUE;
		}
		elseif (is_numeric($request)) {
			 return FALSE;
		}
		if (isset($request) && parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}
	
}
