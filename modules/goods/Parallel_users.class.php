<?php
namespace Snake\Modules\Goods;

Use \Snake\Package\User\User;
/**
 * @package goods
 * @author weiwang
 * @since 2012.09.03
 * @example curl 
 */
class Parallel_users extends \Snake\Libs\Controller{

	public function run() {
		$uids = isset($this->request->REQUEST['uids']) ? $this->request->REQUEST['uids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";

		//获取用户信息
		$col = array("nickname","user_id","avatar_c","is_taobao_seller");
		if (!empty($fields)) {
			$col = $fields;
		}
		$userAssembler = new User();
		$users = $userAssembler->getUserInfos($uids, $col);

		$this->view = $users;
		return TRUE;
	}

}
