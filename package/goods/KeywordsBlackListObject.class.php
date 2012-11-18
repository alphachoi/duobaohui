<?php
namespace Snake\Package\Goods;

class KeywordsObject extends \Snake\Package\Base\DomainObject{

	private $keywords = array();
	
	function __construct($keywords = array()) {
		$this->keywords = $keywords;
	}

	public function getKeywords() {
		return $this->keywords;
	}
}

