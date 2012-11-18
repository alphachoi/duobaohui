<?php
namespace Snake\Package\TaobaoApi;

/**
 *
 *
 * {@link http://api.taobao.com/apidoc/api.htm?spm=0.0.0.76.12aSOO&path=cid:4-apiId:11039 [淘宝api网址]}	 
 * 
 * @example 
 *
 *   $c = new TopClient;
 *
 *   一般情况下不需要设置如下两个变量
 *   $c->appkey = appkey;
 *   $c->secretKey = secret;
 *
 *   $req = new UmpPromotionGetRequest;
 *	 $req->setItemId();
 *	 $req->setFields("num_iid,detail_url,title,nick,volume,pic_url,delist_time,price,score,post_fee,type");
 *   $resp = $c->execute($req);
 *
 * 清单在此結束
 * 查询淘宝商品促销详细信息
 * @package TaobaoApi 
 * @author XuanZheng 
 * @since 2012.10.22
 */


class UmpPromotionGetRequest extends TaobaoApi{


	/**
	 * 昵称
	 *
	 * @var array
	 * @access private
	 */

	private $itemId = 0;

	/**
	 * 渠道来源，第三方站点
	 *
	 * @var string
	 * @access private
	 */
	private $channelKey = '';
	

	/**
	 *
	 * @return ItemsGetRequest
	 */
    public function __construct() {
		$this->method = "taobao.ump.promotion.get";
	}

	/**
	 * 设置淘宝商品id
	 *
	 * @param array $nicks 參數1
	 * @return void
	 * @access public
	 */	
	public function setItemId($itemId) {
		$this->itemId = $itemId;
	}

	/**
	 * 设置淘宝相应api的私有变量
	 *
	 * @return array 
	 * @access public
	 */	
	public function getParamArr(){
		if (empty($this->itemId)) {
			return array();
		}
		$paramArr = array(
           'item_id'  => $this->itemId,
		);	
		return $paramArr;
	}

}

