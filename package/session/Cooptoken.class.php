<?php
namespace Snake\Package\Session;

class Cooptoken {

	private $validTokens = array(
		'af1bbeca07854055' => 'woxihuan',
		'9cc33f233c15ad8c' => 'qqphoto',
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
