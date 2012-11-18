<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 

class GoodsVerify{

	public function getGoodsVerify(IdentityObject $identityObject) {
		$domainObjectAssembler = new DomainObjectAssembler(GoodsVerifyPersistenceFactory::getFactory('\Snake\Package\Goods\GoodsVerifyPersistenceFactory'));
		$goodsVerifyCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$goodsVerify = array();	
		while ($goodsVerifyCollection->valid()) {
			$rowObj = $goodsVerifyCollection->next();	
			$goodsVerify[] = $rowObj->getRow();
		}
		return $goodsVerify;
	}

}
