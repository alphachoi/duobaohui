<?php
namespace Snake\Package\Goods;

class GoodsPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new GoodsMapper();
    }

	function getQueryFactory() {
		return new GoodsQueryFactory();
	}

	function getObject(array $goods) {
		return new GoodsObject($goods);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new GoodsCollection($array, $this->getObject(array()));
    }
}
?>
