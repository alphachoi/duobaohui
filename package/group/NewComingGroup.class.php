<?php
namespace Snake\Package\Group;

Use \Snake\Package\User\Helper\DBUserHelper AS DbUserHelper;
Use \Snake\Package\Group\Helper\DBGroupCmsHelper AS DBGroupCmsHelper;

class NewComingGroup {
	
	private $groupIds = array();

	public function __construct() {

	}

    public function __get($name) {
        if (array_key_exists($name, $this->group)) {
            return $this->group[$name];
        }
        return NULL;
    }


    public function getGroupIds() {
        return $this->groupIds;
    }
	
	public function save() {
		//TODO
	}

	public function getGroupBy() {
		return $this->groupBy;
	}

	public function getNewComingInfo() {
        $gIds = array();
        $sql = "SELECT data_id FROM t_dolphin_cms_index_welcome as t1 WHERE t1.page_type = 53";
        $groupId = DBGroupCmsHelper::getConn()->read($sql, array());
        $randKey = array_rand($groupId, 10); 
        foreach ($randKey AS $key => $value) {
            $gIds[$key]['id'] = $groupId[$value]['data_id'];
            $gIds[$key]['type'] = 'group';
        }
		$this->groupIds = $gIds;

		return TRUE;
	}
}
