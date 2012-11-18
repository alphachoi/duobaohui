<?php
/**
 GetNewShareNotice.class.php
 */

namespace Snake\Modules\Msg;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Msg\Helper\UserHomePosterTimeline AS UserHomePosterTimeline;
use \Snake\Package\Msg\GetNewShareMsg AS GetNewShareMsg;

class Getnewsharenotice extends \Snake\Libs\Controller {

	public function run() {
		$msgHelper = new GetNewShareMsg($this->userSession['user_id'], $this->request->COOKIE['santorini_mm']);
		$msgHelper->getUserNewShareMsg();
		$num = array();
		$num['num'] = $msgHelper->getMsgInfo();
		$this->view  = $num;
	}
}
