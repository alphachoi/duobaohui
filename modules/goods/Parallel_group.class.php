<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Group\Groups;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/parallel_grouptwitter?tids=74090164
 */
class Parallel_group extends \Snake\Libs\Controller{
	
	public function run() {
		$groupIds = isset($this->request->REQUEST['group']) ? $this->request->REQUEST['group'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";
		$groupTmp = array();
		foreach ($groupIds as $gid) {
			if (!empty($gid)) {
				$groupTmp[] = $gid;
			}	
		}
		$groupIds = $groupTmp;

		if (empty($groupIds)) {
			$this->view = array();
			return TRUE;
		}
		$col = array("group_id","name");
		$groupAssembler = new Groups();
		$groupNames = $groupAssembler->getGroupInfo($groupIds, $col);
		$this->view = $groupNames;
		return TRUE;
	}
}
