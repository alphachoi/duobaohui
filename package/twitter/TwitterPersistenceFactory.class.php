<?php
namespace Snake\Package\Twitter;

class TwitterPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new TwitterMapper();
    }

	function getObject(array $twitter) {
		return new TwitterObject($twitter);	
	}

	function getQueryFactory() {
		return new TwitterQueryFactory();
	}

	function getMemcache(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
		return new TwitterReplyMemcache($memidobj);	
	}

    function getCollection(array $twitter) {
        return new TwitterCollection($twitter, $this->getObject(array()));
    }
}
?>
