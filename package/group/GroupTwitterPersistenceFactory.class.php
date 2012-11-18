<?php
namespace Snake\Package\Group;

class GroupTwitterPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
		//return TRUE;
        return new TopicGroupTwitterMapper();
    }
	function getObject(array $groupTwitter) {
		return new TopicGroupTwitterObject($groupTwitter);
	}
    function getQueryFactory(){
		//return TRUE;
        return new TopicGroupTwitterQueryFactory();
    }

    function getCollection(array $array) {
		//return TRUE;
        return new TopicGroupTwitterCollection($array, $this->getObject(array()));
    }
}
