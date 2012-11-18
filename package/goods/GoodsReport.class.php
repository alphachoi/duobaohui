<?php
namespace Snake\Package\Goods;

Use \Snake\Package\Goods\Helper\DBGoodsReportHelper;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class GoodsReport {


	static public function getGoodsReport($params, $cal = "*") {

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

		if (!empty($params['orderby'])) {
			$identityObject->orderby($params['orderby']);
		}
		if (!empty($params['limit'])) {
			$identityObject->limit($params['limit']);
		}

		//设置需要获取的列
		$identityObject->col($cal);

		$domainObjectAssembler = new DomainObjectAssembler(GoodsReportPersistenceFactory::getFactory('\Snake\Package\Goods\GoodsReportPersistenceFactory'));
		$goodsReportCollection = $domainObjectAssembler->mysqlFind($identityObject);
		//遍历集合
		$goodsReport = array();
		while ($goodsReportCollection->valid()) {
			$goodsReportObj = $goodsReportCollection->next();	
			$goodsReport[] = $goodsReportObj->getRow();
		}
		return $goodsReport;
	}

}
