<?php
namespace Snake\Package\User;

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\UserConnect;
Use \Snake\Package\User\Helper\RedisUserOauth;
Use \Snake\Package\Session\UserSession;
Use \Snake\libs\Cache\Memcache; 
use \Snake\Package\Msg\GetUserMsg ;
use \Snake\Package\Msg\GetNewShareMsg;



class LogOn360 {

	private $userId = NULL;
	private static $request = NULL;
	
	public function __construct() {

	}

	public function userLogOn(array $postParams, $wantPUT = 0) {
		if (empty($postParams)) {
			return FALSE;
		}
		$userName = $postParams['user_name'];
		$password = $postParams['password'];
		$checkStatus = $this->checkUserNameAndPassword($userName, $password);
		if (empty($wantPUT)) {
			return $checkStatus;
		}
		if ($wantPUT == 2) {
			$result = $this->_checkResult($checkStatus);
			return $result;
		}
		$response = $this->checkStatus($checkStatus);
		return $response;
	}

	private function _checkResult($checkStatus) {
		$response = array();
		if ($checkStatus == FALSE) {
			$response['status'] = -1;
		}
		else if ($checkStatus['is_actived'] < 0) {
			$response['status'] = -2;
		}
		else {
			$response['status'] = 1;
		}
		return $response;
	}

	private function checkUserNameAndPassword($userName, $password) {
		$userHelper = new User();
		$checkStatus = $userHelper->getUserBaseInfoByNicknameOrEmail($userName, '*');
		//$checkStatus = $userHelper->getUserBaseInfoByUsernameAndPassword($userName, $password);
		$pw = $checkStatus['password'];
		$key = md5(md5($checkStatus['email']) + $pw);
		if (($key === $password || $pw == $password || $pw == md5($password))  && !empty($checkStatus)) {
			$userProfile = $userHelper->getUserInfo($checkStatus['user_id'], array('avatar_c'), FALSE, FALSE);
			$checkStatus['avatar'] = \Snake\Libs\Base\Utilities::convertPicture($userProfile['avatar_c']);
			$checkStatus['avatar_c'] = $userProfile['avatar_c'];
			unset($checkStatus['password']);
			$checkStatus['password'] = $key;
			//$logon = array('360_logon' => 1);
			//$checkStatus = array_merge($checkStatus, $logon);
			$checkStatus['logon_360'] = 1;
			$checkStatus['base64'] = base64_encode('userid=' . $checkStatus['user_id'] . '&username=' . $checkStatus['nickname'] . '&password=' . $checkStatus['password']);
			return $checkStatus;
		}
		return FALSE;
	}

	private function checkStatus($checkStatus) {
		$baseUrl = MEILISHUO_URL . '/';
		$url = 'http://newlab.meilishuo.com/';
		if ($checkStatus == FALSE) {
			$response = array(
				'code' => 403,
				'msg' => '用户名或密码输入错误',
				'client' => '美丽说官方客户端v1.0'
			);
			return $response;
		}
		else if ($checkStatus['is_actived'] < 0) {
			$response = array(
				'code' => 401,
				'msg' => '该用户正在审核中',
				'client' => '美丽说官方客户端v1.0'
			);
			return $response;
		}
		else {
			$cacheHelper = Memcache::instance();
			$cacheKey = "LogOn360:" . $checkStatus['user_id'];
			$userId = $checkStatus['user_id'];
			$result = array(
				'code' => 200,
				'msg' => '正常',
				'client' => '美丽说官方客户端v1.0'
			);
			$session = substr(\Snake\Libs\Base\Utilities::getUniqueId(), 0, 17);
			$cacheHelper->set($cacheKey, $session, 3600 * 24);
			$querymsg = array('querymsgurl' => $baseUrl . 'app/360/notice?session=' . $session . '&user_id=' . $checkStatus['user_id']);
			$password = md5(md5($checkStatus['nickname']) + $checkStatus['password']);
			$msgHelper = new GetUserMsg($userId);
			$msgHelper->getInfoByUid();
			$msgInfo = $msgHelper->getMsgInfo();

			$newShareHelper = new GetNewShareMsg($userId, '', '360');
			$newShareHelper->getUserNewShareMsg();
			$msgInfo['newshare'] = $newShareHelper->getMsgInfo();
		    $content = array(
				array(
					'msgtypeid' => 'fans_num',
					'msgtypename' => '新粉丝',
					'navigateurl' => $baseUrl . 'ur/fans/' . $checkStatus['user_id'],
					'count' => (int) $msgInfo['fans_num'] 
				),  
				array(
					'msgtypeid' => 'atme_num',
					'msgtypename' => '@我',
					'navigateurl' => $baseUrl . 'atme',
					'count' => (int) $msgInfo['atme_num'] 
				),  
				array(
					'msgtypeid' => 'pmsg_num',
					'msgtypename' => '私信',
					'navigateurl' => $baseUrl . 'msg/main/user',
					'count' => (int) $msgInfo['pmsg_num'] 
				),  
				array(
					'msgtypeid' => 'recommend_num',
					'msgtypename' => '新喜欢我的',
					'navigateurl' => $baseUrl . 'atme/recommend/' . $checkStatus['user_id'],
					'count' => (int) $msgInfo['recommend_num']
				),  
				array(
					'msgtypeid' => 'sysmesg',
					'msgtypename' => '系统消息',
					'navigateurl' => $baseUrl . 'msg/main/syser',
					'count' => (int) $msgInfo['sysmesg']  
				),  
				array(
					'msgtypeid' => 'newshare',
					'msgtypename' => '新分享',
					'navigateurl' => $baseUrl . 'ihome',
					'count' => (int) $msgInfo['newshare']
				),
			);
			$msg = array(
				array(
				'categoryid' => 'category1',
				'categoryname' => '默认分类',
				'content' => $content
				)
			);
			$userInfo = array(
				'userid' => $checkStatus['user_id'],
				'displayname' => $checkStatus['nickname'],
				'headerurl' => $checkStatus['avatar'],
				'navigateurl' => $baseUrl . 'ihome'
			);
			$data = array(
				'result' => $result,
				'querymsg' => $querymsg,
				'userinfo' => $userInfo,
				'msg' => $msg
			);
			return $data;
		}
		return $checkStatus;

	}

	


}
