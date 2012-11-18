<?php
namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\NewComingGroup AS NewComingGroup;
use \Snake\Package\User\UserFollowGroup;

class Newcoming extends \Snake\Libs\Controller {

	public function run() {
		$this->main();
	}

	public function main() {
		//新晋杂志社 -start
		$groupInfo = array();
		$gIds = array();
		$mem = Memcache::instance();
		$memKey = "snake:group:newcoming";
		$newComingGroup = $mem->get($memKey);
		$gids = array();
		if (empty($newComingGroup)) {
			$groupHelper = new NewComingGroup();
			$groupHelper->getNewComingInfo();
			$gIds = $groupHelper->getGroupIds();
			if (empty($gIds)) {
				return FALSE;
			}
			$squareHandle = new GroupSquare(1, 1, '新晋杂志社', $gIds);
			$groupInfoObj = $squareHandle->getSquareInfos();
			$newComingGroup = array();
			foreach ($groupInfoObj AS $k) {
				$newComingGroup[] = $k->getGroup();
			}
			$mem->set($memKey, $newComingGroup, 60*5);
		}
        else {
            foreach ($newComingGroup AS $key => $value) {
                $gids[] = $newComingGroup[$key]['group_id'];
            }
        }
		if (!empty($this->userSession['user_id'])) {
			if (empty($gids)) {
				foreach ($gIds AS $key => $value) {
					$gids[] = $gIds[$key]['id'];
				}
			}
			$UserFollowGroup = new UserFollowGroup($this->userSession['user_id'], $gids);
			$userFollowGroups = array();
			$userFollowGroups = $UserFollowGroup->getRelation();

			foreach ($newComingGroup AS $key => $value) {
				$group_info = $newComingGroup[$key];
				if (isset($userFollowGroups[$newComingGroup[$key]['group_id']]['role']) && $userFollowGroups[$newComingGroup[$key]['group_id']]['role'] != 8) {
					$group_info['is_follower'] = 1;
				}
				$newComingGroup[$key] = $group_info;
			}
		}
		$newComingGroup = array_slice($newComingGroup, 0, 6);
		$this->view = $newComingGroup;
		return ;
		//新晋杂志社 -end
	}

}
