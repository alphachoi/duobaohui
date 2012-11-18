<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper AS DBGroupHelper;
Use \Snake\Libs\Cache\Memcache;

class TopicGroupTwitterObject extends \Snake\Package\Base\DomainObject{
	
	private $groupTwitter = array();

    public function __construct($groupTwitter = array()) {
		$this->groupTwitter = $groupTwitter;
	}

    public function getGroup() {
        return $this->groupTwitter;
    }

	public function getTwitterId() {
		return $this->groupTwitter['twitter_id'];
	}

	public function getGroupId() {
		return $this->groupTwitter['group_id'];
	}

	public function getFields() {
		return $this->groupTwitter['fields'];
	}
	
	public function getCondition() {
		return $this->groupTwitter['condition'];
	}
	public function getInsert() {
		return $this->groupTwitter['insert'];
	}
		
	public function getInfo() {
		return $this->groupTwitter;
	}
}
