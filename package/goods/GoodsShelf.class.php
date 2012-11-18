<?php
namespace Snake\Package\Goods;
/**
 * @author xuanzheng@meilishuo.com
 */

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 
Use Snake\Package\Goods\GoodsShelfPersistenceFactory;

/**
 * Guang的热榜最新的tids，数据库相关操作
 * @author xuanzheng@meilishuo.com
 */

class GoodsShelf {

	
	/**
	 * 下架标识
	 *
	 * @var int 
	 * @access private 
	 */
	private $shelf = 1;

	public function getGoodsShelf(IdentityObject $identityObject, $collection = FALSE) {
		$domainObjectAssembler = new DomainObjectAssembler(GoodsShelfPersistenceFactory::getFactory('\Snake\Package\Goods\GoodsShelfPersistenceFactory'));
		$goodsShelfCollection = $domainObjectAssembler->mysqlFind($identityObject);

		if ($collection) {
			return $twitterCollection;
		}
		$goodsShelf = array();	
		while ($goodsShelfCollection->valid()) {
			$rowObj = $goodsShelfCollection->next();	
			$goodsShelf[] = $rowObj->getRow();
		}
		return $goodsShelf;
	}

	/**
	 * 判断商品是否下架
	 *
	 * @return int 0/1
	 * @access public
	 * @param $gid 
	 */	
	public function isOnshelf($gid) {
		$identityObject = new IdentityObject();
		$identityObject->field('goods_id')->in(array($gid))->field('shelf')->eq($this->shelf);
		$identityObject->col(array("shelf"));
		$goodsShelf = $this->getGoodsShelf($identityObject);
		if (empty($goodsShelf)) {
			return 0;
		}
		return 1;
	}

	/**
	 * 通过id取得info in goodsShelf
	 * @param array 
	 * @param array
	 * @return array
	 */
	public function getGoodsShelfByGid($gids = array(), $fields = array('item_id')) {
		$identityObject = new IdentityObject();
		$identityObject->field('goods_id')->in($gids);
		$identityObject->col($fields);
		$goodsShelf = $this->getGoodsShelf($identityObject);
		return $goodsShelf;
	}
}
