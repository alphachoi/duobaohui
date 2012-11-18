<?php
namespace Snake\Package\Goods;

class CatalogAttrMapPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new CatalogAttrMapMapper();
    }

	function getQueryFactory() {
		return new CatalogAttrMapQueryFactory();
	}

	function getObject(array $goods) {
		return new CatalogAttrMapObject($goods);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new CatalogAttrMapCollection($array, $this->getObject(array()));
    }
}
?>
