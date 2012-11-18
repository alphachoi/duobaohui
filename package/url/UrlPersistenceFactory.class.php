<?php
namespace Snake\Package\Url;

class UrlPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new UrlMapper();
    }

	function getObject(array $url) {
		return new UrlObject($url);	
	}

	function getQueryFactory() {
		return new UrlQueryFactory();
	}

	/*function getMemcache(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
		return new TwitterMemcache($memidobj);	
	}*/

    function getCollection(array $url) {
        return new UrlCollection($url, $this->getObject(array()));
    }
}
?>
