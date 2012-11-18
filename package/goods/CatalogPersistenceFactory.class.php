<?php
namespace Snake\Package\Goods;

class CatalogPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new CatalogMapper();
    }

	function getQueryFactory() {
		return new CatalogQueryFactory();
	}

	function getObject(array $goods) {
		return new CatalogObject($goods);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new CatalogCollection($array, $this->getObject(array()));
    }
}
?>
