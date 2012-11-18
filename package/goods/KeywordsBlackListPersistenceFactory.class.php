<?php
namespace Snake\Package\Goods;

class KeywordsBlackListPersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new KeywordsBlackListMapper();
    }


	function getQueryFactory(){
		return new KeywordsBlackListQueryFactory();
	}


    function getCollection(array $array) {
        return new KeywordsBlackListCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new KeywordsBlackListObject($array);
	}
}
?>
