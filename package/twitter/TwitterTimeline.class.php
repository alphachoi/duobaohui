<?php
namespace Snake\Package\Twitter;

class TwitterTimeline implements \Snake\Libs\Interfaces\Iobserver {

	private $twitter = array();

	public function __construct() {
	}

	private function updateTimeLine() {
		\Snake\Package\Timeline\Timeline::newPosterTwitter($this->twitter);
	}

	public function onChanged($sender, $args) {
		$this->twitter = $args['twitter'];
		if (!in_array($this->twitter['twitter_show_type'], array(2, 7, 8, 9))) {
			return TRUE;
		}

		$this->twitter = $args['twitter'];
		
		$this->updateTimeLine();
	}

}
