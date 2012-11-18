<?php
namespace Snake\Package\Goods;

class CatalogGroupIcoPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new CatalogGroupIcoMapper();
    }

	function getQueryFactory() {
		return new CatalogGroupIcoQueryFactory();
	}

	function getObject(array $twitterIds) {
		return new CatalogGroupIcoObject($twitterIds);
	}


    function getCollection(array $array) {
        return new CatalogGroupIcoCollection($array, $this->getObject(array()));
    }
}
?>
