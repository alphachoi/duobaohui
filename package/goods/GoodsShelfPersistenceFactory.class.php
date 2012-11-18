<?php
namespace Snake\Package\Goods;

class GoodsShelfPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new GoodsShelfMapper();
    }

	function getQueryFactory() {
		return new GoodsShelfQueryFactory();
	}

	function getObject(array $twitterIds) {
		return new GoodsShelfObject($twitterIds);
	}


    function getCollection(array $array) {
        return new GoodsShelfCollection($array, $this->getObject(array()));
    }
}
?>
