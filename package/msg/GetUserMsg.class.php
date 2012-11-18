<?php
namespace Snake\Package\Msg;

use \Snake\Package\Msg\Helper\RedisUserNotification AS UserNotification;
Use \Snake\Package\Msg\Helper\DBMsgHelper AS DBMsgHelper;
use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Msg\Helper\DBMsgWalrusHelper;
use \Snake\Package\Msg\SystemMsg;
class GetUserMsg {
	
	private $userId = NULL;
	private $msgInfo = array();

	public function __construct ($user_id) {
		$this->userId = $user_id;
	}

	public function getInfoByUid() {
		$userId = $this->userId;
		$data = UserNotification::hGetAll($userId);
        if (!empty($data)) {
			$sysHelper = new SystemMsg();
			$cache = Memcache::instance();
            $num = $cache->get('SYS_MSG_NUM'.$userId);
            if ($num === false) { 
				$maxId = $sysHelper->getMaxSysId($cache);
                $userMsgId = $sysHelper->getUserMsgInfo('last_message_id', $userId);
                if (!empty($userMsgId)) {
                    $num = intval(intval($maxId) - intval($userMsgId[0]['last_message_id']));
					if ($num < 0) {
						$num = 0;
					}
                    $data['sysmesg'] += $num;
					$cache->set('SYS_MSG_NUM'.$userId, $num , 2*3600);
                }   
            }   
            else if ($num != 0){
                $data['sysmesg'] = $num;
            }   
            $this->msgInfo = $data;
			return ;
        }   

        $result['user_id'] = $userId;
        $result['fans_num'] = 0;
        $result['atme_num'] = 0;
        $result['pmsg_num'] = 0;
        $result['recommend_num'] = 0;
        $result['sysmesg'] = 0;

        $this->msgInfo =  $result;
		return ;
	}

	public function getMsgInfo() {
		return $this->msgInfo;
	}


}
