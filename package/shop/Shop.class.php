<?php
/**
 *
 * Shop.class.php
 *
 * Shop相关信息的一个描述类
 *
 * @author ZhengXuan < xuanzheng@meilishuo.com >
 * @version 1.0
 * @todo 扩展时候需要按面向对象，现在很不好。。。
 *
 */
namespace Snake\Package\Shop;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler;
Use Snake\Package\Shop\Helper\DBShopExtInfoHelper;

class Shop {

	private $shopIds = array();
	private $shopExtInfos = array();

	public function setShopId($shopId) {
		if (empty($shopId)) {
			return FALSE;
		}
		array_push($this->shopIds, $shopId);
		return TRUE;
	}

	public function setShopIds($shopIds) {
		if (empty($shopIds) || !is_array($shopIds)) {
			return FALSE;	
		}	
		$this->shopIds = array_merge($this->shopIds, $shopIds);
		return TRUE;
	}

	public function setShopExtInfo() {
		if (empty($this->shopIds)) {
			return FALSE;
		}
		$params = array();
		$params['shop_id'] = $this->shopIds;
		$shopExtInfos = $this->getShopExtInfoFromDB($params, "/*Shop-zx*/*");
		if (empty($shopExtInfos)) {
			return FALSE;
		}
		foreach ($shopExtInfos as $shopExtInfo) {
			$this->shopExtInfos[$shopExtInfo['shop_id']] = $shopExtInfo;
		}
		return TRUE;
	}

	public function getShopExtInfo() {
		return $this->shopExtInfos;
	}

	static function getRandShops($num=4 , $baseNum=100) {
		$sqlComm = "SELECT STRAIGHT_JOIN * FROM  t_dolphin_shop_extinfo  t1 LEFT JOIN t_dolphin_shop_stat t2 ON t1.shop_id=t2.shop_id WHERE t1.pic_path<>'' ORDER BY t2.interest_num DESC LIMIT {$baseNum}"; 
		$result = array();
//		self::$db->getQueryData( $result, $sqlComm, array() );
		$result = DBShopExtinfoHelper::getConn()->read($sqlComm, $sqlData);
		$ret = array();
		$keys = array_rand( $result, $num );
		if( $num > 1 ) { 
			foreach ($keys as $k) {
				$ret[] = $result[$k];
			}
		} else {
			$ret[] = $result[$keys];
		}
		return $ret;
	}

	private function getShopExtInfoFromDB($params = array(), $selectComm = "*") {

		if (empty($params)) {
			return array();
		}

		$selectComm = explode(",", $selectComm);
		$identityObject = new IdentityObject();
		if (isset($params['shop_id'])) {
			if (is_array($params['shop_id'])) {
				$identityObject->field('shop_id')->in($params['shop_id']);	
			}
			else {
				$identityObject->field('shop_id')->eq($params['shop_id']);	
			}
		}
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(ShopExtInfoPersistenceFactory::getFactory('\Snake\Package\Shop\ShopExtInfoPersistenceFactory'));
		$ShopExtInfoCollection = $domainObjectAssembler->mysqlFind($identityObject);
		while ($ShopExtInfoCollection->valid()) { 
			$ShopExtInfoObj = $ShopExtInfoCollection->next();
			$shop[] = $ShopExtInfoObj->getRow();
		}
		return $shop;
	}
	
}
