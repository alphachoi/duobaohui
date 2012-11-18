<?php
namespace Snake\Package\Cms;

class CmsIndexTypePersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new CmsIndexTypeMapper();
    }


	function getQueryFactory(){
		return new CmsIndexTypeQueryFactory();
	}


    function getCollection(array $array) {
        return new CmsIndexTypeCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new CmsIndexTypeObject($array);
	}
}
?>
