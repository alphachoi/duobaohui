<?php
namespace Snake\Package\User;

use \Snake\Package\User\Helper\DBUserHelper;

class UserBaseMapper extends \Snake\Package\Base\Mapper{

	protected $user = array();

    public function __construct(array $user, $enforce) {
		parent::__construct($enforce);
		$this->user = $user;
	}
	public function remove(array $uids) {
	}
	
    public function getUser() {
        return $this->user;
    }   
	public function get($sql, $sqlData) {
		$this->user = DBUserHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
		return $this->user;
	}
	protected function doCreateObject(array $user) {
	}
	//TODO
	public function doInsert(array $user) {
	}
	//TODO

	public function doUpdate() {
	}

	protected function doCreateCollection(array $users) {
		$collection = new UserCollection($users, $this);
		return $collection;
	}
	    
}
