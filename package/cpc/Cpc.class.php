<?php
namespace Snake\Package\Cpc;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class Cpc{

	private $cpc = array();

	public static $running = array(1);

    public function __construct() {
	}

	public function getCpcInfo(IdentityObject $identityObject) {
		$domainObjectAssembler = new DomainObjectAssembler(CpcPersistenceFactory::getFactory('\Snake\Package\Cpc\CpcPersistenceFactory'));
		$cpcCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$cpc = array();	
		while ($cpcCollection->valid()) {
			$cpcObj = $cpcCollection->next();	
			$cpc[] = $cpcObj->getRow();
		}
		$this->cpc = $cpc;
		return $this->cpc;
	}
	
	public function isCpc($tid) {
		$col = array('twitter_id','verify_stat');
		$identityObject = new \Snake\Package\Base\IdentityObject();
		$identityObject->field('twitter_id')->in(array($tid));
		$identityObject->col($col);
		$this->cpc = $this->getCpcInfo($identityObject);
		if (!empty($this->cpc)) {
			return 1;
		}
		return 0;
	}
}
