<?php
namespace Snake\Modules\Target;

USE \Snake\Package\Target\ShortUrlModel AS ShortUrlModel;
USE \Snake\Package\Target\CommonTarget AS CommonTarget;

/**
 * 跳转分发
 * GuoChao
 */
class Targeturl extends \Snake\Libs\Controller {

	private $agent = '';
	private $requestUri = '';
	private $isWap = FALSE;
	private $destUrl = '';

	//const movTemp = "HTTP/1.1 302 Moved Temporarily";
	//const movPer = "HTTP/1.1 301 Moved Permanently";

	public function run() {

		if ($this->initAgent() === FALSE) {
			return FALSE;
		}
		$this->initRequestUri();
		$this->initTarget();
		$this->initDestUrl();

		header("HTTP/1.1 302 Moved Temporarily"); 
		header("Location: {$this->destUrl}");
	}

	private function initAgent() {
		$agent = $this->request->agent; //empty error
		if (empty($agent)) {
			return FALSE;
		}
		$this->agent = $agent;	
		return TRUE;
	}

	/**
	 * 扩展短链
	 */
	private function initRequestUri() {
		$this->requestUri = $this->request->uri;
	}

	private function initTarget() {
		if (strpos($this->agent, 'iPhone') !== FALSE || strpos($this->agent, 'ndroid') !== FALSE) {
			$this->isWap = TRUE;
		}
		return TRUE;
	}

	private function initDestUrl() {
		$host = 'http://www.meilishuo.com';
		if ($this->isWap === TRUE) {
			$host = 'http://wap.meilishuo.com';
			$commonUrlObj = new CommonTarget($this->requestUri);	
			$commonUrlObj->targetMap();
			$this->requestUri = $commonUrlObj->getTargetUri();
		}
		$this->destUrl = $host . $this->requestUri;
	}
}
