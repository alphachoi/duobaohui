<?php
namespace Snake\Package\Twitter;

Use \Snake\package\group\GroupTwitters;

class TwitterGroup implements \Snake\Libs\Interfaces\Iobserver {
	private $twitter = array();
	private $hasPicture = 0;

	public function __construct() {
	}

	private function updateGroupTwitter() {
		$GroupTwitter = new GroupTwitters();
		$GroupTwitter->operationInsertTwitter($this->twitter['group_id'], $this->twitter['twitter_id'], $this->twitter['twitter_author_uid'], $this->hasPicture, $this->twitter['twitter_show_type']);
	}

	public function onChanged($sender, $args) {
		if (isset($args['twitter']['group_id']) && !empty($args['twitter']['group_id']) && !in_array($this->twitter['twitter_show_type'], array(2, 5, 7, 8))) {
			$this->twitter = $args['twitter'];
		}
		else {
			return TRUE;
		}

		if (!empty($this->twitter['twitter_images_id'])) {
			$this->hasPicture = 1;
		}

		$this->updateGroupTwitter();
	}
}
