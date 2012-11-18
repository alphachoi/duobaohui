<?php
namespace Snake\Package\Cms;

class CmsTypeObject extends \Snake\Package\Base\DomainObject{
	
	private $data = array();

    public function __construct($data = array()) {
		$this->data = $data;
	}

    public function getData() {
        return $this->data;
    }  
}
