<?php
namespace Snake\Package\Cpc;

class CpcObject extends \Snake\Package\Base\DomainObject {

    public function __construct($cpc = array()) {
		$this->row = $cpc;
	}
		
}
