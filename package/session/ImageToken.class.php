<?php
namespace Snake\Package\Session;

class ImageToken {
	
	private $validTokens = array(
		'asde39ad9' => 'captcha_register',
		'9adfc893s' => 'captcha',
	);

	private $request = NULL;
	private $token = NULL;
	private $user = NULL;

	public function __construct($request) {
		$this->request = $request;
		$this->getToken();
		$this->getUser();
	}

	private function getToken() {
        if (!empty($this->request->GET['token'])) {
            $this->token = $this->request->GET['token'];
        }   
    }   

    public function getUser() {
        if (!empty($this->validTokens[$this->token])) {
            $this->user = $this->validTokens[$this->token];
        }   
        return $this->user;
    }  	
}
