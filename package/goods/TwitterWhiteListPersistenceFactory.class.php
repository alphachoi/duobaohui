<?php
namespace Snake\Package\Goods;

class TwitterWhiteListPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new TwitterWhiteListMapper();
    }

	function getQueryFactory() {
		return new TwitterWhiteListQueryFactory();
	}

	function getObject(array $twitterIds) {
		return new TwitterWhiteListObject($twitterIds);
	}


    function getCollection(array $array) {
        return new TwitterWhiteListCollection($array, $this->getObject(array()));
    }
}
?>
