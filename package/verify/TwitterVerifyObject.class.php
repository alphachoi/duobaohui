<?php
namespace Snake\Package\Verify;
use \Snake\Libs\Base\Face;

class TwitterVerifyObject extends \Snake\Package\Base\DomainObject{

    public function __construct($twitterVerify = array()) {
		$this->row = $twitterVerify;
	}
     
}
