<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper;
Use \Snake\libs\Cache\Memcache;

class TopicGroupTwitterMapper extends \Snake\Package\Base\Mapper{
	//需要作为where条件的字段
	private $enforce = array('twitter_id','group_id');
	private $groupTwitter = array();

    public function __construct($groupTwitter = array()) {
		parent::__construct($this->enforce);
		$this->groupTwitter = $groupTwitter;
	}
	public function __get($name) {
        if (array_key_exists($name, $this->twitter)) {
            return $this->groupTwitter[$name];
        }
        return NULL;
    }   

    public function __set($name, $value) {
        $this->groupTwitter[$name] = $value;
    }   

    public function getGroupTwitter() {
        return $this->groupTwitter;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	protected function doCreateObject(array $group) {
		$obj = new TopicGroupTwitterObject($group);	
		return $obj;
	}
	//TODO
	public function doInsert($sql, array $sqlData) {
		DBGroupHelper::getConn()->write($sql, $sqlData);
		$id = DBGroupHelper::getConn()->getInsertId();
		return $id;
	}
	//TODO
	public function doUpdate($sql, array $sqlData){
		$result = DBGroupHelper::getConn()->write($sql, $sqlData);
		return $result;
	}

	protected function doCreateCollection(array $group) {
		$collection = new TopicGroupTwitterCollection($group, $this);
		return $collection;
	}

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->groupTwitter = DBGroupHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->groupTwitter;
	}
}
