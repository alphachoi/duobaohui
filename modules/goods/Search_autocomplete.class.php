<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Goods\Autocomplete;

/**
 * 搜索智能提醒
 * @package goods
 * @author weiwang
 * @since 2012.10.17
 */
class Search_autocomplete extends \Snake\Libs\Controller{

	public function run() {
		$searchKey = isset($this->request->REQUEST['searchKey']) ? urlencode($this->request->REQUEST['searchKey']) : "";
		$completeRequest = new Autocomplete($searchKey);
		$prompt = $completeRequest->complete();
		$this->view = $prompt;
		return TRUE;
	}

}
