<?php
namespace Snake\Modules\Target;

USE \Snake\Package\User\UserFormat AS UserFormat;
USE \Snake\Package\Target\ShortUrlModel AS ShortUrlModel;

/**
 * 获取短链接接囗
 * GC
 */
class Shorturl extends \Snake\Libs\Controller {
	
	private $url = '';
	private $host = 'http://u.meilishuo.com';

	public function run() {
		if (empty($this->request->REQUEST['longurl'])) {
			$this->setError(400, 40150, 'empty url');
			return FALSE;
		}
		$url = trim($this->request->REQUEST['longurl']);
		$url = base64_decode($url);
		$userFormatObj = new UserFormat();
		if ($userFormatObj->urlFormat($url) === FALSE) {
			$this->setError(400, 40151, 'invalid url');
			return FALSE;
		}


		$urlArray = parse_url($url);
		$this->url = $this->host . $urlArray['path'];	
		if (!empty($urlArray['query'])) {
			$this->url = $this->url . '?' . $urlArray['query'];	
		}
		$this->url = trim($this->url);
		$urlObj = new ShortUrlModel();
		$urlObj->setUrl($this->url);
		$short_url = $urlObj->getShortUrl();
		$this->view = $this->host . '/' . $short_url;
	}
}
