<?php
namespace Snake\Package\Cms;

class CmsTypePersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new CmsTypeMapper();
    }


	function getQueryFactory(){
		return new CmsTypeQueryFactory();
	}


    function getCollection(array $array) {
        return new CmsTypeCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new CmsTypeObject($array);
	}
}
?>
