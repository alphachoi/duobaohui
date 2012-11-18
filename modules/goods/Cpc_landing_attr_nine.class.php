<?php
namespace Snake\Modules\Goods;
Use Snake\Package\Recommend\Recommend;
Use Snake\Package\Goods\Attribute;
Use Snake\Package\Twitter\Twitter;
Use Snake\Package\Goods\AttrWords;
Use Snake\Libs\Cache\Memcache;

class Cpc_landing_attr_nine extends \Snake\Libs\Controller{


	public function run() {
		$matchInfos = $this->getCache();
		if (empty($matchInfos)) {
			$wordNames = array("韩版","单鞋","连衣裙","复古","休闲");
			$attributeHelper = new Attribute();
			$matchInfos = array();
			$matchInfosTmp = $attributeHelper->getCpcNine($wordNames);
			if (!empty($matchInfosTmp)) {
				$matchInfos = $matchInfosTmp;
				$this->setCache($matchInfos);
			}
		}
		$this->view = $matchInfos;
		return TRUE;
	}


	private function setCache($attrInfo = array()) {
		if (empty($attrInfo)) {
			return FALSE;
		}
		$cacheHelper = Memcache::instance();
		$cacheKey = $this->getCacheKey();
		return 	$cacheHelper->set($cacheKey, $attrInfo, 3600 * 2);
	}

	private function getCache() {
		$cacheHelper = Memcache::instance();
		$cacheKey = $this->getCacheKey();
		return $cacheHelper->get($cacheKey);
	}
		
	private function getCacheKey() {
		return "CACHE_CPC_LANDING_ATTR_NINE";	
	}

}
