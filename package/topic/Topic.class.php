<?php
/**
 *
 * Topic.class.php
 *
 * Topic的一个描述类
 *
 * @author ZhengXuan < xuanzheng@meilishuo.com >
 * @version 1.0
 * @todo 扩展的时候需要全面发展为面向对像...
 *
 */
namespace Snake\Package\Topic;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler;

class Topic {

	
	static function getTopicInfo($params = array(), $selectComm = "*") {

		if (empty($params)) {
			return array();
		}

		$selectComm = explode(",", $selectComm);
		$identityObject = new IdentityObject();
		if (isset($params['topic_id'])) {
			$identityObject->field('topic_id')->eq($params['topic_id']);	
		}
		if (isset($params['topic_sign'])) {
			$identityObject->field('topic_sign')->eq($params['topic_sign']);	
		}
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(TopicInfoPersistenceFactory::getFactory('\Snake\Package\Topic\TopicInfoPersistenceFactory'));
		$topicInfoCollection = $domainObjectAssembler->mysqlFind($identityObject);
		while ($topicInfoCollection->valid()) { 
			$topicInfoObj = $topicInfoCollection->next();
			$topic[] = $topicInfoObj->getRow();
		}
		return $topic;
	}

}
