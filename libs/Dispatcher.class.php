<?php
namespace Snake\Libs;

class Dispatcher {

	private $mode = 'json';
	private $request = NULL;
	private $userSession = NULL;
	private $module = NULL;
	private $action = NULL;

	public static function get($mode = 'json') {
		static $singleton = NULL;
		is_null($singleton) && $singleton = new Dispatcher($mode);
		return $singleton;
	}

	private function __construct($mode) {
		$this->mode = $mode;
		$request = \Snake\Libs\Base\HttpRequest::getSnakeRequest();
		$this->request = $request;
		$this->setUser($mode);
	}

	private function setUser($mode) {
		switch ($mode) {
			case 'json' :
				$this->setHornbillUser();
				break;
			case 'captcha' :
				$this->setCaptchaHostUser();
				break;
			case 'ht' :
				$this->setHoutaiHostUser();
				break;
			case 'u' :
				$this->setUHostUser();
				break;
			case 'xml' :
			default :
				$this->setXmlHostUser();
				break;
		}
	}
	private function setHoutaiHostUser() {
		$userSession = new \Snake\Package\Session\HoutaiAction($this->request);
		$this->userSession = $userSession->get_session();
	}

	private function setUHostUser() {
		$uToken = new \Snake\Package\Session\UToken();
		$this->userSession = $uToken->getUser();	
	}

	private function setCaptchaHostUser() {
		$imgToken = new \Snake\Package\Session\ImageToken($this->request);
		$this->userSession = $imgToken->getUser();
	}

	private function setXmlHostUser() {
		$coopToken = new \Snake\Package\Session\Cooptoken($this->request);
		$this->userSession  = $coopToken->getUser();
	}

	private function setHornbillUser() {
        $userSession = new \Snake\Package\Session\UserSession('', $this->request);
        $this->userSession = $userSession->get_session();
	}

	public function dispatch() {
		$request = $this->request;
		$path_args = $request->path_args;

		// first arg is the module's name
		$module = array_shift($path_args);
		empty($module) && $module = 'welcome';
		$this->module = $module;
		
		$action = array_shift($path_args);
		empty($action) && $action = 'main';
		$this->action = $action;

		// pass the control to module's Router class
		$class = '\\Snake\\Modules\\' . ucwords($module) . '\\' . ucwords($action);
		$request->path_args = $path_args;
		if (!class_exists($class)) {
			$class = '\\Snake\\Modules\\Systems\\Badcall';
		}
		$controller = new $class($request, $this->userSession, $this->mode);
		if ($controller->checkStatusValid()) {
			$controller->run();
			$controller->echoView();
		}
	}

	public function udispatch() {
		$request = $this->request;
		$path_args = $request->path_args;
		$this->module = 'target';
		$this->action = 'targeturl';
		$class = '\\Snake\\Modules\\' . ucwords($this->module) . '\\' . ucwords($this->action);
		$controller = new $class($request, $this->userSession, $this->mode);
		if ($controller->checkStatusValid()) {
			$controller->run();
		}
	}

	public function get_request() {
		return $this->request;
	}

	public function get_userSession() {
		return $this->userSession;
	}

	public function get_module() {
		return $this->module;
	}

	public function get_action() {
		return $this->action;
	}
}
