<?php
namespace Snake\Modules\Shop;
//Use Snake\Package\Goods\AttrWords;


class Shop_recommend extends \Snake\Libs\Controller{

	private $wordId = 0;

	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
		$this->view = array(
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',   'word_belong'=>'短裙'),
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦1', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',  'word_belong'=>'短裙'),
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦2', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',  'word_belong'=>'短裙'),
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦3', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',  'word_belong'=>'短裙'),
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦4', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',  'word_belong'=>'短裙'),
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦5', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',  'word_belong'=>'短裙'),
			array( 'shop_id' =>34295, 'shop_title'=>'啦啦啦6', 'pic_url'=>'http://imgtest-dl.meiliworks.com/pic/r/f2/4b/18e3650473962a46138f2630db6f_500_750.jpeg',  'word_belong'=>'短裙')
		);
	}

	private function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}	
		return TRUE;
	}

	private function setWordId() {
		$wordId = intval($this->request->REQUEST['word']);
		if (!empty($wordId) && !is_numeric($wordId)) {
			$this->errorMessage(400, 'word is not number');
			return FALSE;
		}
		if ($wordId < 0) {
			$this->errorMessage(400, 'bad word');
			return FALSE;
		}
		$this->wordId = $wordId;
		return TRUE;
	}
}
