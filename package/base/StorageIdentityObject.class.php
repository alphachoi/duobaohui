<?php
namespace Snake\Package\Base;

class StorageIdentityObject {
    protected $currentfield = NULL;
    protected $fields = array();
	private $col = array();
    private $and = NULL;
    private $enforce = array();

    function __construct($field = NULL, array $enforce = NULL) {
        if (!is_NULL($enforce)) {
            $this->enforce = $enforce;
        }
        if (!is_NULL($field)) {
            $this->field($field);
        }
    }

    function getObjectFields() {
		return $this->col;
        //return $this->enforce;
    }
	function enforce($enforce) {
		$this->enforce();	
	}
	function col(array $col) {
		$this->col = $col;	
	}
    function field($fieldname) {
        if (!$this->isVoid() && $this->currentfield->isIncomplete()) {
            throw new \Exception("Incomplete field");
        }
        $this->enforceField($fieldname);
        if (isset($this->fields[$fieldname])) {
            $this->currentfield = $this->fields[$fieldname]; 
        } 
		else {
            $this->currentfield = new Field($fieldname);
            $this->fields[$fieldname] = $this->currentfield;
        }
        return $this;
    }
	function extra() {
		
	}

    function isVoid() {
        return empty($this->fields);
    }

    function enforceField($fieldname) {
        if (!in_array($fieldname, $this->enforce) && !empty($this->enforce)) {
            $forcelist = implode(', ', $this->enforce);
            throw new \Exception("{$fieldname} not a legal field ($forcelist)");
        }
    }
/*
    function add( $fieldname ) {
        if ( ! $this->isVoid() && $this->currentfield->isIncomplete() ) {
            throw new Exception("Incomplete field");
        } 
        return $this->field( $fieldname );
    }
*/

    function eq($value) {
        return $this->operator("=", $value);
    }

    function lt($value) {
        return $this->operator("<", $value);
    }

    function gt($value) {
        return $this->operator(">", $value);
    }

	function noteq($value) {
		return $this->operator("!=", $value);
	}

	//ie.limit('3,20')
	function limit($value) {
		$this->addExtra("limit", $value);	
		return $this;
	}

	//ie.orderby('goods_id')
	function orderby($value) {
		$this->addExtra("order by", $value);	
		return $this;
	}

	function in($value) {
        return $this->operator("in", $value);
    }

    private function operator($symbol, $value) {
        if ($this->isVoid()) {
            throw new \Exception("no object field defined");
        }
        $this->currentfield->addTest($symbol, $value);
        return $this;
    }

	private function addExtra($op, $name) {
		if (empty($name)) {
			throw new \Exception("$op extra name is empty");
		}
		$this->extra[] = array('operation' => $op, 'name' => $name);	
	}
	function getExtra() {
		return $this->extra;
	}
    function getComps() {
        $ret = array();
        foreach ($this->fields as $key => $field) {
            $ret = array_merge($ret, $field->getComps());
        }
        return $ret;
    }

    function __toString() { 
        $ret = array();
        foreach($this->getComps() as $compdata) {
            $ret[] = "{$compdata['name']} {$compdata['operator']} {$compdata['value']}";
        } 
        return implode(" AND ", $ret);
    }
}    

?>
