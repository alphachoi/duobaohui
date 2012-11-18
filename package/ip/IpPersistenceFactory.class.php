<?php
namespace Snake\Package\Ip;

class IpPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new IpMapper();
    }

	function getObject(array $ip) {
		return new IpObject($ip);	
	}

	function getQueryFactory() {
		return new IpQueryFactory();
	}

	/*function getMemcache(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
		return new TwitterMemcache($memidobj);	
	}*/

    function getCollection(array $ip) {
        return new IpCollection($ip, $this->getObject(array()));
    }
}
?>
