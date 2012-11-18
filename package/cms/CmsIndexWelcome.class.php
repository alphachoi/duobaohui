<?php
namespace  Snake\Package\Cms;
/**
 * @author xuanzheng@durian.meilishuo.com
 * @since 2012-07-02
 * @version 1.0
 */
Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler;

/**
 * CmsIndexWelcome class
 *
 * t_dolphin_cms_index_welcome 表的相关操作类
 *
 * @author xuanzheng@durian.meilishuo.com
 * @since 2012-07-02
 * @version 1.0
 */
class CmsIndexWelcome {

	/**
	 * 取出cms_index_welcome数据函数
	 * @author xuanzheng@durian.meilishuo.com
	 * @param array() date_type,page_type,limit等参数
	 * @return array() 一个数据的数组
	 */
	static public function getCmsData($params, $selectComm) {
		if (empty($params)) {
			return array();
		}

		$selectComm = explode(",", $selectComm);
		$identityObject = new IdentityObject();
		if (isset($params['date_type'])) {
			$identityObject->field('date_type')->eq($params['date_type']); 
		}
		if (isset($params['page_type'])) {
			$identityObject->field('page_type')->eq($params['page_type']); 
		}
		if (isset($params['type_id'])){
			$identityObject->field('type_id')->eq($params['type_id']);
		}
		if (isset($params['orderby'])) {
			$identityObject->orderby($params['orderby']);
		}
		if (isset($params['limit'])) {
			$identityObject->limit($params['limit']);
		}
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(CmsIndexWelcomePersistenceFactory::getFactory('\Snake\Package\Cms\CmsIndexWelcomePersistenceFactory'));
		$cmsIndexWelcomeCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($cmsIndexWelcomeCollection->valid()) {
			$cmsIndexWelcomeObj = $cmsIndexWelcomeCollection->next();
			$cmsData[] = $cmsIndexWelcomeObj->getData();
		}
		return $cmsData;
	}



}

