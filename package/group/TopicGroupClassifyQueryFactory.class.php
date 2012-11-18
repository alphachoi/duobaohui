<?php
namespace Snake\Package\Group;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class TopicGroupClassifyQueryFactory extends \Snake\Package\Base\QueryFactory {

	private $table = "t_whale_topic_group_classify";

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
        //$values['name'] = $obj->getName();
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
}
?>
