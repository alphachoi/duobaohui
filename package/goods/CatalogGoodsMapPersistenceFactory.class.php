<?php
namespace Snake\Package\Goods;

class CatalogGoodsMapPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new CatalogGoodsMapMapper();
    }

	function getQueryFactory() {
		return new CatalogGoodsMapQueryFactory();
	}

	function getObject(array $goods) {
		return new CatalogGoodsMapObject($goods);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new CatalogGoodsMapCollection($array, $this->getObject(array()));
    }
}
?>
