<?php
namespace Snake\Package\Twitter;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class TwitterVerify {

	private $twitterVerify = array();

    public function __construct() {
	}

	public function getTwitterVerify(IdentityObject $identityObject) {

		$domainObjectAssembler = new DomainObjectAssembler(TwitterVerifyPersistenceFactory::getFactory('\Snake\Package\Twitter\TwitterVerifyPersistenceFactory'));
		$twitterVerifyCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$twitterVerify = array();	
		while ($twitterVerifyCollection->valid()) {
			$twitterVerifyObj = $twitterVerifyCollection->next();	
			$twitterVerify[] = $twitterVerifyObj->getRow();
		}
		$this->twitterVerify = $twitterVerify;
		return $this->twitterVerify;
	}
}
