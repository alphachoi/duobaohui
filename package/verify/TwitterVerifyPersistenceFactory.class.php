<?php
namespace Snake\Package\Verify;

class TwitterVerifyPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new TwitterVerifyMapper();
    }

	function getQueryFactory() {
		return new TwitterVerifyQueryFactory();
	}

	function getObject(array $TwitterVerify) {
		return new TwitterVerifyObject($TwitterVerify);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new TwitterVerifyCollection($array, $this->getObject(array()));
    }
}
?>
