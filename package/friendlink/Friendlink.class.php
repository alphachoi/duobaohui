<?php
namespace Snake\Package\Friendlink;

class Friendlink {
	
	private $friendlink = array();

	public function __set($name, $value) {
		$this->friendlink[$name] = $value;
	}

	public function __get($name) {
		if(isset($this->friendlink[$name])) {
			return $this->friendlink[$name];
		}
		return NULL;
	}

}
