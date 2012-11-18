<?php
namespace Snake\Package\Group;

class TopicGroupClassifyObject extends \Snake\Package\Base\DomainObject{
	
	private $group = array();

    public function __construct($group = array()) {
		$this->group = $group;
	}
    public function getGroup() {
        return $this->group;
    }   

	public function getGroupId() {
		return $this->group['group_id'];
	}

	public function getId() {
		return $this->group['group_id'];
	}

	public function getFields(){
		return $this->group['fields'];
	}
	
	public function getInsert() {
		return $this->group['insert'];
	}
	
	public function getCondition() {
		return $this->group['condition'];
	}
	
	public function setId($id) {
		$this->group['group_id'] = $id;
		return TRUE;
	}
}
