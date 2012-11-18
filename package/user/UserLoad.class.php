<?php
namespace Snake\Package\User;

use \Snake\Package\User\User;
use \Snake\Package\User\Helper\DBUserHelper;

class UserLoad implements \Snake\Package\User\IUser {

	protected $userInfos = array();
	protected $users = array();

	public function __construct() {}

	public function addUser(User $user) {
		$this->userInfos[] = $user;
	}

	public function getUser() {
		if (empty($this->users)) {
			return FALSE;
		}
		elseif (count($this->users) > 1) {
			return $this->users;
		}
		else {
			return $this->users[0];
		}
	}

	private function conform($result) {
		if (!empty($result)) {
			foreach ($reuslt as $userInfo) {
				$this->users[] = new User($userInfo);
			}
		}
	}

	private function getUserIds() {
		$user_ids = array();
		foreach ($this->userInfos as $user) {
			if (!empty($user->user_id) && is_numeric($user->user_id) {
				$user_ids[] = $user->user_id;
			}
			else {
				die('wrong parameter');
			}
		}
		$user_ids = implode(',', $user_ids);
		
		return $user_ids;
	}

	public function getUserBasefromIds($col = '*') {
		if ("*" == $col) {
			die('please do not select *');
		}
		$user_ids = $this->getUserIds();
		$sql = "SELECT $col FROM t_dolphin_user_profile WHERE user_id IN ($user_ids)";
		$result = DBUserHelper::getConn()->read($sql, array());
		$this->conform($result);
		return $this->getUser();
	}
	/*
	这个函数很强大，只有你想不到的,没有它做不到的。如果需要请在此扩展
	*/
   function getUserProfile($param = array(), $col = "*" , $getFromMaster = FALSE) {
		if ("*" == $col) {
			die('please do not select *');
		}
    	$sqlData = array();
		$sqlComm = "SELECT $col FROM t_dolphin_user_profile WHERE ";
		if (isset($param['user_id'])) {
			if (is_array($param['user_id'])) {
				$uids = implode(",", $param['user_id']);
				$sqlComm .= "AND user_id in($uids)"; 	
			}
			else {
				$sqlComm .= "AND user_id=:_user_id";
				$sqlData['_user_id'] = $param['user_id'];
			}
		}
		if (isset($param['active_code'])) {
			$sqlComm .= " AND active_code=:active_code";
			$sqlData['active_code'] = $param['active_code'];
		}
		if (isset($param['nickname'])) {
			$sqlComm .= " AND nickname=:nickname";
			$sqlData['nickname'] = $param['nickname'];
		}
   		if (isset($param['email'])) {
			$sqlComm .= " AND email=:email";
			$sqlData['email'] = $param['email'];
		}
		$result = DBUserHelper::getConn()->read($sql, $sqlData, $getFromMaster);
		return $result;
    }
	/*public function getUsersInfo($uids, $col = '*', $master = FALSE) {
		print_r($uids);
		if ("*" == $col) {
			die('please do not select *');
		}
		$str = implode(",", $uids);
		$sql = "SELECT $col FROM t_dolphin_user_profile t1 
				LEFT JOIN t_dolphin_user_profile_extinfo t2 
				ON t1.user_id = t2.user_id 
				WHERE user_id IN ($str)";
		return DBUserHelper::getConn()->read($sql, array(), $master);
	}*/

	public function getUsersfromIds($col = '*') {
		if ("*" == $col) {
			die('please do not select *');
		}
		$user_ids = $this->getUserIds();
		$sql = "SELECT $col FROM t_dolphin_user_profile t1 
				LEFT JOIN t_dolphin_user_profile_extinfo t2 
				ON t1.user_id = t2.user_id 
				WHERE user_id IN ($user_ids)";
		$result = DBUserHelper::getConn()->read($sql, array());
		$this->conform($result);
		return $this->getUser();
	}

	public function getUserfromMailandPassword($col = '*') {
		if (empty($this->userInfos[0]->email) || empty($this->userInfos[0]->password)) {
			die('wrong parameter');
		}
		if ("*" == $col) {
			die('please do not select *');
		}
		$sql = "SELECT $col FROM t_dolphin_user_profile t1
				LEFT JOIN t_dolphin_user_profile_extinfo t2 
				ON t1.user_id = t2.user_id 
				WHERE t1.email = :email AND t1.password = :password";
		$sqlData = array('email' => $this->userInfos[0]->email,
						 'password' => $this->userInfos[0]->password);
		$result = DBUserHelper::getConn()->read($sql, $sqlData);
		$this->conform($result);
		return $this->getUser();
	}

	public function get_user_from_cookie() {
		if (empty($this->userInfos[0]->cookie)) {
			die('wrong parameter');
		}
		$sql = "SELECT t1.*, t2.avatar_c FROM t_dolphin_user_profile t1 
				LEFT JOIN t_dolphin_user_profile_extinfo t2 ON t2.user_id = t1.user_id WHERE cookie = :cookie";
		$sqlData['cookie'] = $this->userInfos[0]->cookie;
		$result = DBUserHelper::getConn()->read($sql, $sqlData);
		$this->conform($result);
		return $this->getUser();
	}

}
