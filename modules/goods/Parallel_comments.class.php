<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Twitter\Twitter;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.31
 * @example curl snake.mydev.com/goods/parallel_comments?tid=74090164
 */
class Parallel_comments extends \Snake\Libs\Controller{

	public function run() {
		$tids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";
		$col = array('twitter_id','twitter_author_uid','twitter_source_tid','twitter_htmlcontent');
		if (!empty($fields)) {
			$col = $fields;
		}
		$twitterAssembler = new Twitter($col);
		$twitters = $twitterAssembler->getTwitterRecentReply($tids);
		$this->view = $twitters;
	}
}
