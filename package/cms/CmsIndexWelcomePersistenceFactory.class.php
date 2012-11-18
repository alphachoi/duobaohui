<?php
namespace Snake\Package\Cms;

class CmsIndexWelcomePersistenceFactory extends \Snake\Package\Base\PersistenceFactory {  

    function getMapper() {
        return new CmsIndexWelcomeMapper();
    }


	function getQueryFactory(){
		return new CmsIndexWelcomeQueryFactory();
	}


    function getCollection(array $array) {
        return new CmsIndexWelcomeCollection($array, $this->getObject(array()));
    }


	function getObject(array $array) {
		return new CmsIndexWelcomeObject($array);
	}
}
?>
