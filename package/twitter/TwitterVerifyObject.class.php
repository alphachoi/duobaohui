<?php
namespace Snake\Package\Twitter;

class TwitterVerifyObject extends \Snake\Package\Base\DomainObject{


    public function __construct($twitterVerify = array()) {
		$this->row = $twitterVerify;
	}

}
