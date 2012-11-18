<?php
namespace Snake\Modules\Msg;
use Snake\Package\Msg\Helper\RedisUserPrivateMsg;
use Snake\Package\User\User;
use Snake\Package\Msg\PrivateMsg;
class Premsg_list extends \Snake\Libs\Controller {

	private  $page_size = 20;
	
	public function run(){
		$user_id = $this->userSession['user_id'];
		if (empty($user_id)) {
			$this->setError(404,400402, "no login in");
			return false;
		}
		$page = $this->request->REQUEST['page'] ? $this->request->REQUEST['page'] : 0;
		$start = $page * $this->page_size;
		$relativeIds = RedisUserPrivateMsg::getConversationTimeline($user_id, $start, $this->page_size);
		if (empty($relativeIds)) return false;
		$msgHelper = new PrivateMsg();
		$getFromInfo = $getToInfo = array();
		//获得接受的私信信息
		$colum = 'max(message_id) as message_id , from_user_id, count(message_id) as num';
		$getFromInfo = $msgHelper->getFromPreMsg($relativeIds, $user_id, $colum, 'from_user_id'); 
		//获得发送的私信信息
		$colum = 'max(message_id) as message_id , to_user_id, count(message_id) as num';
		$getToInfo = $msgHelper->getToPreMsg($relativeIds, $user_id, $colum, 'to_user_id');
		if (empty($getFromInfo) && empty($getToInfo)) return false;
		//获得这些用户的信息
		$colum = array('user_id','nickname', 'avatar_c');
		$userHelper = new User();
		$userInfo = $userHelper->getUserInfos($relativeIds, $colum, 'user_id');
		//去重和数量相加
		$allInfo = $this->assmbleInfo($getFromInfo, $getToInfo, $relativeIds);
		$allInfo = \Snake\Libs\Base\Utilities::changeDataKeys($allInfo, 'message_id');
		$msgIds = \Snake\Libs\Base\Utilities::DataToArray($allInfo, 'message_id');
		//获得私信的信息
		$colum = 'message_id, from_user_id, to_user_id, message_content, message_time';
		$msgInfo = $msgHelper->getMsgInfoByIds($msgIds, $colum, 'message_id');
		$info = $this->assignInfo($allInfo, $msgInfo, $user_id, $userInfo, $userHelper);
		//type为1是我发送的，2是发送给我的
		$totalnum = count($relativeIds);
		if ($totalnum == $this->page_size) {
			 $totalnum = RedisUserPrivateMsg::countConversationTimeline($user_id);
		}
		$this->view = array('list' => $info, 'totalNum' => $totalnum);
	}	
	private function assignInfo($allInfo, $msgInfo, $user_id, $userInfo, $userHelper) {
		foreach($msgInfo as $key=>&$mInfo) {
			$mInfo['num'] = $allInfo[$key]['num'];
			$mInfo['message_time'] = \Snake\Libs\Base\Utilities::timeStrConverter($mInfo['message_time']);
			if ($mInfo['from_user_id'] == $user_id) {
				$mInfo['type'] = 1;	
				$mInfo['avatar'] = $userHelper->picConvertFromAvatarC($userInfo[$mInfo['to_user_id']]['avatar_c'],'b');
				$mInfo['nickname'] = $userInfo[$mInfo['to_user_id']]['nickname'];
			}
			else {
				$mInfo['type'] = 2;	
				$mInfo['avatar'] = $userHelper->picConvertFromAvatarC($userInfo[$mInfo['from_user_id']]['avatar_c'], 'b');
				$mInfo['nickname'] = $userInfo[$mInfo['from_user_id']]['nickname'];
			}
		}	
		return $msgInfo;
	}

	private function assmbleInfo($getFromInfo, $getToInfo, $relativeIds) {
		if (empty($getFromInfo)) return $getToInfo;
		if (empty($getToInfo)) return $getFromInfo;
		foreach($relativeIds as $id) {
			if (!isset($getFromInfo[$id])) $getFromInfo[$id] = array('message_id' => 0,'num' => 0);
			if (!isset($getToInfo[$id]))  $toFromInfo[$id] = array('message_id' => 0, 'num' => 0);
		}
		foreach ($getFromInfo as $user_id => &$fromInfo) {
			$fromInfo['num'] += $getToInfo[$user_id]['num'];
			if ($fromInfo['message_id'] < $getToInfo[$user_id]['message_id']) {
				$fromInfo['message_id'] =  $getToInfo[$user_id]['message_id'];
			}
		}
		return $getFromInfo;
	}
	
	
	
	
	
	
	
}
