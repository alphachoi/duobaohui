<?php
namespace Snake\Package\Msg;
/**
 * 外观者,主要复杂给对外调用
 */
use \Snake\Package\Msg\Helper\RedisUserNotification;
use \Snake\Package\Msg\Alert;
use \Snake\Package\Msg\PrivateMsg;
use \Snake\Package\Msg\SystemMsg;

class Msg {
	private $table = "t_walrus_message_info";
	private $stable = "t_walrus_system_message";
	private $utable = "t_walrus_user_misc";
	public function __construct() {
		
	}
	/**添加提醒
	 * $paramStr string fan_num,atme_num,pmsg_num,recommend_num
	 * 需要添加多个提醒时用,分格
	 */
    public function setNumByParamAndUid($paramStr, $userId) {
		$alertHelper = new Alert();
		$back = $alertHelper->setNumByParamAndUid($paramStr, $userId);
		return $back;
    } 
	
	/**
	 * 发私信操作
	 * 先插入表，然后更新redis,加提醒
	 */
	public function sendPrivateMsg($fromUserId, $toUserId, $msgContent) {
		$privateHelper = new PrivateMsg();
		$back = $privateHelper->sendPrivateMsg($fromUserId, $toUserId, $msgContent);
		return $back;
	}
	/**
	 * 发某些人系统消息
	 */
	public function sendSysMsg($user_id, $content) {
		$sysHelper = new SystemMsg();
		$back = $sysHelper->sendSysMsg($user_id, $content);
		return $back;
	}
	/**
	 * 新用户创建timeline
	 */
	public function insertRowByUid($userId) { 
        $result['user_id'] = $userId;
        $result['fans_num'] = 0;
        $result['atme_num'] = 0;
        $result['pmsg_num'] = 0;
        $result['recommend_num'] = 0;
        $result['sysmesg'] = 0;
        RedisUserNotification::hMSet($userId, $result);
        return TRUE;
    }   
	/** 
     * 设置用户提示信息为0
     */    
    public function setZeroByParamAndUid ($paramStr, $userId) { 
        if (empty($paramStr) || empty($userId)) {
            return FALSE;
        }
        if (!RedisUserNotification::hasId($userId)) {
            $this->insertRowByUid($userId);
        }
        $userNotification = RedisUserNotification::getById($userId);
        $userNotification->update($userId, $paramStr, 0); 
        return TRUE;
    } 
}



