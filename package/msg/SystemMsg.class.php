<?php
namespace Snake\Package\Msg;
/** 有关系统消息的操作
 * 系统消息分2类,全站的系统消息，和对某人发的系统消息
 * author gstan
 * email guoshuaitan@meilishuo.com
 * version 1.0
 */
use \Snake\Package\Msg\Helper\DBMsgWalrusHelper;
use \Snake\libs\Cache\Memcache;
use \Snake\Package\Msg\PrivateMsg;
use \Snake\Package\Msg\Alert;

class SystemMsg {
	
	private $table = 't_walrus_message_info';
	private $utable = 't_walrus_user_misc';
	private $stable = 't_walrus_system_message';
	/** 
	 * 给单独某些人发系统消息
	 */
	public function sendSysMsg($user_id, $content) {
		$msgHelper = new  PrivateMsg();
		$back = $msgHelper->insertPmsg(219, $user_id , $content, 1);
		$alertHelper = new Alert();
		$alertHelper->setNumByParamAndUid('sysmesg', $user_id);
		return $back;
	}
	/**
	 *插入一条全站的系统消息
	 */
	public function insertSysMsgForAll($content,$sender) {
		$cache = Memcache::instance();
		$maxId =  $this->getMaxSysId($cache);
		$id = $maxId + 1;
		$time = time();
		$sqlComm = "insert into $this->stable (message_id, sender, message_content, message_time,status) values (:_message_id, :_sender, :_message_content, :_time, :_status)";
		$sqlData = array('_message_id' => $id,
						 '_sender'     => $sender,
						 '_message_content' => $content,
						 '_time' => $time,
						 '_status' => 1
						);
		$result = DBMsgWalrusHelper::getConn()->write($sqlComm, $sqlData);
		if ($result) {
			$cache->set('MAX_SYS_ID', $id, 2*3600);	
		}
		return $result;
	}
	
	/**
	 *获得最大系统消息id
	 */
	public function getMaxSysId($cache) {
		if (!is_object($cache)) {
			return  $cache = Memcache::instance();	
		}
		$maxId = $cache->get('MAX_SYS_ID');
		if (empty($maxId)) {
			$maxSysMsgId = $this->getSysMsgForAll('max(message_id) as id', 2);
			$maxId = $maxSysMsgId[0]['id'];
			$cache->set('MAX_SYS_ID', $maxId, 2*3600);
		}
		return $maxId;
	}

	/**
	 *是系统消息提醒置零,$type=1为注册时使用，$type=2 为老用户
	 */
	public function setSysZero ($user_id, $type = 2) {
		$cache =  Memcache::instance();
		$maxId = $this->getMaxSysId($cache);
		//插入或者更新用户最大看过的id
		if ($type == 2) {
			$this->updateUserSysId($user_id, $maxId);
		}
		else {
			$this->insertUserSysId($user_id, $maxId);	
		}
		$cache->set('SYS_MSG_NUM' .$user_id, 0, 2*3600);
		return $maxId;
	}

	/**
	 * 更新用户最大看过的id(仅适合老用户)
	 */
	public function updateUserSysId($user_id, $maxId) {
		$sqlComm = "update $this->utable set last_message_id =:last_message_id where user_id =:user_id";
		$sqlData['last_message_id'] = $maxId;
		$sqlData['user_id'] = $user_id;
		$result = DBMsgWalrusHelper::getConn()->write($sqlComm, $sqlData);
		if (!$result) {
			//如果没有用户数据，则插入一条用户数据
			$sqlComm = "insert into $this->utable (user_id, last_message_id) values (:user_id, :last_message_id)";
			$data['user_id'] = $user_id;
			$data['last_message_id'] = $maxId;
			$result = DBMsgWalrusHelper::getConn()->write($sqlComm, $data);
		}
	}
	
	/** 
	 *插入一条用户查看过的系统消息(试用于注册时使用)
	 */
	public function insertUserSysId($user_id, $maxId) {
		$sqlComm = "insert into $this->utable (user_id, start_message_id, last_message_id) values (:user_id, :start_message_id, :last_message_id)";
		$sqlData = array('user_id' => $user_id,
						 'start_message_id' => $maxId,
						 'last_message_id'  => $maxId
		);
		DBMsgWalrusHelper::getConn()->write($sqlComm, $sqlData);
	}
	/**
	 *获得全站的系统消息
	 */
	public function getSysMsgForAll($colum , $type = 1, $message_id = "", $start = 0, $limit = 20) {
		$sqlComm = "select $colum from $this->stable where status = 1";
		if (!empty($message_id)) {
			$sqlComm .= " and message_id > $message_id ";	
		}
		if ($type == 1) {
			$sqlComm .= " order by message_id desc limit $start, $limit ";
		}
		$sqlData = array();
		$result =  DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData);
		return $result;	 
	}
	/** 
	 *获得小精灵为用户发的系统消息
	 */
	public function getSysMsg($user_id, $colum, $type = 1, $start = 0, $limit = 20) {
		$sqlComm = "select $colum from $this->table where to_user_id = :user_id and issysmesg = 1 and to_show_type =1";	 
		if ($type == 1 ) {
			$sqlComm .= " order by message_id desc limit $start, $limit";
		}
		$sqlData['user_id'] = $user_id;
		$result =  DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	} 
    /**
     *获得用户读过的系统消息最大id
     */
	public function getUserMsgInfo($selComm='*', $user_id) {
		if (empty($user_id)) {
			return false;
		}
		$sqlComm = "SELECT $selComm FROM $this->utable WHERE user_id = :_user_id";
		$sqlData['_user_id'] = $user_id;
		$result = DBMsgWalrusHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
    }
	//删除某个人全站的系统消息
	public function deleteTotalMsg($message_id, $user_id) {
		if (empty($message_id) || empty($user_id)) {
			return FALSE;	
		}
		$sqlComm = "update	$this->utable set delete_message_id = concat_ws( ',' , $message_id, delete_message_id) where user_id = :_user_id";
		$sqlData['_user_id'] = $user_id;
		$result = DBMsgWalrusHelper::getConn()->write($sqlComm, $sqlData);
		return $result;
	}
	
}


