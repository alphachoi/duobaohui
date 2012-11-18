<?php
namespace Snake\Package\Group;

use \Snake\Package\Group\Helper\DBGroupHelper AS DBGroupHelper;

class Group {
	
	private $group = array();

	public function __construct($group = array()) {
		$this->group = $group;
		if (!empty($this->group['group_id'])) {
            $sql = "SELECT /*groupfac-xj*/count(twitter_id) AS num FROM t_whale_topic_group_twitter WHERE group_id = :_group_id AND have_picture = 1 AND show_type = 0";
            $sqlData = array('_group_id' => $this->group['group_id']);
            $result = DBGroupHelper::getConn()->read($sql, $sqlData);
            $this->group['num'] = $result[0]['num'];	
		}
	}

    public function __get($name) {
        if (array_key_exists($name, $this->group)) {
            return $this->group[$name];
        }
        return NULL;
    }

    public function __set($name, $value) {
        $this->group[$name] = $value;
    }

	public function setTwitterIds($twitter_ids = array()) {
		foreach ($twitter_ids as $twitter_id) {
			$this->group['twitter_ids'][$twitter_id] = array('twitter_id' => $twitter_id);
		}
	}

	public function setPicUrl($url) {
		$this->group['picture_url'] = $url;
	}

	public function getGroup() {
		return $this->group;
	}
	
	public function save() {
		//TODO
	}
}
