<?php
namespace Snake\Modules\Home;

USE \Snake\Package\Timeline\Timeline   AS Timeline;
USE \Snake\Package\Timeline\TimelineDB AS TimelineDB;

/**
 * 修复用户的timeline和关注杂志社,四天不登录会自动删除用户
 * 的timeline，把删除的tid记入到数据库t_seal_user_twitter，
 * 在没有timeline的这段时间内，关注的人创建的杂志社不会更新
 * 你的关注列表。
 *
 * timeline恢复过程为：
 * 1,从数据库获取主编和编辑的杂志社信息,遍历杂志社的outbox,取出
 * 合适的tid。
 * 2,如果从杂志社取出的tid总数大于timeline的SIZE,跳入第四步。
 * 3,获取从删除用户timline的那个时间点到目前时间期间内关注人的喜欢tid(生成的新推)。
 * 4,排序，去重后取出最新的SIZE条记录写入到timeline
 * 
 */
class Home_rebuild extends \Snake\Libs\Controller {
	
	private $userId = NULL;

	const REBUILD_SIZE = 50;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->rebuildTimeline();
		return TRUE;
	}

	private function _init() {
		if (!$this->setUserId()) {
			$this->setError(400, 20150, 'empty user_id');
			return FALSE;
		}
		return TRUE;
	}

    private function rebuildTimeline() {
        if (UserHomePosterTimeline::exists($this->userId) == FALSE || UserHomePosterTimeline::getSize($this->userId) < self::REBUILD_SIZE) {
            $result = TimelineDB::getInstance()->getTimelineFromDB($this->userId, "/*Home-rebuild gc*/ last_tid, last_update_time, tids");
            $lastTid = 0;
            $dbTids = array();
            $lastUpdateTime = 0;
            if (!empty($result)) {
                $lastTid = $result[0]['last_tid'];
                $lastUpdateTime = strtotime($result[0]['last_update_time']);
                $dbTids = explode(',', $result[0]['tids']);
            }   
            Timeline::rebuildUserHomePosterTimelineNew($this->userId, $lastTid, $lastUpdateTime, $dbTids);
        }   
    } 

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		if (empty($this->userId)) {
			return FALSE;
		}
		return TRUE;
	}
}
