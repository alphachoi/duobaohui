<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Group\GroupTwitters;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/parallel_grouptwitter?tids=74090164
 */
class Parallel_grouptwitter extends \Snake\Libs\Controller{
	
	public function run() {
		$tids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";
		$col = array("group_id","twitter_id");
		if (!empty($fields)) {
			$col = $fields;
		}
		$groupAssembler = new GroupTwitters();
		$groupIds = $groupAssembler->getGroupTwitter($tids, $col);
		$this->view = $groupIds;
		return TRUE;
	}
}
