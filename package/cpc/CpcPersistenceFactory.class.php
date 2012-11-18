<?php
namespace Snake\Package\Cpc;

class CpcPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{

    function getMapper() {
        return new CpcMapper();
    }

	function getObject(array $cpc) {
		return new CpcObject($cpc);	
	}

	function getQueryFactory() {
		return new CpcQueryFactory();
	}

	/*function getMemcache(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
		return new TwitterMemcache($memidobj);	
	}*/

    function getCollection(array $cpc) {
        return new CpcCollection($cpc, $this->getObject(array()));
    }
}
?>
