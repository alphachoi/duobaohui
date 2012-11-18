<?php
namespace Snake\Package\Twitter;

class TwitterActivityPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new TwitterActivityMapper();
    }

	function getObject(array $twitter) {
		return new TwitterActivityObject($twitter);	
	}

	function getQueryFactory() {
		return new TwitterActivityQueryFactory();
	}


    function getCollection(array $twitter) {
        return new TwitterActivityCollection($twitter, $this->getObject(array()));
    }
}
?>
