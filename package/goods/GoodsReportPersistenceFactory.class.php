<?php
namespace Snake\Package\Goods;

class GoodsReportPersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new GoodsReportMapper();
    }

	function getQueryFactory() {
		return new GoodsReportQueryFactory();
	}

	function getObject(array $goodsReport) {
		return new GoodsReportObject($goodsReport);
	}

    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new GoodsReportCollection($array, $this->getObject(array()));
    }
}
?>
