<?php
namespace Snake\Package\Group;

class GroupUserPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
		//return TRUE;
        return new TopicGroupUserMapper();
    }
	function getObject(array $groupUser) {
		return new TopicGroupUserObject($groupUser);
	}
    function getQueryFactory(){
		//return TRUE;
        return new TopicGroupUserQueryFactory();
    }

    function getCollection(array $array) {
		//return TRUE;
        return new TopicGroupCollection($array, $this->getObject(array()));
    }
}
