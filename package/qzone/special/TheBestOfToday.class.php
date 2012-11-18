<?php

namespace Snake\Package\Qzone\Special;

use \Snake\Package\Qzone\Special\DBSpecialOfferWear;
use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Url\Url;
Use \Snake\Package\Url\ShortUrl;
Use \Snake\Libs\Base\Utilities;
Use \Snake\Package\Twitterstat\TwitterStat;
Use \Snake\Package\Cpc\Cpc;


class TheBestOfToday {

    private $DBhelper = NULL;
    private $twitterInfos = NULL;
    private $comments = NULL;
    private $infos = NULL;
    const pageSize = 20;

    public function __construct() {
        $this->DBhelper = new DBSpecialOfferWear();
    }
/**
* v1.0
**/
    public function goodsInfoF() {
        $this->_getInfos();
        $this->_getAllGoodsInfo();
        $this->_getComments();
		//$this->_getTwitterLikes();
        $this->_assembleInfo();
    }

    public function getPerfectInfos() {
        return $this->infos;
    }

    public function setPerfectInfos($infos) {
        $this->infos = $infos;
        return TRUE;
    }

    private function _getInfos() {
        $this->infos = $this->DBhelper->getAllGoods();
        $this->twitterIds = array_keys($this->infos);
        return TRUE;
    }

    private function _getAllGoodsInfo() {
        if (empty($this->twitterIds)) {
            return FALSE;
        }
        $fields = array('twitter_id', 'twitter_goods_id', 'twitter_images_id');
        $twitterHelper = new Twitter($fields, array());
        $this->twitterInfos = $twitterHelper->getTwitterByTids($this->twitterIds);
        $picUrl = $twitterHelper->getPicturesByTids($this->twitterIds, "l");
        $goodsId = \Snake\Libs\Base\Utilities::DataToArray($this->twitterInfos, 'twitter_goods_id');
        $fields = array('goods_id', 'goods_price', 'goods_url');
        $goodsHelper = new Goods($fields, array());
		$goodsHelper->returnConvertData(FALSE);
        $goodsInfo = $goodsHelper->getGoodsByGids($goodsId);
		$goodsUrl = \Snake\Libs\Base\Utilities::DataToArray($goodsInfo, 'goods_url');
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
		$i = 1;
        foreach ($this->twitterInfos AS $key => $value) {
            $twitterId = $this->twitterInfos[$key]['twitter_id'];
			$goodsId = $this->twitterInfos[$key]['twitter_goods_id'];
			$urlId = $gInfo[$goodsId]['goods_url'];
			$isCpc = $cpcHelper->isCpc($twitterId);
			if ($isCpc) {
				$shortUrl = new ShortUrl($twitterId);
				$url = MEILISHUO_URL . "/s/" . $shortUrl->dec2base() . "/" . $twitterId . "?frm=qzone_hlt_" . $i;
			}
			else {
				$shortUrl = new ShortUrl($urlId);
				$url = MEILISHUO_URL . "/u/" . $shortUrl->dec2base() . "/" . $twitterId . "?frm=qzone_hlt_" . $i;
			}
            $this->twitterInfos[$key]['pic_url'] = $picUrl[$twitterId]['n_pic_file'];
            $this->twitterInfos[$key]['goods_price'] = $gInfo[$goodsId]['goods_price'];
			//$this->twitterInfos[$key]['goods_url'] = $url; 
			$this->twitterInfos[$key]['goods_url'] = $urlInfo[$urlId]['click_url'];
			$i++;
			if (empty($urlInfo[$urlId]['click_url'])) {
				$this->twitterInfos[$key]['goods_url'] = $urlInfo[$urlId]['source_link'];
			}
        }
        return TRUE;
    }

    private function _getComments() {
        if (empty($this->twitterIds)) {
            return FALSE;
        }
        $comments = $this->DBhelper->getGoodsComments($this->twitterIds);
        foreach ($comments AS $key => $value) {
            $comments[$key]['commentator_img'] = Utilities::getPictureUrl($comments[$key]['commentator_img'], 'r');
        }
		foreach ($this->twitterIds AS $twitterId) {
			foreach ($comments AS $key => $value) {
				if ($comments[$key]['twitter_id'] == $twitterId) {
					$this->comments[$twitterId][] = $comments[$key];
					unset($comments[$key]);
				}
			}
		}
        return TRUE;
    }

	private function _getTwitterLikes() {
		$twitterLikes = TwitterStat::objects()->filter($twitterLikes)->get();	
		print_r($twitterLikes);exit;
	}

    private function _assembleInfo(){
        if (empty($this->twitterIds)) {
            return FALSE;
        }
        foreach ($this->twitterInfos AS $key => $value) {
            $twitterId = $this->twitterInfos[$key]['twitter_id'];
            $this->infos[$twitterId]['twitter_goods_id'] = $this->twitterInfos[$key]['twitter_goods_id'];
            $this->infos[$twitterId]['pic_url'] = $this->twitterInfos[$key]['pic_url'];
            $this->infos[$twitterId]['twitter_goods_id'] = $this->twitterInfos[$key]['twitter_goods_id'];
            $this->infos[$twitterId]['goods_price'] = $this->twitterInfos[$key]['goods_price'];
            $this->infos[$twitterId]['goods_url'] = $this->twitterInfos[$key]['goods_url'];
            $this->infos[$twitterId]['likes'] = rand(300,600);
            $this->infos[$twitterId]['comments'] = $this->comments[$twitterId];
            $this->infos[$twitterId]['is_promoting'] = "0"; 
        }
        $infos = $this->infos;
        $this->infos = array_values($infos);
        return TRUE;
    }
}
