<?php
namespace Snake\Package\Group;
use \Snake\Package\Group\TopicGroupObject;
use \Snake\Package\Group\TopicGroupMapper;
use \Snake\Package\Group\TopicGroupQueryFactory;
use \Snake\Package\Group\TopicGroupCollection;

class GroupPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
		//return TRUE;
        return new TopicGroupMapper();
    }


    function getQueryFactory(){
		//return TRUE;
        return new TopicGroupQueryFactory();
    }


    function getCollection(array $array) {
		//return TRUE;
        return new TopicGroupCollection($array, $this->getObject(array()));
    }

	 function getObject(array $array) {
		 return new TopicGroupObject($array);	
	 }
}
