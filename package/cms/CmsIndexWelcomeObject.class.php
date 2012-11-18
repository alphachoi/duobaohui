<?php
namespace Snake\Package\Cms;

Use \Snake\Libs\Cache\Memcache;

class CmsIndexWelcomeObject extends \Snake\Package\Base\DomainObject{
	
	private $data = array();

    public function __construct($data = array()) {
		$this->data = $data;
	}

    public function getData() {
        return $this->data;
    }  

//	public function getGroupUserId() {
//		return $this->groupUser['user_id'];
//	}
	/*public function getTwitterId() {
		return $this->groupUser['twitter_id'];
	}
	public function getGroupId() {
		return $this->groupTwitter['group_id'];
	}*/
		
}
