<?php
/**
 Groupheader.class.php
 */

namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Manufactory\Poster AS Poster;
use \Snake\package\Group\GroupUser AS GroupUser;
use \Snake\package\Group\GroupTwitters;

class Group_poster extends \Snake\Libs\Controller {
	
	private $groupId = NULL;
	private $frame = NULL;
	private $page = NULL;
	private $superUsers = array(219,1751,1431119,1765,1698845,1590448,1714106,3896618, 7579460,7222759, 6140112,10918214);

	public function run() {
		if (!$this->init()) {
			return FALSE;
		}
		$mem = Memcache::instance();
		$start = FRAME_SIZE_MAX * $this->page * WIDTH_PAGE_SIZE + WIDTH_PAGE_SIZE * $this->frame;
		$groupId = $this->groupId;
		$userId = $this->userSession['user_id'];
		if (!empty($userId)) {
			$groupUserHelper = new GroupUser();
			$user = $groupUserHelper->getGroupRelation(array($groupId), $userId);
		}
		else {
			$cacheKey = "GROUP_POSTER:" . $this->groupId . ":FRAME:" . $start;
			$poster = $mem->get($cacheKey);
			if (!empty($poster)) {
				$this->view = $poster;
				return TRUE;
			}
		}
		$groupHelper = new Groups();
		$groupTwitterHelper = new GroupTwitters();
		$twitterIds = $groupTwitterHelper->getGroupTwitters(array($groupId), array('/*GroupPoster-lhz*/twitter_id', 'group_id', 'elite'), $start, WIDTH_PAGE_SIZE);
		$twitterIds = $twitterIds[$groupId];
		//$twitterIds = $groupTwitterHelper->getGroupTwittersByGroupIdsNoCache(array($groupId), array('twitter_id', 'group_id', 'elite'), $start, WIDTH_PAGE_SIZE);
		$count = $groupTwitterHelper->getGroupsTwitterNumbers(array($groupId));
		$count = $count[$groupId]['num'];
		$tIds = array();
		/*foreach ($twitterIds[$groupId] AS $key => $value) {
			//$tIds[] = $twitterIds[$key]['twitter_id'];
			$tIds[] = $twitterIds[$groupId][$key]['twitter_id'];
		}*/
		$tIds = $twitterIds;
		if (empty($tIds)) {
			$poster['tInfo'] = array();
			$poster['totalNum'] = $count;
			$this->view = $poster;
			return TRUE;
		}
		$posterObj = new Poster();
		$posterObj->isGroup(1);
		$posterObj->isShowPrice(1);
		if ($user[$userId][0]['role'] == 1 || in_array($userId, $this->superUsers)) {
			$posterObj->isAdmin(1);
		}
		$posterObj->isShowComment(1);
		$posterObj->setVariables($tIds, $userId);
		$poster['tInfo'] = $posterObj->getPoster();	
		$poster['totalNum'] = $count;
		$this->view = $poster;
		if (empty($userId) && !empty($poster)) {
			$cacheKey = "GROUP_POSTER:" . $this->groupId . ":FRAME:" . $start;
			$mem->set($cacheKey, $poster, 5 * 60);
		}
		return TRUE;
	}

	private function init() {
		$this->groupId = intval($this->request->REQUEST['group_id']);
		if (empty($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty');
			return FALSE;
		}
		$this->frame = intval($this->request->REQUEST['frame']);
		if ($this->frame < 0 || $this->frame >= FRAME_SIZE_MAX) {
			$this->setError(400, 40302, 'frame is out of range');
			return FALSE;
		}
		$this->page = intval($this->request->REQUEST['page']);
		return TRUE;
	}

	
}
