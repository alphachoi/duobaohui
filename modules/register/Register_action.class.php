<?php
namespace Snake\Modules\Register;

USE \Snake\Package\User\User					AS User;
USE \Snake\Libs\Base\Utilities					AS Utilities;
USE \Snake\Package\User\UserRelation			AS UserRelation;
USE \Snake\libs\Cache\Memcache					AS Memcache;
USE \Snake\Package\Msg\ObsGetUserMsg            AS ObsGetUserMsg;
USE \Snake\Package\User\ObsUserRegister         AS ObsUserRegister;
USE \Snake\Package\User\ChangeRedisUserFans     AS ChangeRedisUserFans;
USE \Snake\Package\User\UserStatistic           AS UserStatistic;
USE \Snake\Package\User\UserFormat              AS UserFormat;
USE \Snake\Package\Edm\SendEdm                  AS SendEdm;
USE \Snake\Package\User\UserValidate            AS UserValidate;
USE \Snake\Package\User\ObsSendMsg              AS ObsSendMsg;
USE \Snake\package\User\LogOnOB					AS LogOnOB;
USE \Snake\Package\User\UserInvite              AS UserInvite;
USE \Snake\Package\Spam\SpamRegister            AS SpamRegister;
USE \Snake\Libs\Base\SnakeLog                   AS SnakeLog;


/**
 * 注册，用户信息写入到数据库
 * before : 用户填写注册信息
 * after : 发送激活码到邮箱
 *
 *
 * TODO 根据传递的参数选择不同的处理方式，比如免激活，邮件发送等等
 *
 * @author ChaoGuo
 */
class Register_action extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {

	const ERROR_DATABASE = 1;
	const DEBUG_MODE = 1;

	const NORMAL_URL = 1;
	const SPAM_URL = 2;

	private $userId = NULL;	  //用户编号
	private $inviteCode = ''; //邀请码
	private $captcha = '';	  //验证码
	private $URL = '';		  //返回的跳转URL
	private $santorini = '';
	private $observers = array();
	private $obsParams = array();

	private $userInfo = array(
		'email' => '',				//邮箱
		'nickname' => '',			//呢称
		'password' => '',			//密码
		'confirmpassword' => '',	//验证密码
		'gender' => '女',			//性别
		'agreement' => TRUE,		//注册条款
		'activateCode' => '',		//激活码
		'inviteCode' => '',			//邀请码
		'realname' => '',           //真实姓名
		'regFrom' => 10,		
		'cookie' => '',				
		'isActived' => 2,          
	);

	//用户详细信息
	private $userExtInfo = array(
		'gender' => '女',
		'birthday' => '0000-00-00',
		'province_id' => 0,
		'city_id' => 0,	
		'msn' => '',
		'qq' => '',
		'blog' => '',
		'about_me' => '',
		'interests' => '',
		'hobby' => '',
		'school' => '',
		'workplace' => '',
	);

	public function run() {
		if (!$this->_init() || !$this->_validate()) {
			return FALSE;
		}

		$spamRegister = new SpamRegister();	
		$spamRegister->setClientIp($this->request->ip);
		if ($spamRegister->registerSpam() === FALSE) {
			$this->view = array('url' => self::SPAM_URL);
			return TRUE;
		}

		//向数据库写入数据,发邮件等操作，返回URL	
		$result = $this->_registerAction();

		if ($result === self::ERROR_DATABASE) {
			$this->setError(400, 40204, 'database error');
			return FALSE;
		}
		else {
			$this->view = array(
				'url' => $this->URL,
				'email' => $this->userInfo['email'],
			);
		}
		return TRUE;
	}

	/**
	 * 初始化数据
	 *
	 */
	private function _init() {
		$this->userInfo['email'] = trim($this->request->REQUEST['email']);
		$this->userInfo['nickname'] = trim($this->request->REQUEST['nickname']);
		$this->userInfo['password'] = md5(trim($this->request->REQUEST['password']));
		$this->userInfo['confirmpassword'] = md5(trim($this->request->REQUEST['confirmpassword']));
		$this->userInfo['gender'] = trim($this->request->REQUEST['gender']);
		$this->userExtInfo['gender'] = trim($this->request->REQUEST['gender']);
		$this->captcha = strtolower(trim($this->request->REQUEST['checkcode']));
		$this->userInfo['agreement'] = $this->request->REQUEST['agreement'];
		$this->userInfo['activateCode'] = md5(Utilities::getUniqueId());
		$this->userInfo['inviteCode'] = md5(Utilities::getUniqueId());
		$this->userInfo['cookie'] = md5(Utilities::getUniqueId());
		$this->santorini = $this->request->COOKIE[DEFAULT_SESSION_NAME];

		$this->userInfo['regFrom'] = 2;

		if (!empty($this->request->REQUEST['invitecode'])) {
			$this->inviteCode = trim($this->request->REQUEST['invitecode']);
		}
		
		return TRUE;
	}

	/**
	 * 验证数据(非空，格式，数据)
	 *
	 */
	private function _validate() {
		//非空验证
		if (empty($this->userInfo['email']) ||
			empty($this->userInfo['nickname']) ||
			empty($this->userInfo['password']) || 
			empty($this->userInfo['confirmpassword']) ||
			empty($this->userInfo['gender']) ||
			empty($this->santorini) ||
			empty($this->captcha) ||
			empty($this->userInfo['agreement'])) {
				$this->setError(400, 40204, 'required field');
				return FALSE;
		}
		//验证码
		$validate = new UserValidate($this->captcha, $this->santorini);
		if ($validate->ValidateCaptcha($this->captcha, $this->santorini) === FALSE) {
			$log = new \Snake\Libs\Base\SnakeLog('captcha', 'normal');
			$log->w_log(print_r(array('register_action', 'captcha' => $this->captcha, 'santorini' => $this->santorini), true));
			$this->setError(400, 40205, 'captcha code invalid');
			return FALSE;
		}
		//将验证码cache删除
		$validate->ClearCaptcha($this->santorini);

		//格式验证
		$userFormatObj = new UserFormat();
		if ($this->userInfo['gender'] != '女' || 
			$this->userInfo['password'] != $this->userInfo['confirmpassword'] ||
			$userFormatObj->emailFormat($this->userInfo['email']) === FALSE ||
			$userFormatObj->nicknameFormat($this->userInfo['nickname']) === FALSE ) {
				$this->setError(400, 40204, 'format error');
				return FALSE;
		}
		//数据验证
		$email = array('email' => $this->userInfo['email']);
		$nickname = array('nickname' => $this->userInfo['nickname']);

		if ($this->isExist($email) === FALSE || $this->isExist($nickname) === FALSE || $this->isMaskWords($nickname) === FALSE) {
			$this->setError(400, 40205, 'email or nickname invalid');
			return FALSE;
		}
		return TRUE;
	}

	private function isMaskWords($param) {
		if (empty($param) || !is_array($param)) {
			return FALSE;
		}
        $maskWords = new \Snake\Package\Spam\MaskWords($param['nickname'], 'DFA_register');
        $mask = $maskWords->getMaskWords();
		if (!empty($mask['maskWords'])) {
			return FALSE;	
		}
		return TRUE;
	}

    private function isExist($param) {
		if (empty($param) || !is_array($param)) {
			return FALSE;
		}
        $user = new User();
        $result = $user->getUserProfile($param, "count(*) AS num");
        if ($result[0]['num'] > 0) {
			return FALSE;
        }   
		return TRUE;
    }

	/**
	 * 注册操作
	 *
	 */
	private function _registerAction() {
		$user = new User();
		//初始化基本信息
		$this->userId = $user->insertUserBaseInfo($this->userInfo);
		if (!isset($this->userId) && !is_numeric($this->userId) && !$this->userId > 0) {
			$log = new SnakeLog("empty_userinfo", "normal");
			$log->w_log(print_r(array('userid' => $this->userId, 
				'regfrom' => $this->userInfo['regFrom'], 
				'userinfo' => $this->userInfo,
				'santorini' => $this->santorini,
				'extinfo' => $this->userExtInfo,
			), true));
			return self::ERROR_DATABASE;
		}
		//附属信息
		$this->addObserver(new ObsUserRegister());	
		//系统消息
		$this->addObserver(new ObsGetUserMsg());
		//发送激活邮件
		$this->addObserver(new SendEdm());
		//登录
		$this->addObserver(new LogOnOB());
		//通过邀请过来的用户
		$this->inviteAction();
		//在邀请的步骤里会添加新key:invitename, other_id
		$this->obsParams['user_id'] = $this->userId;
		$this->obsParams['ext_info'] = $this->userExtInfo;
		$this->obsParams['nickname'] = $this->userInfo['nickname'];
		$this->obsParams['email'] = $this->userInfo['email'];
		$this->obsParams['activatecode'] = $this->userInfo['activateCode'];
		$this->obsParams['request'] = $this->request;
		$this->obsParams['password'] = $this->request->REQUEST['password'];

		foreach ($this->observers as $obs) {
			$obs->onChanged('Register_action', $this->obsParams);
			if (self::DEBUG_MODE == 1) {
				$log = new \Snake\Libs\Base\SnakeLog('observer', 'normal');
				$log->w_log(print_r(array($this->userInfo['nickname'], $obs), true));
			}
		}
		
		//处理一些Cookie或条件,根据它(比如邀请码)返回特定的URL标记
		$this->URL = self::NORMAL_URL;
	}

	private function inviteAction() {
		$otherUid = 0;	
		if (!empty($this->inviteCode) && $this->inviteCode != 'aa74158f1933b65e23e61bf99d8157f5') {
			$param = array(
				'invite_code' => $this->inviteCode,
			);
			$user = new User();
			$inviteUserInfo = $user->getUserProfile($param, 'user_id, nickname, is_actived');
			if (!empty($inviteUserInfo) && ($inviteUserInfo[0]['is_actived'] == 1 || $inviteUserInfo[0]['is_actived'] == 0)) {
				$otherUid = $inviteUserInfo[0]['user_id'];
				$otherName = $inviteUserInfo[0]['nickname'];
				//使用这个参数发送系统消息
				$this->obsParams['invitename'] = $otherName;
				$this->obsParams['other_id'] = $otherUid;
				//互相关注
				$return = UserRelation::getInstance()->setUserFollow($this->userId, $otherUid);
				$returnO = UserRelation::getInstance()->setUserFollow($otherUid, $this->userId);
				if (!empty($return) && !empty($returnO)) {
					$this->addObserver(new ChangeRedisUserFans());	
					$this->addObserver(new UserStatistic());
				}
				//写入邀请关系
				$userInvite = new UserInvite();
				$userInvite->insertUserInvite($this->inviteCode, $this->userId);
				//向邀请人发送私信
				$this->addObserver(new ObsSendMsg());
			}
		}
	}
    public function addObserver($observer) {
        $this->observers[] = $observer;
    }
}
