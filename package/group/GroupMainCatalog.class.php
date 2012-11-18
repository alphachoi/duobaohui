<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupCmsHelper AS DBGroupCmsHelper;

class GroupMainCatalog {
	
	private $groupIds = array();
    private $groupBy = array();

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

	public function getMainCatalogInfo() {
        $sql = "SELECT count(*), twitter_type, t2.sortno 
				FROM t_dolphin_cms_index_welcome AS t1 
				INNER JOIN t_dolphin_group_class AS t2 ON t1.twitter_type = t2.class_id 
				WHERE page_type = 54 AND t2.isuse =1 
				GROUP BY twitter_type 
				ORDER BY t2.sortno";
        $this->groupBy = DBGroupCmsHelper::getConn()->read($sql, array());
        $sql = "SELECT t1.data_id, t2.* 
				FROM t_dolphin_cms_index_welcome as t1 
				INNER JOIN t_dolphin_group_class as t2 ON t1.twitter_type = t2.class_id 
				WHERE t1.page_type = 54 AND t2.isuse = 1 
				ORDER BY t2.sortno ASC,t1.twitter_type ASC,t1.sortno ASC";
        $this->groupIds = DBGroupCmsHelper::getConn()->read($sql, array());
		return TRUE;
	}

	public function deleteGroup($groupId) {
		$sql = "DELETE FROM t_dolphin_cms_index_welcome WHERE page_type = 54 AND data_id = {$groupId}";
		DBGroupCmsHelper::getConn()->write($sql, array());
		return TRUE;
	}
}
