<?php
namespace Snake\Modules\About;

USE \Snake\Package\About\Feedback AS FeedbackModel;
USE \Snake\Package\User\User      AS User;

class FeedBack_list extends \Snake\Libs\Controller {

	private $userId = 0;
	private $feedId = 0;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$feedBack = new FeedbackModel();
		$fields = array('feedback_id', 'user_id', 'feedback_detail', 'time', 'flag');
		$feedbackListInfo = $feedBack->selectFeedback($this->feedId, $fields);
		$userIds = \Snake\Libs\Base\Utilities::DataToArray($feedbackListInfo, 'user_id'); 
		$userHelper = new User();
		$userFields = array('user_id', 'nickname');
		$userExtFields = array('user_id', 'avatar_c');
		$userBaseInfo = $userHelper->getUserBaseInfos($userIds, $userFields, TRUE);
		$userExtInfo = $userHelper->getUserExtInfos($userIds, $userExtFields, TRUE);
		$mergeInfo = $this->mergeInfo($feedbackListInfo, $userBaseInfo, $userExtInfo);
		$this->view = $mergeInfo;
	}

	private function mergeInfo($listInfo, $baseInfo, $extInfo) {
		if (empty($listInfo) || empty($baseInfo) || empty($extInfo)) {
			return FALSE;
		}
		if (!empty($listInfo)) {
			foreach ($listInfo as $key => $info) {
				$uid = $info['user_id'];
				$listInfo[$key]['nickname'] = $baseInfo[$uid]['nickname'];
				$listInfo[$key]['avatar'] = \Snake\Libs\Base\Utilities::convertPicture($extInfo[$uid]['avatar_c']);
			}
		}
		return $listInfo; 
	}

	private function _init() {
		$this->userId = $this->userSession['user_id'];
		if (empty($this->userId)) {
			$this->setError(400, 40150, 'empty user_id');
			return FALSE;
		}	
		$this->feedId = $this->request->REQUEST['feed_id'];
		if (empty($this->feedId)) {
			$this->setError(400, 40151, 'empty feed_id');
			return FALSE;
		}
		$feedBack = new FeedbackModel();
		$feedBackInfo = $feedBack->selectFeedbackInfo($this->feedId, array('user_id'));
		if ($this->userId != $feedBackInfo[0]['user_id']) {
			$this->setError(400, 40152, 'permission deny');
			return FALSE;
		}
		return TRUE;
	}
}
