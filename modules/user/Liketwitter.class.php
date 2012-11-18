<?php
namespace Snake\Modules\User;

use \Snake\Package\User\User;

class Liketwitter extends \Snake\Libs\Controller {

	private $user_id = NULL;
	private $pageSize;
	private $pigeNumber;

	public function run() {
		$this->pageSize = 40;
		$this->pigeNumber = 0;
		if (isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id'])) {
			$this->user_id = intval($this->request->REQUEST['user_id']);
		}
		if (isset($this->request->REQUEST['page_size']) && is_numeric($this->request->REQUEST['page_size'])) {
			$this->pageSize = intval($this->request->REQUEST['page_size']);
		}
		if (isset($this->request->REQUEST['page_number']) && is_numeric($this->request->REQUEST['page_number'])) {
			$this->pageNumber = intval($this->request->REQUEST['page_number']);
		}

		if (empty($this->user_id)) {
			$this->head = 400;
			$this->view = array(
				'code' => 400,
				'message' => 'empty userId',
			);
			return;
		}

		$this->main();
	}

	public function main() {
		$user = new User();
		$start = $this->pageSize * $this->pageNumber;
		$limit = $this->pageSize;
		$result = $user->getUserLikeTwitters($this->user_id, $start, $limit);

		if($result['status']) {
			$this->view['total'] = $result['total'];
			$this->view['twitter_ids'] = $result['data'];
		}
	}

}
