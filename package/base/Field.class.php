<?php
namespace Snake\Package\Base;

class Field {
    protected $name = NULL;
    protected $operator = NULL;
    protected $comps = array();
    protected $incomplete = FALSE;

    function __construct($name) {
        $this->name = $name;
    }

    function addTest($operator, $value) {
        $this->comps[] = array('name' => $this->name, 'operator' => $operator, 'value' => $value);
    }
    function getComps() { 
		return $this->comps; 
	}
    function isIncomplete() { 
		return empty($this->comps); 
	}
    
}

