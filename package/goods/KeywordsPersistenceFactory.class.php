<?php
namespace Snake\Package\Goods;

class KeywordsPersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new KeywordsMapper();
    }


	function getQueryFactory(){
		return new KeywordsQueryFactory();
	}


    function getCollection(array $array) {
        return new KeywordsCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new KeywordsObject($array);
	}
}
?>
