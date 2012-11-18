<?php
namespace Snake\Package\Verify;

Use \Snake\Package\Verify\Helper\DBTwitterVerifyHelper;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class TwitterVerify {


	static public function getTwitterVerify($params, $cal = "*") {

		$cal = explode(",",$cal);
		//构造sql
		$identityObject = new IdentityObject();

		if (!empty($params['twitter_id'])) {
			if (is_array($params['twitter_id'])) {
				$identityObject->field('twitter_id')->in($params['twitter_id']);
			}
			else {
				$identityObject->field('twitter_id')->eq($params['twitter_id']);
			}
		}

		if (empty($params['twitter_id'])) {
			$identityObject->field('twitter_id')->noteq(0);
		}

		if (isset($params['verify_stat'])) {
			if (is_array($params['verify_stat'])) {
				$identityObject->field('verify_stat')->in($params['verify_stat']);
			}
			else {
				$identityObject->field('verify_stat')->eq($params['verify_stat']);
			}
		}

		if (!empty($params['orderby'])) {
			$identityObject->orderby($params['orderby']);
		}
		if (!empty($params['limit'])) {
			$identityObject->limit($params['limit']);
		}

		//设置需要获取的列
		$identityObject->col($cal);

		$domainObjectAssembler = new DomainObjectAssembler(TwitterVerifyPersistenceFactory::getFactory('\Snake\Package\Verify\TwitterVerifyPersistenceFactory'));
		$twitterVerifyCollection = $domainObjectAssembler->mysqlFind($identityObject);
		//遍历集合
		$twitterVerify = array();
		while ($twitterVerifyCollection->valid()) {
			$twitterVerifyObj = $twitterVerifyCollection->next();	
			$twitterVerify[] = $twitterVerifyObj->getRow();
		}
		return $twitterVerify;
	}

}
