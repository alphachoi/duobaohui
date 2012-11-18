<?php
namespace Snake\Package\Goods;

class KeywordsObject extends \Snake\Package\Base\DomainObject{

	
	function __construct($keywords = array()) {
		$this->row = $keywords;
	}

	function getId() {
		return $this->row['word_id'];	
	}
}

