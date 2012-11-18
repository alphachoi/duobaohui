<?php
namespace Snake\Package\Session;

class UToken {
	
	private $user = NULL;

	public function __construct() {
		$this->user = 'u';
	}

    public function getUser() {
		return $this->user;
    }  	
}
