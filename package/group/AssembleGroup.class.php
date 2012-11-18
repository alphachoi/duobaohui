<?php
namespace Snake\Package\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\GroupMainCatalog AS GMainCatalog;
use \Snake\Package\User\UserFollowGroup;

class AssembleGroup {

	protected $groups = array();
	protected $group_ids = array();

	public function __construct($group_ids) {
		if (!empty($group_ids)) {
			$this->group_ids = $group_ids;
		}
	}

	public function getGroups() {
		return $this->groups;
	}

	public function assembleMaincatalogSquare($groupInfo, $gIds) {
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
		$ids = \Snake\Libs\Base\Utilities::DataToArray($gIds, 'data_id');
		$groupHelper = new Groups();
		$result = $groupHelper->getGroupSquareInfo($ids, 0);
		$groupInfo['group_info'] = array_values($result);
		$this->groups = $groupInfo;
        return $this->groups;
	}

}

