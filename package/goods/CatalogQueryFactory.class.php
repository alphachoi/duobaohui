<?php
namespace Snake\Package\Goods;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObject;

class CatalogQueryFactory extends \Snake\Package\Base\QueryFactory {

	private $table = "t_dolphin_catalog";

    function newSelection(IdentityObject $obj) {
        $fields = implode(',', $obj->getObjectFields());
		//$fields = $obj->getObjectFields();
        $core = "SELECT $fields FROM $this->table";
        list($where, $values) = $this->buildWhere($obj);
        return array($core . " " . $where, $values);
    }
	function newUpdate(DomainObject $obj) { 
		
        $id = $obj->getId();
        $cond = null; 
        $values = $obj->getRow();

        if (!empty($id)) { 
            $cond['catalog_id'] = $id;
			unset($values['catalog_id']);
        }   
        return $this->buildStatement($this->table, $values, $cond);
		 
    }   	
}
?>
