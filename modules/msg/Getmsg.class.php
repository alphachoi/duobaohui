<?php
/**
 Getmsg.class.php
 */

namespace Snake\Modules\Msg;

use Snake\Package\Group\GroupCatalog AS Gcatalog;
use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Msg\Helper\RedisUserNotification AS UserNotification;
use \Snake\Package\Msg\GetUserMsg AS GetUserMsg;

class Getmsg extends \Snake\Libs\Controller {

	public function run() {
		$msgHelper = new GetUserMsg($this->userSession['user_id']);
		$msgHelper->getInfoByUid();
		$msgInfo = $msgHelper->getMsgInfo();
        if ( $msgInfo['fans_num'] || $msgInfo['atme_num'] || $msgInfo['pmsg_num'] || $msgInfo['sysmesg'] || $msgInfo['recommend_num'] || isset($msgInfo['group_new_twitter'])) {
            $msgInfo['total_num'] = intval($msgInfo['fans_num']) + intval($msgInfo['atme_num']) + intval($msgInfo['pmsg_num']) + intval($msgInfo['sysmesg']) + intval($msgInfo['recommend_num']);
        } else {
            $msgInfo['total_num'] = 0;
        }
		$this->view = $msgInfo;

	}
}
