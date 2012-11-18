<?php
namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\GroupMainCatalog AS GMainCatalog;
use \Snake\Package\User\UserFollowGroup;
use \Snake\Package\Group\AssembleGroup AS AssembleGroup; 
use \Snake\Package\Group\GroupUser AS GroupUser;

class Groupmaincatalog extends \Snake\Libs\Controller {

	
	public function run() {
		$this->main();
	}
	

	public function main() {
		//分类杂志社 -start
		$gIds = array();
		$groupInfo = array();
		$mem = Memcache::instance();
		$cacheKey = "snake:group:maincatalog:catalogInfo";
		$mainInfo = $mem->get($cacheKey);
		if (empty($mainInfo)) {
			$groupHelper = new GMainCatalog();
			$groupHelper->getMainCatalogInfo();
			$groupBy = $groupHelper->getGroupBy();
			$groupIds = $groupHelper->getGroupIds();
			$mainInfo['group_by'] = $groupBy;
			$mainInfo['group_ids'] = $groupIds; 
			$mem->set($cacheKey, $mainInfo, 60*5);
		}
		else {
			$groupBy = $mainInfo['group_by'];
			$groupIds = $mainInfo['group_ids'];
		}
		$gInfo = array();
		foreach ($groupBy AS $k => $v) {
			$memKey = "snake:group:maincatalog:" . $groupBy[$k]['twitter_type'];
			$result = $mem->get($memKey);
			$gIds = array();
			$gIds = array_splice($groupIds, 0, $groupBy[$k]['count(*)']);
			if (!empty($result)) {
				$groupInfo[$k] = $result;
			}
			else {
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
				$mem->set($memKey, $groupInfo[$k], 60 * 5);
			}
			if (!empty($this->userSession['user_id'])) {
				//use relation
				$groupInfo[$k] = $this->fillRelation($groupInfo[$k], $this->userSession['user_id']);
			}
		}
		$this->view = $groupInfo;
		return;
		//分类杂志社 -end
	}

	private function assembleGroup($groupInfo, $gIds){
		$groupInfo['class_name'] = $gIds[0]['class_name'];
		$groupInfo['link'] = $gIds[0]['class_id'];
		if ($gIds[0]['class_name'] == '家居' || $gIds[0]['class_name'] == '萌物') {
			$groupInfo['link'] = 88;
		}
		elseif ($gIds[0]['class_name'] == '韩范') {
			$groupInfo['link'] = 11;
		}
		elseif ($gIds[0]['class_id'] > 20) {
			$groupInfo['link'] = 88;
		}
		$gId = array();
		foreach ($gIds AS $key => $value) {
			$gId[$key]['id'] = $gIds[$key]['data_id'];
			$gId[$key]['type'] = 'group';
		}
		$squareHandle = new GroupSquare(10, $groupInfo['link'], $gIds[0]['class_name'], $gId);
		$groupInfoObj = $squareHandle->getSquareInfos();
		foreach ($groupInfoObj AS $gInfoObj) {
			$groupInfo['group_info'][] = $gInfoObj->getGroup();
		}
		return $groupInfo;
	}

	/*
	private function fillRelation($groupInfos, $userId) {
        $groupUserHelper = new GroupUser();
        foreach ($groupInfos['group_info'] AS $key => $value) {
            $isFollower = $groupUserHelper->isGroupFollower($groupInfos['group_info'][$key]['group_id'], $userId);
            if ($isFollower == TRUE) {
                $groupInfos['group_info'][$key]['is_follower'] = 1;
            }
        }
        return $groupInfos;
	}
	*/

    private function fillRelation($groupInfos, $userId) {
        $groupUserHelper = new GroupUser();
        foreach ($groupInfos['group_info'] AS $key => $value) {
            $gids[] = $groupInfos['group_info'][$key]['group_id'];
        }   
        $relations = $groupUserHelper->isGroupFollowerMulti($gids, $userId);
        foreach ($groupInfos['group_info'] AS $key => $value) {
            $gid = $groupInfos['group_info'][$key]['group_id'];
            $groupInfos['group_info'][$key]['is_follower'] = $relations[$gid];
        }   
        return $groupInfos;
    }

}
