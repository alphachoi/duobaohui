<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 

class CatalogGoodsMap{
	//返回结果 
	private $catalogGoodsMap = array();

	public function getCatalogGoodsMap(IdentityObject $identityObject) {
		$domainObjectAssembler = new DomainObjectAssembler(CatalogGoodsMapPersistenceFactory::getFactory('\Snake\Package\Goods\CatalogGoodsMapPersistenceFactory'));
		$catalogGoodsMapCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$catalogGoodsMap = array();	
		while ($catalogGoodsMapCollection->valid()) {
			$catalogGoodsMapObj = $catalogGoodsMapCollection->next();	
			$catalogGoodsMap[] = $catalogGoodsMapObj->getRow();
		}
		$this->catalogGoodsMap= $catalogGoodsMap;
		return $this->catalogGoodsMap;
	}

}
