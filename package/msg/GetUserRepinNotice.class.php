<?php
namespace Snake\Package\Msg;

use \Snake\Package\Msg\Helper\RedisUserNotification AS UserNotification;
Use \Snake\Package\Msg\Helper\RedisUserRepinNotice AS UserRepinNotice;
use \Snake\libs\Cache\Memcache AS Memcache;

class GetUserRepinNotice {
	private $userId = NULL;
	private $msgInfo = array();

	public function __construct ($user_id) {
		$this->userId = $user_id;
	}

	public function getRepinNotice () {
		$userId = $this->userId;
		$notices = UserRepinNotice::lRange($userId, 0, 10);
		$noticesArray = array();
		foreach($notices as $key => $notice) {
			$noticesArray[$key] = json_decode($notice, TRUE);
		}
		$msgInfo = $this->dealMsgInfo($noticesArray);
	
		$this->msgInfo = $msgInfo;	
	}

	public function getMsgInfo() {
		return $this->msgInfo;
	}

	public function dealMsgInfo($msgInfo) {
        foreach($msgInfo as &$msg) {
            $timeGap = $currentTime - $msg['time'];
            $msg['time_msg'] = $this->getTimeGapMsg($timeGap);
            $msg['person_url'] = 'person/u/' . $msg['from'];
            switch($msg['type']) {
                case 'like':
                    $msg['url'] = 'share/' . $msg['tid'];    
                    break;
                case 'collect':
                case 'followgroup':
                    $msg['goodsUrl'] = 'share/' . $msg['tid'];
                    $msg['url'] = 'group/' . $msg['gid'];
                    break;
                case 'follow':
                    $msg['url'] = 'person/u/' . $msg['from'];
                    break;
                default:
                    break;
            }    
        }
		return $msgInfo;
	}
	
	public function getTimeGapMsg($timeGap) {

        if ($timeGap <= 60) {
            $timeMsg = '1分钟内';
        }
        elseif ($timeGap > 60 && $timeGap <= 120) {
            $timeMsg = '1分钟前';
        }
        elseif ($timeGap > 120 && $timeGap <= 180) {
            $timeMsg = '2分钟前';
        }   
        elseif ($timeGap > 180 && $timeGap <= 600) {
            $timeMsg = '3分钟前';
        }   
        elseif ($timeGap > 600 && $timeGap <= 1800) {
            $timeMsg = '10分钟前';
        }   
        elseif ($timeGap > 1800 && $timeGap <= 3600) {
            $timeMsg = '30分钟前';
        }   
        elseif ($timeGap > 3600 && $timeGap <= 3 * 3600) {
            $timeMsg = '1小时前';
        }   
        elseif ($timeGap > 3 * 3600 && $timeGap <= 6 * 3600) {
            $timeMsg = '3小时前';
        }   
        elseif ($timeGap > 6 * 3600 && $timeGap <= 12 * 3600) {
            $timeMsg = '6小时前';
        }
        elseif ($timeGap > 12 * 3600 && $timeGap <= 24 * 3600) {
            $timeMsg = '12小时前';
        }
        elseif ($timeGap > 24 * 3600 && $timeGap <= 48 * 3600) {
            $timeMsg = '1天前';
        }   
        elseif ($timeGap > 48 * 3600 && $timeGap <= 72 * 3600) {
            $timeMsg = '2天前';
        }   
        else {
            $timeMsg = '3天前';
        }
        return $timeMsg;
	}
		
}
