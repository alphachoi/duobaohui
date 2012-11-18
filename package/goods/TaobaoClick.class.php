<?php
namespace Snake\Package\Goods;
Use Snake\Package\TaobaoApi\TopClient;
Use Snake\Package\TaobaoApi\TaobaokeItemsDetailGetRequest;

/**
 *
 * 淘宝客相关的操作
 *
 * @author xuanzheng
 * @package goods
 * @todo class的名字起的有点不好
 */

class TaobaoClick {

	/**
	 * id of taobao
	 * @var int
	 */
	private $taobaoId = 0;

	/**
	 * source url
	 * @var string
	 */
	private $url = '';

	/**
	 * click url
	 * @var string
	 */
	private $clickUrl = '';

	/**
	 * taobao id 的pattern
	 * @var string
	 */
	private $patternOfTaobaoId = "/id=([0-9]*)/i";

	/** 
	 * 通过URL获取淘宝id
	 * @param string
	 * @return int
	 */
	public function setIdByUrl($url = '') {
		preg_match ( $this->patternOfTaobaoId, $url, $matches );
		if (empty($matches[1])) {
			return FALSE;	
		}
		$this->taobaoId = $matches[1];
		//return TRUE;
		return $matches[1];
	}

//	/**
//	 * 获取clickUrl
//	 * @param array
//	 * @return array 
//	 */
//	public function getClickUrlByIds($ids = array()) {
//		if (empty($ids)) {
//			return FALSE;	
//		}	
//
//		$req = new TaobaokeItemsDetailGetRequest;
//		$req->setNumiids($ids);
//		$req->setFields("title,nick,pic_url,price,click_url,cid,num_iid");
//
//		$c = new TopClient;
//		$taobaokeItem = $c->execute($req);
//
//		if (empty($taobaokeItem['taobaoke_item_details']['taobaoke_item_detail'])) {
//			return FALSE;
//		}
//		$taobaoClickUrl = $taobaokeItem['taobaoke_item_details']['taobaoke_item_detail'];
//		
//		$clickUrls = array();
//		if (is_array($taobaoClickUrl) && !isset($taobaoClickUrl['click_url'])) {
//			foreach ($taobaoClickUrl as $clickUrl) {
//				if (isset ( $clickUrl['click_url'] )) {
//					$clickUrls[$clickUrl['item']['num_iid']] = !empty($clickUrl['click_url']) ? $clickUrl['click_url'] : '';
//				}
//			}
//		}
//		else if(isset($taobaoClickUrl['click_url'])) {
//			$clickUrls[$taobaoClickUrl['item']['num_iid']] = !empty($taobaoClickUrl['click_url']) ? $taobaoClickUrl['click_url'] : '';
//		}
//		return $clickUrls;	
//	}

	/**
	 * 获取clickUrl
	 * @param int
	 * @return string
	 */
	public function getClickUrlById($id = 0){
		if (empty($id)) {
			return FALSE;	
		}	

		$req = new TaobaokeItemsDetailGetRequest;
		$req->setNumiids(array($id));
		$req->setFields("title,nick,pic_url,price,click_url,cid,num_iid");

		$c = new TopClient;
		$taobaokeItem = $c->execute($req);

		if (empty($taobaokeItem['taobaoke_item_details']['taobaoke_item_detail'])) {
			return FALSE;
		}
		$taobaoClickUrl = $taobaokeItem['taobaoke_item_details']['taobaoke_item_detail'];
		if(isset($taobaoClickUrl['click_url'])) {
			$clickUrl = !empty($taobaoClickUrl['click_url']) ? $taobaoClickUrl['click_url'] : '';
		}
		return $clickUrl;
	}




}
