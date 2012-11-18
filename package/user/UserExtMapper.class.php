<?php
namespace Snake\Package\User;

use \Snake\Package\User\Helper\DBUserHelper;

class UserExtMapper extends UserBaseMapper{

	private $enforce = array('user_id');	

    public function __construct($user = array()) {
		parent::__construct($user, $this->enforce);
	}

	protected function doCreateObject(array $user) {
		$obj = new \Snake\Package\User\UserExtObject($user);	
		return $obj;
	}
	//TODO
	public function doInsert(array $user) {
	}
	//TODO
	public function doUpdate() {
	}

	protected function doCreateCollection(array $users) {
		$collection = new UserExtCollection($users, $this);
		return $collection;
	}
    
}
