<?php
namespace Snake\Package\User;

Use \Snake\Package\User\User as User;

class UserObject extends \Snake\Package\Base\DomainObject{
	//数据库中的一行纪录
	private $user = array();

    public function __construct($user = array()) {
		$this->user = $user;
	}
    
	public function __get($name) {
        if (array_key_exists($name, $this->user)) {
            return $this->user[$name];
        }   
        return NULL;
    }   
        
    public function __set($name, $value) {
        $this->user[$name] = $value;
    }   

    public function getUser() {
        return $this->user;
    }
   
	public function getUid() {
		return $this->user['user_id'];
	}

	//处理新浪微博等互联过来的 昵称带#的问题
	public function getNickname() {
		$nick = explode("#", $this->user['nickname']);   
		return $nick[0];
	}
}
