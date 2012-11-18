<?php
namespace Snake\Package\Goods;

class AttrWeightObject extends \Snake\Package\Base\DomainObject{

	private $words = array();
	
	function __construct($words = array()) {
		$this->words = $words;
	}

	public function getWords() {
		return $this->words;
	}
}

