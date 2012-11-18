<?php
/**
 ChangeRelation.class.php
 */

namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupFactory AS GroupFactory;
use \Snake\Package\Relation\UserRelationGroup AS UserRelationGroup;
use \Snake\Package\Group\ChangeGroupRelation AS ChangeGroupRelation;
use \Snake\Package\Group\ClearGroupCache AS ClearGroupCache;
use \Snake\Package\Group\UserFollowGroupRedis AS UserFollowGroupRedis;
use \Snake\Package\Msg\UpdateUserNotice AS UpdateUserNotice;
use \Snake\Package\Timeline\Timeline AS Timeline;
use	\Snake\Package\ShareOutside\ShareOb AS ShareOb;
use \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnectHelper;

class Changerelation extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable{
	
	private $userId = NULL;
	private $groupId = NULL;
	private $changeRole = NULL;
	private $operationUserId = NULL;
	private $observer = array();
	private $superUser = array(219,1751,1431119,1765,1698845,1590448,1614179,1714106,1110628,3896618, 7579460);

	public function run() {
		if (isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id'])) {
			$this->userId = intval($this->request->REQUEST['user_id']);
			$this->operationUserId = intval($this->request->REQUEST['user_id']);
		}
		if (isset($this->request->REQUEST['group_id']) && is_numeric($this->request->REQUEST['group_id'])) {
			$this->groupId = intval($this->request->REQUEST['group_id']);
		}
		if (isset($this->request->REQUEST['o_user_id']) && is_numeric($this->request->REQUEST['o_user_id'])) {
			$this->operationUserId = intval($this->request->REQUEST['o_user_id']);
		}
		$this->changeRole = 5;
		if (isset($this->request->REQUEST['role']) && is_numeric($this->request->REQUEST['role'])) {
			$this->changeRole = intval($this->request->REQUEST['role']);
		}
        if (empty($this->userId)) {
            $this->head = 400;
            $this->view = array(
                'code'    => 400,
                'message' => 'empty user_id',
            );
            return;
        }
        if (empty($this->groupId)) {
            $this->head = 400;
            $this->view = array(
                'code'    => 400,
                'message' => 'empty group_id',
            );
            return;
        }
		$this->main();

	}

	public function main() {
			$changeHelper = new ChangeGroupRelation($this->userId, $this->operationUserId, $this->groupId, $this->changeRole);
			$changeHelper->runBeta();
			exit;
		$relationHelper = new UserRelationGroup($this->userId, array(0 => $this->groupId));
		$userReltion = $relationHelper->getRelation();

		if ($this->userId == $this->operationUserId && $userReltion[$this->groupId]['role'] != 8) {
			$this->changeRole = 5;
			$changeHelper = new ChangeGroupRelation($this->userId, $this->operationUserId, $this->groupId, $this->changeRole);
			$changeHelper->addObserver($relationHelper);
			$changeHelper->addObserver(new ClearGroupCache());
			$changeHelper->addObserver(new UserFollowGroupRedis());
			$changeHelper->addObserver(new UpdateUserNotice());
			$changeHelper->addObserver(new Timeline());
			$settings = RedisUserConnectHelper::getUserSetting('qplus', $this->userId);
			$result = json_decode($settings, TRUE);
			if ( $result['sync_answer'] == 1 ) {
				$flag = 'follow';
				$changeHelper->addObserver(new ShareOb());
			//	$this->_qplusSync($flag, $this->group['group_id']);
			}	
			$changeHelper->runObserver();
			//$this->setRole($relationHelper);
			$this->view = array(
				'code'	=> 200,
				'message' => 'follow done!'
			);
			return ;
		}
		else {
			if (in_array($this->operationUserId, $this->superAdmins)) {
				$this->setRole($relationHelper);
				$this->view = array(
					'code'	=> 200,
					'message' => 'change done!'
				);
				return ;
			}
			$oRelationHelper = new UserRelationGroup($this->operationUserId, array(0 => $this->groupId));
			$operationUserRelation = $oRelationHelper->getRelation();
			if ($operationUserRelation[$this->groupId]['role'] == 1) {
				$this->setRole($relationHelper);
				$this->view = array(
					'code'	=> 200,
					'message' => 'change done!'
				);
				return ;
			}
			$this->view = array(
				'code'	=> 300,
				'message' => 'permission denied!'
			);
			return ;
		}

	}

	public function setRole($relationHelper) {
		switch ($this->changeRole) {
			case 0: 
				$relationHelper->add();
				break;

			case 1: 
				$relationHelper->create();
				break;

			case 4: 
				$relationHelper->apply();
				break;

			case 5: 
				$relationHelper->onChanged($this->userId, $this->groupId, $this->changeRole);
				break;

			case 8: 
				$relationHelper->block();
				break;

			default :
				$relationHelper->follow();
				break;
		}
		return ;
	}

	public function addObserver($observer) {
		$this->observer[] = $observer;
	}

	public function runObserver() {
		foreach ($this->observer AS $observer) {
			$observer->onChanged($this->userId, $this->groupId, $this->changeRole);
		}
	}

}
