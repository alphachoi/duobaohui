<?php
namespace Snake\Package\Group;

class GroupRankPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
		//return TRUE;
        return new TopicGroupMapper();
    }


    function getQueryFactory(){
		//return TRUE;
        return new TopicGroupRankQueryFactory();
    }


    function getCollection(array $array) {
		//return TRUE;
        return new TopicGroupCollection($array, $this->getObject(array()));
    }

	 function getObject(array $array) {
		 return new TopicGroupObject($array);	
	 }
}
