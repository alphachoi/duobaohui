<?php
namespace Snake\Package\User;

USE \Snake\Package\User\User AS User;
USE \Snake\Package\User\UserSetting             AS UserSetting;
USE \Snake\Package\User\UserStatistic           AS UserStatistic;
USE \Snake\Package\User\UserConnect             AS UserConnect;

/**
 * 注册用户的写库操作(t_dolphin_user_profile除外)
 *
 * t_dolphin_user_profile_extinfo
 * t_dolphin_user_settings
 * t_dolphin_user_statistic
 * t_dolphin_user_gkey
 * t_dolphin_user_profile_connect(互联注册)
 *
 * GC
 */
class ObsUserRegister implements \Snake\Libs\Interfaces\Iobserver {

	public function __construct() {

	}

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Register_actionconnect' :
				$this->actionConnectRegister($args['user_id'], $args['ext_info'], $args['reg_from'], $args['auth'], $args['access_token'], $args['request']);
				break;
			case 'Register_action' :
				$this->actionRegister($args['user_id'], $args['ext_info'], $args['request']);
				break;
			default :
				break;
		}
	}

	/**
	 * 普通注册用户写库操作
	 */	
	private function actionRegister($userId, $extInfo, $request) {
		$user = new User();
		//初始化用户详细信息
        $user->insertUserExtInfo($userId, $extInfo);    
        //初始化统计信息
        UserStatistic::getInstance()->addUserStatisticRow($userId);
        //初始化设置信息
        UserSetting::getInstance()->insertSettings($userId);
        //设置用户Global Key
        $this->setGlobalKey($userId, $request, $user);		
	}

	/**
	 * 互联注册用户的写库操作
	 */
	private function actionConnectRegister($userId, $extInfo, $regFrom, $auth, $access, $request) {
		$user = new User();
		//初始化用户详细信息
		$user->insertUserExtInfo($userId, $extInfo);
		//初始化用户设置
		UserSetting::getInstance()->insertSettings($userId);
		//初始化统计信息
		UserStatistic::getInstance()->addUserStatisticRow($userId);   
		//初始化连接信息   
		UserConnect::getInstance()->insertUserConnectInfo($userId, $regFrom, $auth, $access);    
		//设置用户Global Key 
        $this->setGlobalKey($userId, $request, $user);
	}

	/**
	 * 初始化用户Global Key
	 */
	private function setGlobalKey($userId, $request, $user) {
		$globalKey = $request->COOKIE['MEILISHUO_GLOBAL_KEY'];			
		if (empty($globalKey)) {
			//$globalKey = \Snake\Libs\Base\Utilities::getGlobalKey();	
			//setcookie('MEILISHUO_GLOBAL_KEY', $globalKey, time() + 3600 * 24 * 365 * 500, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
			return FALSE;
		}
		$user->setGlobalKey($globalKey, $userId, time());
	}
}
