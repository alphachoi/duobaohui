<?php
namespace Snake\Package\Twitter;

class TwitterVerifyPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new TwitterVerifyMapper();
    }

	function getObject(array $twitter) {
		return new TwitterVerifyObject($twitter);	
	}

	function getQueryFactory() {
		return new TwitterVerifyQueryFactory();
	}

//	function getMemcache(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
//		return new TwitterMemcache($memidobj);	
//	}

    function getCollection(array $twitter) {
        return new TwitterVerifyCollection($twitter, $this->getObject(array()));
    }
}
?>
