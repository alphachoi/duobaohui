<?php
namespace Snake\Package\Msg;

USE \Snake\Package\Msg\SystemMsg;
/**
 * 更新MAX_SYS_ID操作
 *
 */
class ObsGetUserMsg implements \Snake\Libs\Interfaces\Iobserver {
	public function __construct() {

	}

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Register_actionconnect' :
			case 'Register_action' :
				$msgHelper = new SystemMsg();
				$msgHelper->setSysZero($args['user_id'], 1);
				break;
			default :
				break;
		}
	}

}
