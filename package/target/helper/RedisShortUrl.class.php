<?php
namespace Snake\Package\Target\Helper;

class RedisShortUrl extends \Snake\Libs\Redis\Redis {

	public static function addShortUrl($shortUrl, $longUrl) {
		if (empty($shortUrl) || empty($longUrl)) {
			return FALSE;
		}
		$longUrl = base64_encode(serialize($longUrl));
		return self::set($shortUrl, $longUrl);
	}

	public static function urlExists($shortUrl) {
		if (empty($shortUrl)) {
			return FALSE;
		}
		$result = self::get($shortUrl);
		return unserialize(base64_decode($result));
	}
}
