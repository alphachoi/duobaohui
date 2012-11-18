<?php
namespace Snake\Package\Shop;

class ShopExtInfoPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new ShopExtInfoMapper();
    }

	function getQueryFactory() {
		return new ShopExtInfoQueryFactory();
	}

	function getObject(array $shopExtInfo) {
		return new ShopExtInfoObject($shopExtInfo);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new ShopExtInfoCollection($array, $this->getObject(array()));
    }
}
?>
