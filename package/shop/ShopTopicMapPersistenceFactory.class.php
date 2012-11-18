<?php
namespace Snake\Package\Shop;

class ShopTopicMapPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new ShopTopicMapMapper();
    }

	function getQueryFactory() {
		return new ShopTopicMapQueryFactory();
	}

	function getObject(array $shopTopicMap) {
		return new ShopTopicMapObject($shopTopicMap);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new ShopTopicMapCollection($array, $this->getObject(array()));
    }
}
?>
