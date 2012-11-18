<?php
namespace Snake\Package\Group;

class GroupClassifyPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
		//return TRUE;
        return new TopicGroupClassifyMapper();
    }


    function getQueryFactory(){
		//return TRUE;
        return new TopicGroupClassifyQueryFactory();
    }


    function getCollection(array $array) {
		//return TRUE;
        return new TopicGroupClassifyCollection($array, $this->getObject(array()));
    }

	 function getObject(array $array) {
		 return new TopicGroupClasssifyObject($array);	
	 }
}
