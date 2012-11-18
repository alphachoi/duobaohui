<?php
namespace Snake\Package\Group;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class TopicGroupTwitterQueryFactory extends \Snake\Package\Base\QueryFactory {

	private $table = "t_whale_topic_group_twitter";

    function newSelection(IdentityObject $obj) {
        $fields = implode(',', $obj->getObjectFields());
		//$fields = $obj->getObjectFields();
        $core = "SELECT $fields FROM $this->table";
        list($where, $values) = $this->buildWhere($obj);
        return array($core." ".$where, $values);
    }
	function newUpdate(DomainObject $obj) { 
        $insert = $obj->getInsert();
        $cond = null; 
		$values = $obj->getFields();
        if (empty($insert)) { 
            $cond = $obj->getCondition();
        }   
        return $this->buildStatement($this->table, $values, $cond);
    }   	
	function delete(DomainObject $obj) {
		$cond = $obj->getCondition();
		if (empty($cond)) {
			return FALSE;
		}
		return $this->buildDelete($this->table, $cond);
	}

	/*function buildDelete($table, $cond) {
		$sql = "DELETE FROM " . $table . " WHERE ";
		$i = 0;
		foreach ($cond AS $key => $value) {
			if ($i != 0) {
				$sql .= " AND ";
			}
			$sql .= $key . "=:" . $key;
			$i++;
		}
		$result[] = $sql;
		$result[] = $cond;
		return $result;
	}*/
}
?>
