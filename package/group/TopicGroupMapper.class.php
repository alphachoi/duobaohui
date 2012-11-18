<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper;
Use \Snake\libs\Cache\Memcache;

class TopicGroupMapper extends \Snake\Package\Base\Mapper{
	//需要作为where条件的字段
	private $enforce = array('group_id','name');
	private $group = array();

    public function __construct($group = array()) {
		parent::__construct($this->enforce);
		$this->group = $group;
	}
	public function __get($name) {
        if (array_key_exists($name, $this->twitter)) {
            return $this->group[$name];
        }
        return NULL;
    }   

    public function __set($name, $value) {
        $this->group[$name] = $value;
    }   

    public function getGroup() {
        return $this->group;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	protected function doCreateObject(array $group) {
		$obj = new TopicGroupObject($group);	
		return $obj;
	}
	//TODO
	public function doInsert($sql, array $sqlData) {
		DBGroupHelper::getConn()->write($sql, $sqlData);
		$result = DBGroupHelper::getConn()->getInsertId();
		return $result;
	}
	//TODO
	public function doUpdate($sql, array $sqlData){
		$return = DBGroupHelper::getConn()->write($sql, $sqlData);
		return $return;
	}

	protected function doCreateCollection(array $group) {
		$collection = new TopicGroupCollection($group, $this);
		return $collection;
	}

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->group = DBGroupHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->group;
	}
	    
}
