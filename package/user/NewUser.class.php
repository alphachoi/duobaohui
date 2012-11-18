<?php
namespace Snake\Package\User;

use \Snake\Package\User\User;
use \Snake\Package\User\Helper\DBUserHelper;

class NewUser implements \Snake\Package\User\IUser, \Snake\Libs\Interfaces\IObservable {

	protected $user;
	protected $userInfo = array();
	private $observers = array();

	public function __construct($userInfo) {
		$this->userInfo = $userInfo;
		//TODO addObserver
		$this->addObserver(new \Snake\Package\User\UserStatistic());
		//$this->addObserver(new \Snake\Package\User\UserPmsg());
		//and so on...
	}

	public function __get($name) {
		if (array_key_exists($name, $this->user)) {
			return $this->user[$name];
		}
	}

	public function __set($name, $value) {
		$this->user[$name] = $value;
	}

	public function getUser() {
		return $this->user;
	}

	public function addObserver($observer) {
		$this->observers[] = $observer;
	}

	public function create() {
		//TODO
		$sql = "INSERT IGNORE INTO t_dolphin_user_profile (nickname, email, ctime, password, active_code, invite_code, is_actived, realname, reg_from, cookie) VALUES (:nackname, :username, now(), :userpassword, :active_code, :invite_code, :isActived, :realname, :reg_from, :cookie)";
		$sqlData['username'] = $this->userInfo['username'];
		//TODO 
		$insert_succ = DBUserHelper::getConn()->write($sql, $sqlData);
		if ($insert_succ === TRUE) {
			$id = DBUserHelper::getConn()->getInsertId();
		}
		else {
			return FALSE;
		}
		$this->user->user_id = $id;
		foreach ($this->userInfo as $name => $value) {
			$this->user->$name = $value;
		}

		foreach ($this->observers as $observer) {
			$observer->onChange($this, $id);
		}
	}
}
