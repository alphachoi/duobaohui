<?php
namespace Snake\Package\Twitter;

Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class TwitterQueryFactory extends \Snake\Package\Base\QueryFactory {

	const TABLE = "t_twitter";

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
	function newStorageMutiRowSelection(IdentityObject $obj) {
		
	}
	function newUpdate(DomainObject $obj) { 
        $id = $obj->getId();
        $cond = null; 
		$values = $obj->getRawTwitter();
        return $this->buildStatement(self::TABLE, $values, $cond);
    }   	
}
?>
