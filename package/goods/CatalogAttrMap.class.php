<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 

class CatalogAttrMap{
	
	private $catalogAttrMap = array();

	public function getCatalogAttrMap(IdentityObject $identityObject) {
		$domainObjectAssembler = new DomainObjectAssembler(CatalogAttrMapPersistenceFactory::getFactory('\Snake\Package\Goods\CatalogAttrMapPersistenceFactory'));
		$catalogAttrMapCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$catalogAttrMap = array();	
		while ($catalogAttrMapCollection->valid()) {
			$catalogAttrMapObj = $catalogAttrMapCollection->next();	
			$catalogAttrMap[] = $catalogAttrMapObj->getCatalogAttrMap();
		}
		$this->catalogAttrMap= $catalogAttrMap;
		return $this->catalogAttrMap;
	}

}
