<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\Groups AS Groups;

class Check_name extends \Snake\Libs\Controller {

	private $name = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$groupHelper = new Groups();
		$result = $groupHelper->checkGroupNameExists($this->name);
		if ($result == TRUE) {
			$this->view = array(
				"response" => "Name existed",
				"code" => 0
				);
			return TRUE;
		}
		else {
			$this->view = array(
				"response" => "Name didn't exist",
				"code" => 1
				);
			return TRUE;
		}
	}

	private function _init() {
		$this->name = $this->request->REQUEST['name'];
		if (empty($this->name)) {
			$this->setError(400, 40305, 'required parameter is empty');
			return FALSE;
		}
		return TRUE;
	}

}
