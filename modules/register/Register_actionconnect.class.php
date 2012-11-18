<?php
namespace Snake\Modules\Register;

USE \Snake\Package\User\User                    AS User;
USE \Snake\Libs\Base\Utilities                  AS Utilities;
USE \Snake\Package\User\UserRelation            AS UserRelation;
USE \Snake\Package\User\UserStatistic           AS UserStatistic;
USE \Snake\Package\User\UserSetting             AS UserSetting;
USE \Snake\Libs\Cache\Memcache                  AS Memcache;
USE \Snake\Package\Msg\GetUserMsg			    AS GetUserMsg;
USE \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnect; 
USE \Snake\Package\Connect\ConnectFactory       AS ConnectFactory;
USE \Snake\Package\User\UserConnect				AS UserConnect;
USE \Snake\Package\Session\UserSession			AS UserSession;
USE \Snake\Package\User\Area				    AS Area;
USE \Snake\Package\User\ObsUserRegister         AS ObsUserRegister;
USE \Snake\Package\Msg\ObsGetUserMsg            AS ObsGetUserMsg;
USE \Snake\Package\User\ObsUpdateRedisConnect   AS ObsUpdateRedisConnect;
USE \Snake\Package\User\ObsMedal                AS ObsMedal;
USE \Snake\Package\User\LogOnOB                 AS LogOnOB;
USE \Snake\Package\User\ObsSendMsg				AS ObsSendMsg;
USE \Snake\Package\User\ObsAvarter              AS ObsAvarter;
USE \Snake\Package\Shareoutside\ShareOb         AS ShareOb;

/**
 * 互联注册
 *
 */
class Register_actionconnect extends  \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {

	//用户编号
	private $userId = NULL;
	//access token
	private $access = '';
	//互联用户信息和美丽说的绑定id
	private $auth = '';
	//过期时间
	private $ttl = 3600;
	//跳转URL
	private $url = '';
	private $type = 'weibo';
	private $avatar = '';
	private $password = '';
	private $jump = '';
	//	
	private $observers = array();

	private $userInfo = array(
        'email' => '',              //邮箱
        'nickname' => '',           //呢称
        'password' => '',           //密码
        'gender' => '女',           //性别
        'agreement' => TRUE,        //注册条款
        'activateCode' => '',       //激活码
        'inviteCode' => '',         //邀请码
        'realname' => '',           //真实姓名
        'regFrom' => 10,            
        'cookie' => '',            
        'isActived' => 0,          
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
		if (!$this->_init()) {
			$this->setError(400, 40150, 'empty userinfo');
			return FALSE;
		}
		
		if (!$this->_registerAction()) {
			$this->setError(400, 40150, 'database error');
			return FALSE;
		}		
		
		$this->view = 'ihome';
		if ($this->jump == 'frm360') {
			$this->view = 'app/360dev/success';
		}
		return TRUE;
	}

	private function _init() {
		$cacheHelper = Memcache::instance();
		$cacheKey = 'Connect:Info:' . $this->request->COOKIE['santorini_mm'];
		$userInfo = $cacheHelper->get($cacheKey);
		$cacheHelper->delete($cacheKey);
		if (!empty($userInfo)) {
			$areaHelper = new Area();
			$this->userInfo['email'] = $userInfo['email'];
			$this->userInfo['nickname'] = $userInfo['nickname'];
			$this->userInfo['realname'] = $userInfo['realname'];
			$this->avatar = $userInfo['avatar'];
			$this->type = substr($userInfo['type'], 4);
			$this->auth = $userInfo['auth'];
			$this->access = $userInfo['access_token'];
			$this->ttl = $userInfo['ttl'];
			$this->jump = !empty($userInfo['frm']) ? $userInfo['frm'] : '';
			$this->password = md5(Utilities::getUniqueId());
			$this->userInfo['password'] = md5($this->password);
			$this->userInfo['activateCode'] = md5(Utilities::getUniqueId());
			$this->userInfo['inviteCode'] = md5(Utilities::getUniqueId());
			$this->userInfo['lastLogindate'] = time();
			$this->userInfo['cookie'] = md5(Utilities::getUniqueId());
			$this->userInfo['gender'] = '女';
			$this->userInfo['regFrom'] = $userInfo['openType'];
			if (!empty($userInfo['province'])) {
				$fields = array('N_PROVID');
				$params = array(
					'S_PROVNAME' => $userInfo['province'],
				);
				$result = $areaHelper->getProvinceInfo($fields, $params);
				$this->userExtInfo['province_id'] = $result[0]['N_PROVID'];
			}
			if (!empty($userInfo['city'])) {
				$fields = array('N_CITYID');
				$params = array(
					'S_CITYNAME' => $userInfo['city'],
				);
				$result = $areaHelper->getCityInfo($fields, $params);
				$this->userExtInfo['city_id'] = $result[0]['N_CITYID'];
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 注册操作
	 */
	private function _registerAction() {
		$user = new User();	
		//初始化基本信息
        $this->userId = $user->insertUserBaseInfo($this->userInfo);
		if (!isset($this->userId) || !is_numeric($this->userId) || $this->userId <= 0) {
			$log = new \Snake\Libs\Base\SnakeLog('gc_register_empty', 'normal');
			$log->w_log(print_r(array($this->userInfo, 1), true));
			return FALSE;
		}

		$this->userInfo['base64'] = '';
		if ($this->jump == 'frm360') {
			$passwordE = md5(md5($this->userInfo['email']) + $this->password); 
			$this->userInfo['base64'] = base64_encode('userid=' . $this->userId . '&username=' . $this->userInfo['nickname'] . '&password=' . $passwordE);
		}

		//互联头像
		$cache = Memcache::instance();
		$cache->set('users_temp_avatar_' . $this->userId, $this->avatar, 7200);	
		//注册写库
		$this->addObserver(new ObsUserRegister());
		//发送系统消息
		$this->addObserver(new ObsSendMsg());
		//设置SYS_MSG_ID(系统消息有关)
		$this->addObserver(new ObsGetUserMsg());
		//更新互联Redis
		$this->addObserver(new ObsUpdateRedisConnect());
		//抓取头像，写入extinfo表
		$this->addObserver(new ObsAvarter());
		//勋章并发消息
		$this->addObserver(new ObsMedal());
		//站外同步消息
		$this->addObserver(new ShareOb());
		//登录 
		$this->addObserver(new LogOnOB());
		$params = array(
			'user_id' => $this->userId,
			'ext_info' => $this->userExtInfo,
			'reg_from' => $this->userInfo['regFrom'],
			'email' => $this->userInfo['email'],
			'auth' => $this->auth,
			'access_token' => $this->access,			
			'ttl' => $this->ttl,
			'request' => $this->request,
			'type' => $this->type,
			'password' => $this->password,  
			'request' => $this->request,
			'avatar' => $this->avatar,
			'base64' => $this->userInfo['base64'],
		);

		foreach ($this->observers as $obs) {
			$timeHelper = new \Snake\Libs\Base\TimeHelper();
			$timeHelper->start(); 

			$obs->onChanged('Register_actionconnect', $params);

			$timeHelper->stop();
			$spend = $timeHelper->spent();

			$log = new \Snake\Libs\Base\SnakeLog('register_spend', 'normal');
			$log->w_log(print_r(array($obs, $spend), true));
        }

		return TRUE;
	}

    public function addObserver($observer) {
        $this->observers[] = $observer;
    }
}
