<?php
namespace Snake\Package\Msg;
/** 获得私信的相关信息
 *  author gstan
 *  email guoshuaitan@meilishuo.com
 *  version 1.0
 */

use \Snake\Package\Msg\Helper\RedisUserPrivateMsg;
use \Snake\Package\Msg\Alert;
use \Snake\Package\Msg\Helper\DBMsgWalrusHelper;

class PrivateMsg {
	
	private $table = "t_walrus_message_info";
	/**
	 * 发私信操作
	 * 先插入表，然后更新redis,加提醒
	 */
	public function sendPrivateMsg($fromUserId, $toUserId, $msgContent) {
		$back = $this->insertPmsg($fromUserId, $toUserId, $msgContent);			
		//如果插入数据成功，更新用户redis和提醒
		RedisUserPrivateMsg::updateConversationTimeline($fromUserId, $toUserId, $_SERVER['REQUEST_TIME']);
		RedisUserPrivateMsg::updateConversationTimeline($toUserId, $fromUserId, $_SERVER['REQUEST_TIME']);
		$alertHelper = new Alert();
		$alertHelper->setNumByParamAndUid('pmsg_num', $toUserId);
		return $back;
	}
	/** 
     * 219就是系统消息,isSysMsg = 1
     */
    public function insertPmsg($fromUserId, $toUserId, $msgContent, $isSysMsg = 0) {
        if (empty($fromUserId) || empty($toUserId)) {
            return false;
        }   
        $sqlData = array('from_user_id' => $fromUserId,
						 'to_user_id' => $toUserId,
						 'message_content' => $msgContent,
						 'issysmesg' => $isSysMsg,
						 'message_time' => time()
		);
        $sqlComm = "INSERT INTO $this->table( from_user_id, to_user_id, message_content, issysmesg ,message_time ) VALUES ( ".
            " :from_user_id, :to_user_id, :message_content, :issysmesg , :message_time )";
		$back = DBMsgWalrusHelper::getConn()->write($sqlComm, $sqlData);
		return $back;
    }    
	/**
	 * 获得接受到的私信信息
	 * param $from_user_id array 发送私信的人
	 * param $to_user_id 接受私信的人
	 */
	public function getFromPreMsg($from_user_id, $to_user_id, $colum, $hashkey = "", $master = false) {
		if (empty($from_user_id) || empty($to_user_id)) return false;
		$from_user_id = implode(',', $from_user_id);
		$sqlComm = "select $colum from $this->table where from_user_id in($from_user_id) and to_user_id =:to_user_id and issysmesg= 0 and to_show_type = 1 group by from_user_id";
		$sqlData['to_user_id'] = $to_user_id;
		$result = DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData, $master, $hashkey);
		return $result;
	}
	/**
	 * 获得发送的私信信息
	 * param $from_user_id array 发送私信的人
	 * param $to_user_id 接受私信的人
	 */
	public function getToPreMsg($to_user_id, $from_user_id, $colum, $hashkey = "", $master = false) {
		if (empty($from_user_id) || empty($to_user_id)) return false;
		$to_user_id = implode(',', $to_user_id);
		$sqlComm = "select $colum from $this->table where to_user_id in($to_user_id) and from_user_id =:from_user_id and issysmesg = 0 and to_show_type = 1 group by to_user_id";
		$sqlData['from_user_id'] = $from_user_id;
		$result = DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData, $master, $hashkey);
		return $result;
	}
	/**
	 *获得二个人之间所有的会话
	 *param 二个人的user_id
	 */
	 public function getPMsgBoth($usera, $userb, $colum , $start = 0, $limit = 20, $hashkey = "", $master = false) {
		$sqlComm = "select $colum from $this->table where ((from_user_id = :userA and to_user_id = :userB) and from_show_type=1 ) or ((from_user_id=:userB and to_user_id = :userA) and to_show_type=1) order by message_id desc limit $start, $limit";
		$sqlData['userA'] = $usera;
		$sqlData['userB'] = $userb;
		$result = DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData, $master, $hashkey);
		return $result;
	}
	/**
	 *通过message_id数组获得私信信息
	 *param message_ids array
	 */
	public function getMsgInfoByIds($message_ids, $colum, $hashkey = "", $master = false) {
		if (empty($message_ids) && !is_array($message_ids)) return false;
		$ids = implode(',', $message_ids);
		$sqlComm = "select $colum from $this->table where message_id in ($ids) order by message_id desc";
		$sqlData = array();
		$result = DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData, $master, $hashkey);
		return $result;
	}
	//更新某字段的字
	public function updateParamById($message_id, $param) {
		$str = implode(',', $param);
		$sqlComm = "update $this->table set $str where message_id = :_message_id";
		$sqlData['_message_id'] = $message_id;
		$result = DBMsgWalrusHelper::getConn()->write($sqlComm, $sqlData);
		return $result;
	}
	//删除某条消息
	public function deleteOneMsg($message_id, $user_id) {
		if (empty($message_id)) {
			return FALSE;	
		}	
		$messageInfo = $this->getMsgInfoByIds(array($message_id), 'from_user_id,to_user_id');
		$fromId = $messageInfo[0]['from_user_id'];
		$toId = $messageInfo[0]['to_user_id'];
		if ($fromId == $user_id) {
			$back = $this->updateParamById($message_id, array('from_show_type=0'));		
		}
		else if ($toId == $user_id) {
			$back = $this->updateParamById($message_id, array('to_show_type=0'));
		}
		else {
			$back = FALSE;	
		}
		return $back;
	}
	
}
