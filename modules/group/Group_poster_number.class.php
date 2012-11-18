<?php
/**
 Groupheader.class.php
 */

namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupTwitters AS GroupTwitters;
use \Snake\Package\Manufactory\Poster AS Poster;
use \Snake\package\Group\GroupUser AS GroupUser;

class Group_poster_number extends \Snake\Libs\Controller {

	private $groupId = NULL;

	public function run() {
		if (!$this->init()) {
			return FALSE;
		}
		$groupId = $this->groupId;
		$groupHelper = new GroupTwitters();
		$count = $groupHelper->getGroupsTwitterNumbers(array($groupId));
		$count = $count[$groupId]['num'];
		$number['totalNum'] = $count;
		$this->view = $number;
		return TRUE;
	}

	private function init() {
		$this->groupId = intval($this->request->REQUEST['group_id']);
		if (empty($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty');
			return FALSE;
		}
		return TRUE;
	}
}
	
