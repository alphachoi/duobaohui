<?php
namespace Snake\Package\Topic;

class TopicInfoPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new TopicInfoMapper();
    }

	function getQueryFactory() {
		return new TopicInfoQueryFactory();
	}

	function getObject(array $goods) {
		return new TopicInfoObject($goods);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new TopicInfoCollection($array, $this->getObject(array()));
    }
}
?>
