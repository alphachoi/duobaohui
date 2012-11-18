<?php
namespace Snake\Package\Manufactory;

Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Libs\Base\MultiClient;
Use \Snake\Package\Picture\Picture;
Use \Snake\Package\Picture\PictureConvert;
use \Snake\Package\Goods\AttrWords;
use \Snake\Package\Goods\Attribute;

class Attrmix {

	protected $attrIds = array();
	protected $attrWords = array();
	protected $attrMix = array();
	protected $attrWordsInfos = array();
	protected $noMixAttrIds = array();
	protected $twitterIds = array();
	protected $twitterInfos = array();
	protected $picIds = array();
	protected $picUrls = array();
	protected $mark = "attr";

	public function __construct($attrIds = array(), $mark = "attr") {
		$this->attrIds = $attrIds;
		$this->mark = $mark;
	}

	public function getAttrMix() {
		$this->getAttrWords();
		$this->getAttrInfos();
		$this->getMixPic();
		$this->getEachAttrPic();
		return $this->attrMix;
	}

	private function getAttrWords() {
		$this->attrWords = AttrWords::getWordInfo(array('word_id' => $this->attrIds));
		$this->attrWords = \Snake\Libs\Base\Utilities::changeDataKeys($this->attrWords, 'word_id');
		foreach ($this->attrIds as $key => $attrId) {
			$this->attrMix[$attrId] = array(
				'attr_id' => $attrId,
				'link' => MEILISHUO_URL . '/attr/show/' . $attrId,
				'name' => $this->attrWords[$attrId]['word_name'],
				'number' => 0,
			);
		}
	}

	private function getAttrInfos() {
		$attrInfos = Attribute::getTwittersByAttrIds($this->attrWords, 0 ,9, 'weight');
		if (!empty($attrInfos)) {
			$this->attrWordsInfos = $attrInfos;
		}	
		foreach ($this->attrWordsInfos as $attrId => $attrWordsInfo) {
			$this->attrMix[$attrId]['number'] = $attrWordsInfo['total'];
		}
	}

	private function getMixPic() {
		$client = MultiClient::getClient(0);
		$attrRequests = array();
		foreach ($this->attrIds as $attrId) {
			$attrRequest = array(
				'multi_func' => 'pop_group_twitter',
				'method' => 'GET',
				'group_id' => $attrId,
				'type' => $this->mark,
				'self_id' => 0, 
			);
			$attrRequests[] = $attrRequest;
		}
		$attrPicUrls = $client->router($attrRequests);
		foreach ($this->attrIds as $key => $attrId) {
			if (!empty($attrPicUrls[$key]['pic'])) {
				$this->attrMix[$attrId]['mix_pic'] = $attrPicUrls[$key]['pic'];
			}
			else {
				$this->attrMix[$attrId]['mix_pic'] = "";
				$this->noMixAttrIds[] = $attrId;
			}
		}
	}

	private function getEachAttrPic() {
		if (empty($this->noMixAttrIds)) {
			return FALSE;
		}
		$this->getTwitterIds();
		$this->getPicIds();
		$this->getPicUrls();
		foreach ($this->noMixAttrIds as $attrId) {
			if (!isset($this->attrWordsInfos[$attrId])) {
				$this->attrMix[$attrId]['pics'] = array();
				continue;
			}
			foreach ($this->attrWordsInfos[$attrId]['tid'] as $tid) {
				$imageId = $this->twitterInfos[$tid]['twitter_images_id'];
				$picObj = new PictureConvert($this->picUrls[$imageId]['n_pic_file']);
				$this->attrMix[$attrId]['pics'][] = $picObj->getPictureC();
			}
		}

		return $this->attrMix;
	}

	private function getTwitterIds() {
		foreach ($this->noMixAttrIds as $attrId) {
			if (!isset($this->attrWordsInfos[$attrId])) {
				continue;
			}
			foreach ($this->attrWordsInfos[$attrId]['tid'] as $tid) {
				$this->twitterIds[] = $tid;
			}
		}
	}

	private function getPicIds() {
		$twitterObj = new Twitter(array('twitter_id', 'twitter_images_id'));
		$twitterInfos = $twitterObj->getTwitterByTids($this->twitterIds);
		$this->twitterInfos = \Snake\Libs\Base\Utilities::changeDataKeys($twitterInfos, 'twitter_id');
		$this->picIds = \Snake\Libs\Base\Utilities::DataToArray($twitterInfos, 'twitter_images_id');
	}
	
	private function getPicUrls() {
		$picObj = new Picture(array('picid', 'n_pic_file'));
		$picInfos = $picObj->getPictureByPids($this->picIds);
		$this->picUrls = \Snake\Libs\Base\Utilities::changeDataKeys($picInfos, 'picid');
	}

}
