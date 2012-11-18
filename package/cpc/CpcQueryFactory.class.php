<?php
namespace Snake\Package\Cpc;

Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class CpcQueryFactory extends \Snake\Package\Base\QueryFactory {

	const TABLE = "t_dolphin_goods_cpc";

	function getFields(IdentityObject $obj) {
        return implode(',', $obj->getObjectFields());
	}

    function newSelection(IdentityObject $obj) {
        $fields = $this->getFields($obj);
		$forceIndex = $obj->getForceIndex();
		if (!empty($forceIndex)) {
			$forceIndex = " force index($forceIndex) ";
		}
        $core = "SELECT $fields FROM " . self::TABLE . $forceIndex;
        list($where, $values) = $this->buildWhere($obj);
        return array($core . " " . $where, $values);
    }

	function newUpdate(DomainObject $obj) { 
        $id = $obj->getId();
        $cond = null; 
        //$values['name'] = $obj->getName();
		$values = $obj->getRawTwitter();
		//unset($values['twitter_id']);
		//$cond['twitter_id'] = $id;
        return $this->buildStatement(self::TABLE, $values, $cond);
    }   	
}
?>
