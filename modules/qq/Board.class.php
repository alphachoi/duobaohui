<?php
namespace Snake\Modules\Qq;


use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\Twitter\Twitter;
use \Snake\Package\Open\QQPhoto;


class Board extends \Snake\Libs\Controller {

	public function run() {

		if (!$this->init()) {
			return FALSE;
		}
		$boardId = $this->boardId;
		$qqHelper = new QQPhoto($boardId);
		$xml = $qqHelper->getBoard();
		$this->view = $xml;
		return TRUE;
	}

	private function init() {
		$this->boardId = $this->request->GET['bid'];
		if (!isset($this->boardId) && empty($this->boardId)) {
			return FALSE;
		}
		return TRUE;
	}

}
