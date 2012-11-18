<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper AS DBGroupHelper;
Use \Snake\Libs\Cache\Memcache;

class TopicGroupUserObject extends \Snake\Package\Base\DomainObject{
	
	private $groupUser = array();

    public function __construct($groupUser = array()) {
		$this->groupUser = $groupUser;
	}

    public function getGroupUser() {
        return $this->groupUser;
    }  

	public function getGroupUserId() {
		return $this->groupUser['user_id'];
	}
	/*public function getTwitterId() {
		return $this->groupUser['twitter_id'];
	}
	public function getGroupId() {
		return $this->groupTwitter['group_id'];
	}*/
	public function getCondition() {
		return $this->groupUser['condition'];
	}

	public function getFields(){
		return $this->groupUser['fields'];
	}
	
	public function getInsert() {
		return $this->groupUser['insert'];
	}
	
	public function setId($id) {
		$this->groupUser['group_id'] = $id;
		return TRUE;
	}

	public function getInfo() {
		return $this->groupUser;
	}
}
