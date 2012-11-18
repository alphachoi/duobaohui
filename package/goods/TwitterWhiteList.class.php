<?php
namespace Snake\Package\Goods;
/**
 * @author xuanzheng@meilishuo.com
 */

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 

/**
 * Guang的热榜最新的tids，数据库相关操作
 * @author xuanzheng@meilishuo.com
 */

class TwitterWhiteList {

	/**
	 * 从数据库中取tids
	 * @author xuanzheng@meilishuo.com
	 */

	static public function getWhiteListTids ($offset, $limit) {
		if (!isset($offset) || !isset($limit)) {
			return array();
		}
		$identityObject = new IdentityObject();
		$limit = "{$offset},{$limit}";
		$identityObject->field('twitter_id')->noteq(0);
		$identityObject->orderby("twitter_id desc");
		$identityObject->limit($limit);
		$identityObject->col(array("/*whiteListTids-zx*/twitter_id"));
		$domainObjectAssembler = new DomainObjectAssembler(TwitterWhiteListPersistenceFactory::getFactory('\Snake\Package\Goods\TwitterWhiteListPersistenceFactory'));
		$twitterWhiteListCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($twitterWhiteListCollection->valid()) {
			$twitterWhiteListObj = $twitterWhiteListCollection->next();
			$twitterIds[] = $twitterWhiteListObj->getTwitterIds();
		}
		return $twitterIds;
	}
}
