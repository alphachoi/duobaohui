<?php
namespace Snake\Package\User;

USE \Snake\Libs\Base\Task AS Task;

class ObsAvarter implements \Snake\Libs\Interfaces\Iobserver {

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Register_actionconnect' :
				$this->setOutsiteAvatar($args['user_id'], $args['avatar']);
				break;
			default :
				break;	
		}
	}	

	private function setOutsiteAvatar($userId, $avatar) {
		$frame = 'dolphin';
		$filename = './getUserAvatarOutSite.php';
		$params = array($userId, urlencode($avatar));
		Task::getTask($frame)->setFile($filename)->setParams($params)->run();	
	}
}	
