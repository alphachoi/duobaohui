<?php
namespace Snake\Package\Twitter;

Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class TwitterVerifyQueryFactory extends \Snake\Package\Base\QueryFactory {

	const TABLE = "t_dolphin_twitter_verify";

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
        //$id = $obj->getId();
        $cond = null; 
		$values = $obj->getRow();
        return $this->buildStatement(self::TABLE, $values, $cond);
    }   	
}
?>
