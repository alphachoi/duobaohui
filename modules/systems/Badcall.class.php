<?php
namespace Snake\Modules\Systems;

class Badcall extends \Snake\Libs\Controller {

	public function run() {
		$this->head = 400;
		$this->error_code = 40001;
		$this->message = "Bad Request";
	}

}
