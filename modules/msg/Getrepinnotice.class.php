<?php
/**
 Getrepinnotice.class.php
 */

namespace Snake\Modules\Msg;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Msg\GetUserRepinNotice AS GetUserRepinNotice;

class Getrepinnotice extends \Snake\Libs\Controller {

	public function run() {
		$msgHelper = new GetUserRepinNotice($this->userSession['user_id']);
		$msgHelper->getRepinNotice();
		$this->view = $msgHelper->getMsgInfo();

	}
}
