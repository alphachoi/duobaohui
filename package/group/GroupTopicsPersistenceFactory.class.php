<?php
namespace Snake\Package\Group;

class GroupTopicsPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
		//return TRUE;
        return new TopicGroupTopicsMapper();
    }


    function getQueryFactory(){
		//return TRUE;
        return new TopicGroupTopicsQueryFactory();
    }


    function getCollection(array $array) {
		//return TRUE;
        return new TopicGroupTopicsCollection($array, $this->getObject(array()));
    }

	 function getObject(array $array) {
		 return new TopicGroupTopicsObject($array);	
	 }
}
