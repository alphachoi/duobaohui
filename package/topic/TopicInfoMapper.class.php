<?php
namespace Snake\Package\Topic;

Use \Snake\Package\Topic\Helper\DBTopicInfoHelper;
use \Snake\libs\Cache\Memcache;

class TopicInfoMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('topic_id');	
	private $topicInfo =  array();

    public function __construct($topicInfo = array()) {
		parent::__construct($this->enforce);
		$this->topicInfo = $topicInfo;
	}

    public function getTopicInfo() {
        return $this->topicInfo;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	//TODO
	public function doInsert($sql,array $sqlData) {
	}
	//TODO
	public function doUpdate() {
	}

    public function doGet($sql, array $sqlData) {
		$this->topicInfo = DBTopicInfoHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->topicInfo;
    }
}
