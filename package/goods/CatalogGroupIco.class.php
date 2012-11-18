<?php
namespace Snake\Package\Goods;
/**
 * @author xuanzheng@meilishuo.com
 */

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 
Use Snake\Package\Goods\CatalogGroupIcoPersistenceFactory;

/**
 * Guang的热榜最新的tids，数据库相关操作
 * @author xuanzheng@meilishuo.com
 */

class CatalogGroupIco {

	
	/**
	 * 下架标识
	 *
	 * @var int 
	 * @access private 
	 */
	private $shelf = 1;

	public function getCatalogGroupIco(IdentityObject $identityObject, $collection = FALSE) {
		$domainObjectAssembler = new DomainObjectAssembler(CatalogGroupIcoPersistenceFactory::getFactory('\Snake\Package\Goods\CatalogGroupIcoPersistenceFactory'));
		$CatalogGroupIcoCollection = $domainObjectAssembler->mysqlFind($identityObject);

		if ($collection) {
			return $twitterCollection;
		}
		$CatalogGroupIco = array();	
		while ($CatalogGroupIcoCollection->valid()) {
			$rowObj = $CatalogGroupIcoCollection->next();	
			$CatalogGroupIco[] = $rowObj->getRow();
		}
		return $CatalogGroupIco;
	}

	/**
	 * 通过id取得info in CatalogGroupIco
	 * @param array 
	 * @param array
	 * @return array
	 */
	public function getCatalogGroupIcoByCidAndGroupName($cid = 0, $groupNames = array(), $fields = array('id', 'catalog_id', 'group_name', 'ico_sortno')) {
		foreach ($groupNames as &$name) {
			$name = "'{$name}'";
		}
		$identityObject = new IdentityObject();
		$identityObject->field('catalog_id')->eq($cid);
		$identityObject->field('group_name')->in($groupNames);
		$identityObject->col($fields);
		$CatalogGroupIco = $this->getCatalogGroupIco($identityObject);
		return $CatalogGroupIco;
	}
}
