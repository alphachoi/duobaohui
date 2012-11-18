<?php
/**
 *
 * ShopRelation.class.php
 *
 * Shop 关系的相关信息的描述类
 *
 * @author ZhengXuan < xuanzheng@meilishuo.com >
 * @version 1.0
 * @todo 扩展时候需要按面向对象，现在很不好。。。
 *
 */
namespace Snake\Package\Shop;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler;

class ShopRelation {


	static function getShopTopicMapInfo($params = array(), $selectComm = "*") {

		if (empty($params)) {
			return array();
		}

		$selectComm = explode(",", $selectComm);
		$identityObject = new IdentityObject();
		if (isset($params['topic_id'])) {
			$identityObject->field('topic_id')->eq($params['topic_id']);	
		}
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(ShopTopicMapPersistenceFactory::getFactory('\Snake\Package\Shop\ShopTopicMapPersistenceFactory'));
		$ShopTopicMapCollection = $domainObjectAssembler->mysqlFind($identityObject);
		while ($ShopTopicMapCollection->valid()) { 
			$ShopTopicMapObj = $ShopTopicMapCollection->next();
			$shopTopicMap[] = $ShopTopicMapObj->getRow();
		}
		return $shopTopicMap;
	}

}
