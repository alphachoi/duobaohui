<?php
namespace Snake\Package\Shop;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class ShopTopicMapQueryFactory extends \Snake\Package\Base\QueryFactory {

	private $table = "t_dolphin_shop_topic_map";

    function newSelection(IdentityObject $obj) {
        $fields = implode(',', $obj->getObjectFields());
		//$fields = $obj->getObjectFields();
        $core = "SELECT $fields FROM $this->table";
        list($where, $values) = $this->buildWhere($obj);
        return array($core . " " . $where, $values);
    }
	function newUpdate(DomainObject $obj) { 
		/*
        $id = $obj->getId();
        $cond = null; 
        $values['name'] = $obj->getName();

        if ( $id > -1 ) { 
            $cond['id'] = $id;
        }   
        return $this->buildStatement("t_twitter", $values, $cond);
		 */
    }   	
}
?>
