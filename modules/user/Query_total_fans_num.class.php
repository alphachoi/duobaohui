<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 得到meilishuo qzone weibo txweibo粉丝总数量信息
 *
 **/

Use Snake\Package\User\UserConnect;

class Query_total_fans_num extends \Snake\Libs\Controller {

	public function run() {
		$result = UserConnect::getInstance()->getTotalFansNum();
		$this->view = array('total_num' => $result);
	}

    /**
     * 初始化变量
     **/
    private function _init() {
    }
}
