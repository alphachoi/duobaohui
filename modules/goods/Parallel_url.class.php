<?php
namespace Snake\Modules\Goods;
//Use \Snake\Package\Group\GroupTwitters;
Use \Snake\Package\Url\Url;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/parallel_grouptwitter?tids=74090164
 */
class Parallel_url extends \Snake\Libs\Controller{
	
	public function run() {
		$urlIds  = isset($this->request->REQUEST['url_ids']) ? $this->request->REQUEST['url_ids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";
		$col = array('source_link','url_id');
		if (!empty($fields)) {
			$col = $fields;
		}
		$urlAssembler = new Url($col);
		$urls = $urlAssembler->getUrlsByUrlIds($urlIds);
		$urls = \Snake\Libs\Base\Utilities::changeDataKeys($urls, 'url_id');
		$this->view = $urls;
	}
}
