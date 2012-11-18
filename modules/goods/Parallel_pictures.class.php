<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Picture\Picture;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/share_main?tid=74090164
 */
class Parallel_pictures extends \Snake\Libs\Controller{

	public function run() {
		$pids = isset($this->request->REQUEST['pids']) ? $this->request->REQUEST['pids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : array();
		//有图片的pictures
		$col = array('picid','n_pic_file','nwidth','nheight');
		if (!empty($fields)) {
			$col = $fields;
		}
		$pictureAssembler = new Picture($col);
		$pictures = $pictureAssembler->getPictureByPids($pids);
		$pictures = \Snake\Libs\Base\Utilities::changeDataKeys($pictures, 'picid');
		$this->view = $pictures;
		return TRUE;
	}
}
