<?php
namespace Snake\Package\Session;

/** 
 * 为后台的操作构造相应的用户信息
 */
use Snake\Package\User\User;
use Snake\Package\Mall\Mall;
use Snake\Package\User\Helper\RedisUserConnectHelper;
use \Snake\Package\User\UserConnect;
class HoutaiAction {
	
	private static $session;
	private static $user_id;

	public function __construct($request) {
		self::$user_id = !empty($request->REQUEST['user_id']) ? $request->REQUEST['user_id'] : 0;
		$param = array('user_id', 'nickname', 'email', 'ctime', 'password', 'active_code', 'cookie', 'is_actived', 'invite_code', 'last_logindate', 'status', 'realname', 'istested', 'reg_from', 'last_email_time', 'level', 'isPrompt', 'isBusiness', 'login_times', 'is_mall', 'avatar_c', 'avatar_d');
		if (empty(self::$user_id)) {
			self::$session = array();
		}
		else {
			//获得用户基本信息
			$userHelper = new User();
			
			$userInfo = $userHelper->getUserInfo(self::$user_id, $param);
			$userInfo['user_id'] = self::$user_id;
			//判断用户是否为商家
			$mallHelper = new Mall();
			$info = $mallHelper->getMallInfoById(self::$user_id);
			if(!empty($info)) {
				$userInfo['is_mall'] = 1;	
			}
			$result = $userHelper->checkUserProperty(self::$user_id);
			unset($userInfo['verify_msg']);
			unset($userInfo['about_me']);
			$userInfo['blueV'] = $result['blueV'];
			$userInfo['pinkV'] = $result['pinkV'];
			$userInfo['purpleV'] = $result['purpleV'];
			$userInfo['editor'] = $result['editor'];
			//将头像拼全连接
			$userInfo['avatar_c'] = \Snake\Libs\Base\Utilities::convertPicture($userInfo['avatar_c']);
			$userInfo['avatar_d'] = \Snake\Libs\Base\Utilities::convertPicture($userInfo['avatar_d']);
			//判断用户是否为互联用户
			$userInfo = self::_initOpenTokens(self::$user_id, $userInfo);
			//判断是否是关注qq空间
			$userInfo['qzone_notfans'] = UserConnect::getInstance()->checkQzoneIsFans(self::$user_id);
			self::$session = $userInfo;
		}
	}

	public function get_session() {
		if (!empty(self::$session['user_id']) && !empty(self::$session['nickname'])) {
			if (mb_strpos(self::$session['nickname'], '#', 0, 'utf-8') > 0) {
				$nick = explode('#', self::$session['nickname']);
				self::$session['nickname'] = $nick[0];
			} 
		}
		return self::$session;	
	}
	/**
     * 　１ 表示已经互联并同步，０表示没有互联，２表示互联不同步。 
	 **/
    static private function _initOpenTokens($userId, $saveData) {
		if (empty($userId)) {
			return FALSE;
		}
        $weiboSettings = RedisUserConnectHelper::getUserSetting('weibo', $userId);
        $weiboSettings = json_decode($weiboSettings, TRUE);
        $weiboToken = RedisUserConnectHelper::getUserToken('weibo', $userId);
        if (!empty($weiboSettings)) {
            $weibo = $weiboSettings['sync_goods'];
        }   
        else {
            $weibo = 1;
        }   
        if ($weibo == 0 && !empty($weiboToken)) {
            $saveData['weibo'] = 2;
        }   
        elseif ($weibo == 1 && !empty($weiboToken)) {
            $saveData['weibo'] = 1;
        }   
        else {
            $saveData['weibo'] = 0;
        }   
        $qzoneSettings = RedisUserConnectHelper::getUserSetting('qzone', $userId);
        $qzoneSettings = json_decode($qzoneSettings, TRUE);
        $qzoneToken = RedisUserConnectHelper::getUserToken('qzone', $userId);
        if (!empty($qzoneSettings)) {
            $qzone = $qzoneSettings['sync_goods'];
        }   
        else {
            $qzone = 1;
        }   
        if ($qzone == 1 && !empty($qzoneToken)) {
            $saveData['qzone'] = 1;
        }   
        elseif (!empty($qzoneToken)) {
            $saveData['qzone'] = 2;    
        }   
        else {
            $saveData['qzone'] = 0;
        }   
		return $saveData;
    } 
	
	
	
	
	
	
	
}
