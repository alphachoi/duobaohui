<?php
namespace Snake\Scripts;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\GroupMainCatalog AS GMainCatalog;
use \Snake\Package\User\UserFollowGroup;
use \Snake\Package\Group\AssembleGroup AS AssembleGroup; 

//add by huazhulin
class Setgroupcache extends \Snake\Libs\Base\Scripts {

	public function run() {
		$this->main();
	}

	public function main() {
        $gIds = array();
        $groupInfo = array();
        $mem = Memcache::instance();
        //set cache for maincatalogInfo
		$cacheKey = "snake:group:maincatalog:catalogInfo";
		$groupHelper = new GMainCatalog();
		$groupHelper->getMainCatalogInfo();
		$groupBy = $groupHelper->getGroupBy();
		$groupIds = $groupHelper->getGroupIds();
		$mainInfo['group_by'] = $groupBy;
		$mainInfo['group_ids'] = $groupIds; 
		$mem->set($cacheKey, $mainInfo, 60*10);
        foreach ($groupBy AS $k => $v) {
            $memKey = "snake:group:maincatalog:" . $groupBy[$k]['twitter_type'];
            $gIds = array();
            $gIds = array_splice($groupIds, 0, $groupBy[$k]['count(*)']);
			$result = array();
			if (count($gIds) > 8) {
				$gInfo = array();
				if (count($gIds) > 8) {
						$randKey = array_rand($gIds, 8);
						foreach ($randKey AS $key => $value) {
								$gInfo[] = $gIds[$value];
						}
						$gIds = $gInfo;
				}
			}
			
			$assembleHelper = new AssembleGroup($gIds);
			$groupInfo[$k] = $assembleHelper->assembleMaincatalogSquare($groupInfo[$k], $gIds);
			$mem->set($memKey, $groupInfo[$k], 60 * 10);
        }
		echo "Setting is done!";
        return ;
	
	}

}
