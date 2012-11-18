<?php
namespace Snake\Modules\User;

Use Snake\Package\User\User					     AS User;
Use Snake\Package\User\UserObject				 AS UserObject;
Use Snake\Package\User\UserRelation				 AS UserRelation;
Use Snake\Package\User\UserConnect			     AS UserConnect;

class User_check_qzonefans extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $_qzone_pop_times = 1;

	public function run()  {
		$this->userId = $this->userSession['user_id'];
		//$this->userId = 7580188; //5246989;
		//$userId = 1155095;
		if (empty($this->userId)) {
			 $this->header = 400;
			 $this->view = array(
				  "code" => 400,
				  "message" => "empty user_id"
			  );
			 return FALSE;
		}
		if ($this->userId > 0) {
			$userConnect = new UserConnect();
			$result = $userConnect->checkQzoneIsFans($this->userId);
		}
		$this->view = $result;

	}
}
