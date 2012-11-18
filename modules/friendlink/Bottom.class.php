<?php
namespace Snake\Modules\Friendlink;

use \Snake\Package\Friendlink\FriendlinkFactory;

class Bottom extends \Snake\Libs\Controller {

	public function run() {
		$this->main();
	}

	public function main() {
		$conditions = array( 
			'show_type' => 'bottom-home', 
			'stat' => 1
		);
		$friendlinkFactory = new FriendlinkFactory($conditions);

		$col = 'link_text, link_to';
		$friendlinks = $friendlinkFactory->get_friendlink_list($col);

		$this->view = $friendlinks;
	}
}
