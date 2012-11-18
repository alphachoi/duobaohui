<?php
/**
 Groupcatalog.class.php
 */

namespace Snake\Modules\Group;

use Snake\Package\Group\GroupCatalog AS Gcatalog;
use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\User\UserFollowGroup;
use Snake\Package\Group\GroupUser;
use Snake\Package\Group\Groups;

class Groupcatalog extends \Snake\Libs\Controller {

    protected $catalogId;
    protected $pageSize;
    protected $pageNumber;
	public function run() {
		$this->pageSize = 40;
        if (isset($this->request->REQUEST['tab']) && is_numeric($this->request->REQUEST['tab']) ) {
            $this->catalogId = intval($this->request->REQUEST['tab']); 
        }
        if (isset($this->request->REQUEST['page_size']) && is_numeric($this->request->REQUEST['page_size'])) {
            $this->pageSize = intval($this->request->REQUEST['page_size']);
        }
        if (isset($this->request->REQUEST['page']) && is_numeric($this->request->REQUEST['page'])) {
            $this->pageNumber = intval($this->request->REQUEST['page']);
        }

        if (empty($this->catalogId)) {
			$this->head = 400;
			$this->view = array(
				'code'	  => 400,
				'message' => 'empty catalog_id',
			);
			return;
        }
        if (empty($this->pageNumber)) {
            $this->pageNumber = 0;
        }
        $this->main();
    }

    private function main() {
        $groupCatalogHelper = new Gcatalog($this->catalogId);
        $groupCatalogHelper->getGroupIdsByCatalogId($this->pageNumber * $this->pageSize, $this->pageSize); 
        $gIds = $groupCatalogHelper->getGroupIds();
		$mem = Memcache::instance();
		$memKey = "GroupCatalog:" . $this->catalogId . "_" . $this->pageNumber;
		$groupInfos = $mem->get($memKey);
		
		if (empty($groupInfos)) {
			foreach ($gIds AS $key => $value) {
				$groupIds[] = $gIds[$key]['id'];
			}
			$squareHandle = new Groups();
			$groupInfoObj = $squareHandle->getGroupSquareInfo($groupIds, 0);
			$groupInfo['group_info'] = $groupInfoObj;

			if (empty($groupInfo['group_info'])) {
				$this->view = array();
				return TRUE;
			}

			$groupInfo['group_info'] = array_slice($groupInfo['group_info'], 0, 40);
			$groupCatalogHelper->getCatalogPage($this->pageNumber);
			$gInfo = $groupCatalogHelper->getPage($this->pageSize);
			$groupInfos = array();
			foreach ($groupInfo['group_info'] AS $key => $value) {
				$groupInfos['group_info'][] = $groupInfo['group_info'][$key];
			}
			$groupInfos['page_info'] = $gInfo;
			$logHelper = new \Snake\Libs\Base\SnakeLog('hz_groups', 'normal');
			if (empty($groupInfos['group_info'][0]['mixpic'])) {
				$logHelper->w_log(print_r(array($groupInfos, $this->pageNumber), TRUE));
			}
			if (!empty($groupInfos['group_info'][0]['mixpic'])) {
				$mem->set($memKey, $groupInfos, 60*10);
			}
		}
		if (!empty($this->userSession['user_id']) && !empty($groupInfos['group_info'])) {
			//use relation
			$groupInfos = $this->fillRelation($groupInfos, $this->userSession['user_id']);
			//print_r($groupInfo[$k]);
		}

		$this->view = $groupInfos;
		return;
    }

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
