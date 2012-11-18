<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Topic\Topic;
Use Snake\Package\Shop\Shop;
Use Snake\Package\Shop\ShopRelation;
Use Snake\Package\Picture\PictureConvert;


class Attribute_shop extends \Snake\Libs\Controller{

	private $wordId = 0;
	private $wordName = '';

	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}

		if (!empty($this->wordId)) {
			$params = array();
			$params['word_id'] = $this->wordId;
			$params['isuse'] = 1;
			$wordInfo = AttrWords::getWordInfo($params, "/*Attribute_shop-zx*/word_name");
			if (empty($wordInfo[0]['word_name'])) {
				return array();
			}
			$wordName = $wordInfo[0]['word_name'];
		}
		else if (!empty($this->wordName)) {
			$wordName = $this->wordName;
		}
		else {
			return array();
		}

		$params = array();	
		$params['topic_sign'] = md5($wordName);
		$topicInfo = Topic::getTopicInfo($params, "*");
		if (!empty($topicInfo)) {

			$params = array();
			$params['topic_id'] = $topicInfo[0]['topic_id'];
			$shopTopicMaps = ShopRelation::getShopTopicMapInfo($params, '/*Attribute_shop-zx*/*');

			if (!empty($shopTopicMaps)) {
				shuffle($shopTopicMaps);
				$shopTopicMaps = array_slice($shopTopicMaps, 0, 10);
				$shopIds = array();
				foreach ($shopTopicMaps as $shopTopicMap) {
					$shopIds[] = $shopTopicMap['shop_id'];	
				}
			}
		}

		if (count($shopIds) < 4) {
			$num = 4 - count ( $shopIds );
			$shops1 = Shop::getRandShops( $num, 100 );
			$shopIdsTmp = array();
			foreach ($shops1 as $shopSingel) {
				$shopIdsTmp[] = $shopSingel['shop_id'];	
			}
			if ($num == 4) {
				$shopIds = $shopIdsTmp;
			}
			else {
				$shopIds = array_merge ( $shopIds, $shopIdsTmp);
			}
		}

		$shopObjs = new Shop();
		$shopObjs->setShopIds($shopIds);
		$shopObjs->setShopExtInfo();
		$shopExtInfos = $shopObjs->getShopExtInfo();


		$responseData = array();
		foreach ($shopExtInfos as $shopId => $shop) {
			$picUrl = "http://logo.taobaocdn.com/shop-logo" . $shop['pic_path']; 
			$responseDataTmp['pic_url'] = $picUrl;
			$responseDataTmp['shop_id'] = $shopId;
			$responseDataTmp['shop_title'] = $shop['shop_title'];
			$responseData[] = $responseDataTmp;
		} 
		if (empty($responseData)) {
			$this->view = array();
			return TRUE;
		}
		shuffle($responseData);
		$responseData = array_slice($responseData, 0, 4);
		$this->view = $responseData;
		return TRUE;	
	}

	private function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}	
		if (!$this->setBrandName()) {
			return FALSE;
		}
		return TRUE;
	}

	private function setWordId() {
		$wordId = intval($this->request->REQUEST['word']);

//		if (empty($wordId)) {
//			self::setError(400, 400, 'empty word');
//			return FAlSE;	
//		}
		if (!empty($wordId) && !is_numeric($wordId)) {
			self::setError(400, 400, 'word is not number');
			return FALSE;
		}
		if ($wordId < 0) {
			self::setError(400, 400, 'bad word');
			return FALSE;
		}
		$this->wordId = $wordId;
		return TRUE;
	}

	private function setBrandName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			$this->wordName = $wordName;	
		}
		return TRUE;
	}
}
