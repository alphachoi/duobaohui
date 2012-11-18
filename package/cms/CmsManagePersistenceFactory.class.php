<?php
namespace Snake\Package\Cms;


class CmsManagePersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new CmsManageMapper();
    }


	function getQueryFactory(){
		return new CmsManageQueryFactory();
	}


    function getCollection(array $array) {
        return new CmsManageCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new CmsManageObject($array);
	}
}
?>
