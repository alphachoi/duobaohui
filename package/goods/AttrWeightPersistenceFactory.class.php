<?php
namespace Snake\Package\Goods;

class AttrWeightPersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new AttrWeightMapper();
    }


	function getQueryFactory(){
		return new AttrWeightQueryFactory();
	}


    function getCollection(array $array) {
        return new AttrWeightCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new AttrWeightObject($array);
	}
}
?>
