<?php
namespace Snake\Package\Home;

Use \Snake\Package\Msg\Helper\RedisUserRepinNotice AS UserRepinNotice;

class HomeRepin {
	
	private $_repinNotice = array();
	private $_userId = 0;
	private $_oldTime = 0;

	public function __construct($userId, $oldTime = 0) {
		$this->_userId = $userId;
		$this->_oldTime = $oldTime;
	}

	public function getRepinNotice() {
		if (empty($this->_oldTime)) {
			$this->setInitRepinNotice();
		}
		else {
			$this->setRepinNotice();
		}
		return $this->_repinNotice;
	}

	/**
	 * 
	 */
	private function setRepinNotice() {
		$newMsgInfo = array();
        $msgInfo = UserRepinNotice::getNotice($this->_userId);
        $currentTime = time();
        foreach ($msgInfo as $msg) {
			if (empty($msg)) {
				continue;
			}
            $timeGap = $currentTime - $msg['time'];
			$pos = mb_strpos($msg['nickname'], '#', 0, 'utf-8');
			if ($pos !== FALSE) {
				$msg['nickname'] = mb_substr($msg['nickname'], 0, $pos, 'utf-8');
			}
            $msg['time_msg'] = $this->_getTimeGapMsg($timeGap);
            $msg['person_url'] = '/person/u/' . $msg['from'];
			if ($msg['time'] > $this->_oldTime) {
				switch ($msg['type']) {
					case 'like' :
						$msg['url'] = '/share/' . $msg['tid'];
						break;
					case 'collect' :
					case 'followgroup' :
						$msg['goodsUrl'] = '/share/' . $msg['tid'];
						$msg['url'] = '/group/' . $msg['gid'];
						break;
					case 'follow' :
						$msg['url'] = '/person/u/' . $msg['from'];
						break;
					case 'medal' :
						$msg['url'] = '/medal/detail/' . $msg['medal_id'];
						break;
					case 'travel' :
						$msg['url'] = '/huodong/vacation';
						break;
					default :
						break;
				}
				$newMsgInfo[] = $msg;
			}
			else {
				break;
			}
        }
        $this->_repinNotice = $newMsgInfo;
    }

	private function setInitRepinNotice() {
		$msgInfo = UserRepinNotice::getNotice($this->_userId);
		$newMsgInfo = array();
		$currentTime = time();
		foreach ($msgInfo as $msg) {
			if (empty($msg)) {
				continue;
			}
			$timeGap = $currentTime - $msg['time'];
			$pos = mb_strpos($msg['nickname'], '#', 0, 'utf-8');
			if ($pos !== FALSE) {
				$msg['nickname'] = mb_substr($msg['nickname'], 0, $pos, 'utf-8');
			}
			$msg['time_msg'] = $this->_getTimeGapMsg($timeGap);
			$msg['person_url'] = '/person/u/' . $msg['from'];
			switch ($msg['type']) {
				case 'like' :
					$msg['url'] = '/share/' . $msg['tid'];
					break;
				case 'collect' :
				case 'followgroup' :
					$msg['goodsUrl'] = '/share/' . $msg['tid'];
					$msg['url'] = '/group/' . $msg['gid'];
					break;
				case 'follow' :
					$msg['url'] = '/person/u/' . $msg['from'];
					break;
				case 'medal' :
					$msg['url'] = '/medal/detail/' . $msg['medal_id'];
					break;
				case 'travel' :
					$msg['url'] = '/huodong/vacation';
					break;
				default :
					break;
			}
			$newMsgInfo[] = $msg;
		}
		$this->_repinNotice = $newMsgInfo;
	}


	private function _getTimeGapMsg($timeGap) {
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
