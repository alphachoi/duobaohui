<?php
namespace Snake\Package\Goods;

class PopularItemsPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new PopularItemsMapper();
    }

	function getQueryFactory() {
		return new PopularItemsQueryFactory();
	}

	function getObject(array $popularItems) {
		return new PopularItemsObject($popularItems);
	}

    function getCollection(array $array) {
        return new PopularItemsCollection($array, $this->getObject(array()));
    }
}
?>
