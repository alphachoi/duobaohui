<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper AS DBGroupHelper;

class TopGroup{
	
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

	public function getTopGroupInfo() {
        $gIds = array();
		$sql = "SELECT group_id FROM t_whale_topic_group WHERE 1 ORDER BY count_member DESC LIMIT 10";
        $gIds = DBGroupHelper::getConn()->read($sql, array());
        $groupId = array();
        foreach ($gIds AS $key => $value) {
            $groupId[] = $gIds[$key]['group_id'];
        }
		$this->groupIds = $groupId;

		return TRUE;
	}
}
