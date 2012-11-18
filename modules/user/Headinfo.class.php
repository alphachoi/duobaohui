<?php
namespace Snake\Modules\User;

class Headinfo extends \Snake\Libs\Controller {

	public function run() {
		$this->view = $this->userSession;
		return;
	}

}
