<?php
namespace Snake\Package\Goods;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class PopularItemsQueryFactory extends \Snake\Package\Base\QueryFactory {

	const TABLE = "t_dolphin_popular_items";

	function getFields(IdentityObject $obj) {
		return implode(',', $obj->getObjectFields());
	} 

	function newSelection(IdentityObject $obj) {
		$fields = implode(',', $obj->getObjectFields());
		//$fields = $obj->getObjectFields();
		$core = "SELECT $fields FROM " . self::TABLE;
		list($where, $values) = $this->buildWhere($obj);
		return array($core . " " . $where, $values);
	}
	function newUpdate(DomainObject $obj) { 
	}   	
}
?>
