<?php
/**
 Groupheader.class.php
 */

namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\Groups AS Groups;

class Group_header extends \Snake\Libs\Controller {

	private $superUsers = array(219,1751,1431119,1765,1698845,1590448,1714106,3896618, 7579460,7222759, 6140112, 10918214);
	
	public function run() {
		if (!$this->init()) {
			return FALSE;
		}
		if (empty($this->userSession['user_id'])) {
			$mem = Memcache::instance();
			$cacheKey = "GROUP_HEADER_CACHE:" . $this->groupId;
			$groupInfo = $mem->get($cacheKey);
			$groupIfo = array();
			if (!empty($groupInfo)) {
				if ($this->groupId == 16169578) {
					$groupInfo['group_info']['description'] = htmlspecialchars_decode($groupInfo['group_info']['description']);
				}
				$this->view = $groupInfo;
				return TRUE;
			}
		}
		$groupHelper = new Groups();
		$groupInfo = $groupHelper->getGroupHeader($this->groupId, $this->userSession['user_id']);
		if ($this->groupId == 16169578) {
			$groupInfo['group_info']['description'] = htmlspecialchars_decode($groupInfo['group_info']['description']);
		}
		if (empty($this->userSession['user_id'])) {
			$mem->set($cacheKey, $groupInfo, 3600);
		}
		$this->view = $groupInfo;
		return TRUE;
	}

	public function init() {
		$this->groupId = intval($this->request->REQUEST['group_id']);
		if (empty($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty');
			return FALSE;
		}
		return TRUE;
	}
}
