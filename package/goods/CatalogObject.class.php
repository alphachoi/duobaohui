<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class CatalogObject extends \Snake\Package\Base\DomainObject{

    public function __construct($catalog = array()) {
		$this->row = $catalog;
	}
	
	public function getId() {
		return $this->row['catalog_id'];		
	}
}
