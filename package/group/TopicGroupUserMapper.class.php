<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper;
Use \Snake\libs\Cache\Memcache;

class TopicGroupUserMapper extends \Snake\Package\Base\Mapper{
	//需要作为where条件的字段
	private $enforce = array('user_id','group_id');
	private $groupUser = array();

    public function __construct($groupUser = array()) {
		parent::__construct($this->enforce);
		$this->groupUser = $groupUser;
	}
	public function __get($name) {
        if (array_key_exists($name, $this->user)) {
            return $this->groupUser[$name];
        }
        return NULL;
    }   

    public function __set($name, $value) {
        $this->groupUser[$name] = $value;
    }   

    public function getGroupUser() {
        return $this->groupUser;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	protected function doCreateObject(array $group) {
		$obj = new TopicGroupUserObject($group);	
		return $obj;
	}
	//TODO
	public function doInsert($sql, array $sqlData) {
		DBGroupHelper::getConn()->write($sql, $sqlData);
		$id = DBGroupHelper::getConn()->getInsertId();
		return $id;
	}

	public function doUpdate($sql, array $sqlData){
		$result = DBGroupHelper::getConn()->write($sql, $sqlData);
		return $result;
	}
	protected function doCreateCollection(array $group) {
		$collection = new TopicGroupUserCollection($group, $this);
		return $collection;
	}

	/* 批量获取twitter内容 */
    public function get($sql, $sqlData) {
		$this->groupUser = DBGroupHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->groupUser;
	}
	    
	public function doGet($sql, array $sqlData) {

	}
}
