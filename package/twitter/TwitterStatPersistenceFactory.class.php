<?php
namespace Snake\Package\Twitter;

class TwitterStatPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new TwitterStatMapper();
    }

	function getObject(array $twitter) {
		return new TwitterStatObject($twitter);	
	}

	function getQueryFactory() {
		return new TwitterStatQueryFactory();
	}

//	function getMemcache(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
//		return new TwitterMemcache($memidobj);	
//	}

    function getCollection(array $twitter) {
        return new TwitterStatCollection($twitter, $this->getObject(array()));
    }
}
?>
