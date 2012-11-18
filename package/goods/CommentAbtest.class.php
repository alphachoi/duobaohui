<?php
namespace Snake\Package\Goods;

class CommentAbtest extends Abtest{
	
	const SYMBOL = "commentsab";

	function isAbtest() {
		if (parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}
	
}
