<?php
namespace Snake\Package\Url;
Use Snake\Package\Url\ShortUrl;

class UrlObject extends \Snake\Package\Base\DomainObject {

    public function __construct($url = array()) {
		$this->row = $url;
	}

	public function setId($urlId) {
		$this->row['url_id'] = $urlId;
	}

	public function getId() {
		return $this->row['url_id'];
	}
	
	private function urlConvert($urlId) {
		$shortUrl = new ShortUrl($urlId); 			
		return $shortUrl->dec2base();
	}

	public function getSourceLink() {
		return $this->row['source_link'];
	}
}
