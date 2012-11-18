<?php
namespace Snake\Modules\Msg;
use Snake\Package\Msg\SystemMsg;
use Snake\Package\User\User;

class Sysmsg_list extends \Snake\Libs\Controller {
	private $page_size = 20;
	private $page = 0;
	public function run(){
		$user_id = $this->userSession['user_id'];
		if (empty($user_id)) {
			$this->setError(404,400402, "no login in");
			return false;
		}
		$this->page = $this->request->REQUEST['page'] ? $this->request->REQUEST['page'] : 0;
		$limit = ($this->page+1) * $this->page_size;
		$start = 0;
		$mHelper = new SystemMsg();
		//更新用户提醒
		$maxId = $mHelper->setSysZero($user_id);
		//得到删除的发给全站系统消息并且更新自己的系统消息提醒数
		$mHelper = new SystemMsg();
		$deleteMsg = $mHelper->getUserMsgInfo('start_message_id, delete_message_id', $user_id);
		$from = $deleteMsg[0]['start_message_id'];
		$delete = $deleteMsg[0]['delete_message_id'];
		$delIds = array();
		if (!empty($delete)){
			$delIds = explode(',', $delete);
		}
		//获得为全站发的系统消息
		$colum = 'message_id, message_content, message_time';
		$sysInfoAll = $sysInfoPre = array();
		$type = 1;
		$sysInfoAll = $mHelper->getSysMsgForAll($colum, $type, $from, $start, $limit);
		//获得为单个人发的系统消息
		$sysInfoPre = $mHelper->getSysMsg($user_id, $colum , 1, $start, $limit);
		//得到总数
		$sysNum = $mHelper->getSysMsg($user_id, 'count(message_id) as num', 2);
		$sysNum = $sysNum[0]['num'];
		$total = $maxId + $sysNum - count($delIds) - $from;
		$info = array();
		if(empty($sysInfoAll)) {
			$info = $sysInfoPre;
		}
		else {
			$info = $this->assmbleInfo($sysInfoAll, $delIds, $sysInfoPre);	
		}
		$this->view = array('list'=>$info, 'totalnum'=>$total, 'nickname'=>$this->userSession['nickname']);
	}

	private function assmbleInfo($sysInfoAll, $delIds, $sysInfoPre) {
		$info = array();
		foreach($sysInfoAll as $key=> &$sysInfo) {
			if(in_array($sysInfo['message_id'], $delIds)) {
					unset($sysInfoAll[$key]);		
			}	
			$sysInfo['esp'] = 1;
		}	
		$info = array_merge($sysInfoAll,$sysInfoPre);
		// 按时间排序
		usort($info,function($a, $b){
			if ($a['message_time'] == $b['message_time']) {
				return 0;    
			}
			return ($a['message_time'] > $b['message_time']) ? -1 : 1;
		});
		$info = array_slice($info, $this->page*$this->page_size, $this->page_size);
		return $info;
	}
}
