<?php
namespace Snake\Package\Goods;

class AttrTwitterCtr1PersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new AttrTwitterCtr1Mapper();
    }

	function getQueryFactory() {
		return new AttrTwitterCtr1QueryFactory();
	}

	function getObject(array $twitterIds) {
		return new AttrTwitterCtr1Object($twitterIds);
	}


    function getCollection(array $array) {
        return new AttrTwitterCtr1Collection($array, $this->getObject(array()));
    }
}
?>
