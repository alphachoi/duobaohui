<?php
namespace Snake\Package\Goods;

Use \Snake\Libs\Thrift\Packages\BoolOperation;
Use \Snake\Libs\Thrift\Packages\FilterOpcode;
Use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Libs\PlatformService\MlsStorageService;
Use \Snake\Package\Goods\Helper\DBGoodsHelper AS DBGoodsHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');

class Goods {

	private $fields = array();
	private $goods = array();
	private $dataConvert = TRUE;

    public function __construct($fields = array(), $goods = array()) {
		$this->fields = $fields;
		$this->goods = $goods;
	}

	public function returnConvertData($convert = TRUE) {
		$this->dataConvert = $convert;
		return TRUE;	
	}

	public function getGoodsByGids($gids, $obj = FALSE) {
		if (empty($this->fields) || empty($gids)) {
			return array();
		}

		//设置过滤值
		$val = new Anyval();
		
		$anyvalArr = array();
        foreach ($gids as $gid) {
            $anyval = new AnyVal();
            $anyval->SetI32($gid);
            $anyvalArr[] = $anyval;
        }    

		//构造sql
		$identityObject = new IdentityObject();
		$identityObject->field('goods_id')->in($gids);
		//设置需要获取的列
		$identityObject->col($this->fields);
		/*$factory = GoodsPersistenceFactory::getFactory('Goods');
		$domainObjectAssembler = new DomainObjectAssembler($factory);
		$goodsCollection = $domainObjectAssembler->find($identityObject);*/

		//list($selection, $values) = TwitterPersistenceFactory::getFactory('Twitter')->getQueryFactory()->newSelection($identityObject);
		//sql查询
		//$twitters = TwitterPersistenceFactory::getFactory('Twitter')->getMapper()->get($selection, $values);
		//获取twitter对象的集合
		//$twitterCollection = TwitterPersistenceFactory::getFactory('Twitter')->getCollection($twitters);

		$domainObjectAssembler = new DomainObjectAssembler(GoodsPersistenceFactory::getFactory('\Snake\Package\Goods\GoodsPersistenceFactory'));
		//$goodsCollection = $domainObjectAssembler->storageFind($identityObject);
		$goodsCollection = $domainObjectAssembler->storageUniqRowFind($identityObject, $anyvalArr, array());
		//遍历集合
		$goods = array();
		while ($goodsCollection->valid()) {
			$goodsObj = $goodsCollection->next();	
			if ($obj) {
				$goods[] = $goodsObj;
			}
			else {
				$goods[] = $goodsObj->getRow($this->dataConvert);
			}
		}
		$this->goods = $goods;
		return $goods;
	}

	function goodsShelfInSet($gid) {
		if (empty($gid)) {
			return FALSE;	
		}
		return GoodsShelfRedis::sAdd("", $gid);	
	}

}
