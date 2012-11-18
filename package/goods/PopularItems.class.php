<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 

class PopularItems{

	public function getPopularItemData() {
		$identityObject = new IdentityObject();
		$identityObject->field('id')->noteq(-1)->orderby('item_sortno asc');
		$identityObject->col(array("item_name","item_link","item_number","item_sortno"));
		$popularItems = $this->getPopualrItems($identityObject);
		return $popularItems;	
	}

	public function getPopualrItems(IdentityObject $identityObject) {
		$domainObjectAssembler = new DomainObjectAssembler(PopularItemsPersistenceFactory::getFactory('\Snake\Package\Goods\PopularItemsPersistenceFactory'));
		$popualrItemsCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$popualrItems = array();	
		while ($popualrItemsCollection->valid()) {
			$rowObj = $popualrItemsCollection->next();	
			$popualrItems[] = $rowObj->getRow();
		}
		return $popualrItems;
	}

}
