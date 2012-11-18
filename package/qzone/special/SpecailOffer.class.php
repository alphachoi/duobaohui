<?php

namespace Snake\Package\Qzone\Special;

use \Snake\Package\Qzone\Special\DBSpecialOfferWear;
use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Url\Url;
Use \Snake\Package\Cpc\Cpc;
Use \Snake\Libs\Base\Utilities;
Use \Snake\Package\Twitterstat\TwitterStat;
Use \Snake\Package\Url\ShortUrl;


class SpecailOffer {
	
	private $infos = array();
	private $data = array();

	public function goodsInfoF($offset = 0, $limit = 33){
        $cacheHelper = \Snake\Libs\Cache\Memcache::instance();
        $cacheKey = 'taobao_promotions';
        $data = $cacheHelper->get($cacheKey);
		if (empty($data)) {
			$data = $this->_getInfosDB($offset);
		}
		else {
			$data = array_slice($data, 0, 99);
		}
		$data = $this->_addData($data);
        $this->infos = $this->_clearData($data);
	}

	public function getInfos() {
		return $this->infos;
	}

	private function _getInfosDB($offset) {
		
		$helper = new DBSpecialOfferWear();
		$limit = 33;
		$infos = array();
		for ($i=0; $i < 3; $i++) {
			$offset = $i * $limit;
			$name = "qzone_speciallist_" . $offset;
			$dataOriginal = $helper->getSpecialValue($name);
			$data = json_decode($dataOriginal[0]['value'], TRUE);
			$infos = array_merge($infos, $data);
		}
		return $data;
	}

	private function _addData($data) {
        $fields = array('twitter_id', 'twitter_goods_id', 'twitter_images_id');
        $twitterHelper = new Twitter($fields, array());
		$tids = \Snake\Libs\Base\Utilities::DataToArray($data, 'tid');
		$goodsId = \Snake\Libs\Base\Utilities::DataToArray($data, 'goods_id');
        //$twitterInfos = $twitterHelper->getTwitterByTids($tids);
        $picUrl = $twitterHelper->getPicturesByTids($tids, "r");
		$fields = array('goods_id', 'goods_price', 'goods_url');
		$goodsHelper = new Goods($fields, array());
		$goodsHelper->returnConvertData(FALSE);
        $goodsInfo = $goodsHelper->getGoodsByGids($goodsId);
        $goodsUrl = \Snake\Libs\Base\Utilities::DataToArray($data, 'goods_url');
        $fields = array('source_link', 'click_url', 'url_id');
        $urlHelper = new Url($fields);
        $url = $urlHelper->getUrlsByUrlIds($goodsUrl);
		$urlInfo = array();
		foreach ($url AS $key => $value) {
			$urlId = $url[$key]['url_id'];
			$urlInfo[$urlId] = $url[$key];
		}
		$gInfo = array();
		foreach ($goodsInfo AS $key => $value) {
			$goodsId = $goodsInfo[$key]['goods_id'];
			$gInfo[$goodsId] = $goodsInfo[$key];
		}
		$cpcHelper = new Cpc();
		foreach ($data AS $key => $value) {
			$twitterId = $data[$key]['tid'];
			$urlId = $data[$key]['goods_url'];
			$goodsId = $data[$key]['goods_id'];
			$isCpc = $cpcHelper->isCpc($twitterId);
			if ($isCpc) {
				$shortUrl = new ShortUrl($twitterId);
				$url = MEILISHUO_URL . "/s/" . $shortUrl->dec2base() . "/" . $twitterId . "?frm=qzone_hlt";
			}
			else {
				$shortUrl = new ShortUrl($urlId);
				$url = MEILISHUO_URL . "/u/" . $shortUrl->dec2base() . "/" . $twitterId . "?frm=qzone_hlt";
			}

			$data[$key]['goods_url'] = $urlInfo[$urlId]['click_url'];
			//$data[$key]['goods_url'] = $url;
			$data[$key]['goods_price'] = $gInfo[$goodsId]['goods_price'];
            if (empty($urlInfo[$urlId]['click_url'])) {
              $data[$key]['goods_url'] = $urlInfo[$urlId]['source_link'];
            }
			$data[$key]['pic_url'] = $picUrl[$twitterId]['n_pic_file'];
		}
		return $data;
	}

	private function _clearData($data) {
        $infos = array();
        $infos = $data;
        foreach ($infos AS $key => $value) {
            $urlId = $infos[$key]['goods_url'];
			$time = strtotime($infos[$key]['promotion']['end_time']) - time();
			$h = (int)($time/3600);
			$g = $time%3600;
			$m = (int)($g/60);
			$s = $g%60;
			$infos[$key]['end_time'] = $h . ":" . $m . ":" . $s;
			$infos[$key]['end_time'] = strtotime($infos[$key]['promotion']['end_time']);
			if ($infos[$key]['end_time'] < time()) {
				$infos[$key]['end_time'] = time() + 3600;
			}
            $infos[$key]['item_promo_price'] = $infos[$key]['promotion']['item_promo_price'];
			$infos[$key]['discount'] = 10 * number_format($infos[$key]['item_promo_price']/$infos[$key]['goods_price'], 2);
            unset($infos[$key]['promotion']);
        }
        return $infos;
    }

	private function _setDataInDB($data) {
		

	}

}


