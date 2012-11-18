<?php
namespace Snake\Package\Goods;

class GoodsVerifyPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new GoodsVerifyMapper();
    }

	function getQueryFactory() {
		return new GoodsVerifyQueryFactory();
	}

	function getObject(array $goods) {
		return new GoodsVerifyObject($goods);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new GoodsVerifyCollection($array, $this->getObject(array()));
    }
}
?>
