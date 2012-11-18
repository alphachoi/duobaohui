<?php
namespace Snake\Package\TaobaoApi;

/**
 *
 *
 * {@link http://api.taobao.com/apidoc/api.htm?path=cid:38-apiId:339 [淘宝api网址]}	 
 * 
 * @example 
 *
 *   $c = new TopClient;
 *
 *   一般情况下不需要设置如下两个变量
 *   $c->appkey = appkey;
 *   $c->secretKey = secret;
 *
 *   $req = new TaobaokeItemsDetailGetRequest;
 *   $req->setNumiids(array(11313131,313131344));
 *   $req->setFields("title,nick,pic_url,price,click_url,cid");
 *   $resp = $c->execute($req);
 *
 * 清单在此結束
 * 查询淘宝客推广商品详细信息
 * @package TaobaoApi 
 * @author weiwang
 * @since 2012.08.01
 */

class TaobaokeItemsDetailGetRequest extends TaobaoApi{

	/**
	 * 要获取的淘宝itemids
	 *
	 * @var array 
	 * @access private
	 */
	private $numiids = array();

	/**
	 *
	 * 构造函数，定义了要调用的淘宝api
	 * @return TaobaokeItmesDetailGetRequest 
	 */
    public function __construct() {
		$this->method = "taobao.taobaoke.items.detail.get";
	}

	/**
	 * 设置要获取的淘宝num_iids
	 *
	 * @param array $var1 參數1
	 * @return void
	 * @access public
	 */	
	public function setNumiids(array $numiids) {
		$this->numiids = $numiids;	
	}

	/**
	 * 设置淘宝相应api的私有变量
	 *
	 * @return array 
	 * @access public
	 */	
	public function getParamArr(){
		if (empty($this->numiids)) {
			return array();
		}
		$numiids = implode(",", $this->numiids);
		$paramArr = array(
			'nick' => $this->nickname,
		    'num_iids' => $numiids
		);
		return $paramArr;
	}

}

