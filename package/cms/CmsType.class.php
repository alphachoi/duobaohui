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
 * CmsIndexType class
 *
 * t_dolphin_cms_index_type 表的相关操作类
 *
 * @author xuanzheng@durian.meilishuo.com
 * @since 2012-07-02
 * @version 1.0
 */
class CmsType {

	/**
	 * 取出cms_index_type数据函数
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
		if (isset($params['type_name'])) {
			$identityObject->field('type_name')->eq($params['type_name']); 
		}
		if (isset($params['page_type'])) {
			$identityObject->field('page_type')->eq($params['page_type']); 
		}
		if (isset($params['orderby'])) {
			$identityObject->orderby($params['orderby']);
		}
		if (isset($params['limit'])) {
			$identityObject->limit($params['limit']);
		}
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(CmsIndexTypePersistenceFactory::getFactory('\Snake\Package\Cms\CmsTypePersistenceFactory'));
		$cmsIndexTypeCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($cmsIndexTypeCollection->valid()) {
			$cmsIndexTypeObj = $cmsIndexTypeCollection->next();
			$cmsData[] = $cmsIndexTypeObj->getData();
		}
		return $cmsData;
	}


}

