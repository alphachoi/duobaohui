<?php
namespace Snake\Package\ShareOutside;

use \Snake\Package\Twitter\Twitter AS Twitter;
use \Snake\Package\Picture\PictureFactory AS PictureFactory;
use \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnectHelper;
use \Snake\Package\Group\GroupFactory AS GroupFactory;
use \Snake\Package\Group\Group AS Group;
use \Snake\Package\Shareoutside\ShareHelper AS ShareHelper;

class ShareOb implements \Snake\Libs\Interfaces\Iobserver{

	const SHARE_QZONE = 4;

	public function __construct() {

	}
	
	public function onChanged($sender, $params) {
		switch ($sender) {
			case 'Register_actionconnect' :
				$this->_shareSync($params['reg_from'], $params['user_id']);
				break;
			default :
				$this->_qplusSync($params['flag'], $params['group_id'], $params['user_id']);	
				break;
		}
    }

	
	private function _shareSync($regFrom, $userId) {
		if (empty($regFrom) || empty($userId)) {
			return FALSE;
		}
		$extras = array();	
		$content = '';
		list($content, $extras) = $this->_getShareInfo($regFrom, $userId);
		ShareHelper::sync($userId, '', 0, 4, 0, $content, array(), $extras);
	}

	private function _getShareInfo($type, $userId) {
		$extras = array();
		$content = '';
		switch ($type) {
			case self::SHARE_QZONE :
				$content = ">>美丽说，陪你美丽每一天！";	
		        $extras['comment'] = "我刚刚入驻了美丽说 ，男生止步，求姑娘关注>>" . "http://www.meilishuo.com/person/u/" . $userId . "?frm=huiliu_zhuceqzone";
		        $extras['image'] = "http://imgtest-lx.meilishuo.net/css/images/user/qq_sync.jpg";
			    $extras['url'] = 'http://www.meilishuo.com/group?frm=huiliu_groupzhuceqzone';
			break;
			default :
				break;
		}
		return array($content, $extras);
	}


	public function _qplusSync($flag = NULL, $group_id, $user_id) {
		$groupId = array( 0 => $group_id);
		$groupHandle = new GroupFactory($groupId);
		$groupInfoObj = $groupHandle->getGroups();
		foreach ($groupInfoObj AS $key) {
			$groupInfo = $key->getGroup();
		}

		$group_name = $groupInfo['name'];
        $extras = array();
        if ($flag == 'follow') {
            $content = '我刚刚在 @美丽说 关注了杂志社' . '#' . $group_name . '#'; 
            $extras['image'] = "http://imgtest-lx.meilishuo.net/". $result[0]['logo_path'];
            if ($result[0]['logo_path'] == 'glogo/a/24/3a/029fd92ab2a12e07e04a57460a69_180_180.jpg' || $result[0]['header_path'] == 'glogo/_o/77/62/5ca16a2ba61b5933a50dbbc9444a_942_248.jpg') {
                $extras['image'] =  "http://imgtest-lx.meilishuo.net/css/images/group/xxy1.gif";
            }    
        }    
        else {
            $content = '我刚刚在 @美丽说 创建了杂志社' . '#' . $group_name . '#'; 
            $extras['image'] =  "http://imgtest-lx.meilishuo.net/css/images/group/xxy1.gif";
        }    
        if (empty($extras['image'])) {
            $extras['image'] =  "http://imgtest-lx.meilishuo.net/css/images/group/xxy1.gif";
        }    
        $extras['title'] = '[多图]' . $group_name . '- 美丽说';
        $extras['url'] = "http://www.meilishuo.com/group/" . $group_id . '?frm=connectqplus';
        $extras['source'] = 1; 
        //同步新鲜事
        ShareHelper::sync( $user_id, '', '', 11, 0, $content, '', $extras);	
	
	}

}

