<?php
namespace Snake\Package\Target;

USE \Snake\Package\Target\Helper\RedisShortUrl AS RedisShortUrl;
USE \Snake\Package\Target\Helper\DBTargetHelper AS DBTargetHelper;
/**
 * 短链接操作
 *
 */
class ShortUrlModel {

	private $shortUrl = '';
	private $longUrl = '';	
	private $table = 't_dolphin_shorturl_map';

	public function __construct() {
	}

	public function setUrl($longUrl) {
		if (empty($longUrl)) {
			return FALSE;
		}
		$this->longUrl = trim($longUrl);
		$this->shortUrl = $this->gShortUrl($this->longUrl);
		if ($this->storeUrl($this->shortUrl, $this->longUrl)) {
			$this->storeUrlDB($this->shortUrl, $this->longUrl);	
		}
	}

	public function getShortUrl() {
		return $this->shortUrl;
	}

	public function getLongUrl($shortUrl) {
		if (empty($shortUrl)) {
			return FALSE;
		}	
		$longUrl = RedisShortUrl::urlExists($shortUrl);
		if (empty($longUrl)) {
			$longUrl = $this->getLongUrlDB($shortUrl);	
			if (!empty($longUrl)) {
				RedisShortUrl::addShortUrl($shortUrl, $longUrl);	
			}
		}
		return $longUrl;
	}

	/**
	 * 生成短链接
	 * @param $longUrl
	 */
	private function gShortUrl($longUrl) { 
		if (empty($longUrl)) {
			return '';
		}
		$base32 = "abcdefghijklmnopqrstuvwxyz012345"; 
		$hex = md5($longUrl); 
		$first = hexdec($hex[0]);
		$start = $first % 4;
		if ($start > 0) {
			$start = ($start * 8) - 1;
		}
		$subHex = substr($hex, $start, 8); 
		$int = 0x3FFFFFFF & (1 * ('0x' . $subHex));
		$out = ''; 
		for($j = 0; $j < 6; $j++) { 
			$val = 0x0000001F & $int; 
			$out .= $base32[$val]; 
			$int = $int >> 5; 
		}
		return $out;
	}

	private function storeUrl($shortUrl, $longUrl) {
		if (empty($shortUrl) || empty($longUrl)) {
			return FALSE;
		}	
		$exists = RedisShortUrl::urlExists($shortUrl);
		if ($exists === FALSE) {
			RedisShortUrl::addShortUrl($shortUrl, $longUrl);	
			return TRUE;
		}
		return FALSE;
	}

	private function storeUrlDB($shortUrl, $longUrl) {
		if (empty($shortUrl) || empty($longUrl)) {
			return FALSE;
		}	
		$sqlComm = "INSERT INTO {$this->table} (shorturl, longurl) VALUES (:shorturl, :longurl)";
		$sqlData = array('shorturl' => $shortUrl, 'longurl' => $longUrl);
		$result = DBTargetHelper::getConn()->write($sqlComm, $sqlData);
		return $result;
	}

	private function getLongUrlDB($shortUrl) {
		if (empty($shortUrl)) {
			return FALSE;
		}
		$sqlComm = "SELECT longurl FROM {$this->table} WHERE shorturl=:short";
		$sqlData = array('short' => $shortUrl);
		$result = DBTargetHelper::getConn()->read($sqlComm, $sqlData);
		if (!empty($result)) {
			return $result[0]['longurl'];
		}
		return '';
	}
}
