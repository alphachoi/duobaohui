<?php
namespace Snake\Package\Twitterstat;

class TwitterStat {

	private $twitters = array();
	private $twitterStats = array();

	private function __construct() {
	}

	public static function objects() {
		return new TwitterStat();
	}

	public function filter($twitters) {
		if (empty($twitters) || !is_array($twitters)) {
			return FALSE;
		}
		$this->twitters = $twitters;
		return $this;
	}

	public function get() {
		if (empty($this->twitters)) {
			return $this->twitterStats;
		}
		$params = array(
			'twitter_id' => implode(",", $this->twitters),
		);
		return Helper\IOTwitterStatHelper::getClient()->twitter_stat($params); 
	}

}
