<?php
namespace Snake\Package\TaobaoApi;
/**
 *
 * 1. taobaoApi网址 
 *
 *	 //http://api.taobao.com/apidoc/api.htm?path=cid:1-apiId:1
 *	 http://api.taobao.com/apidoc/api.htm?spm=0.0.0.37.d9218d&path=cid:9-apiId:68
 * 
 * 2. example 
 *
 *   $c = new TopClient;
 *
 *   一般情况下不需要设置如下两个变量
 *   $c->appkey = appkey;
 *   $c->secretKey = secret;
 *
 *   //$req = new UserGetRequest;
 *   $req = new ShopGetRequest;
 *   $req->setFields("sid,cid,nick,title,desc,bulletin,pic_path,created,modified,shop_score,remain_count,all_count,used_count");
 *   $resp = $c->execute($req);
 *
 * 清单在此結束
 * 获取单个店铺信息 
 * @package TaobaoApi 
 * @author weiwang
 *
 */

class ShopGetRequest extends TaobaoApi{

	/**
	 *
	 * 构造函数，定义了要调用的淘宝api
	 * @return ShopGetRequest 
	 */
    public function __construct() {
		//$this->method = "taobao.users.get";
		//$this->method = "taobao.user.seller.get ";
		$this->method = "taobao.shop.get";
	}

	/**
	 * 设置要获取的淘宝nick
	 *
	 * @param array $nicks 參數1
	 * @return void
	 * @access public
	 */	
	public function setNick($nick) {
		$this->nick = $nick;
	}

	/**
	 * 设置淘宝相应api的私有变量
	 *
	 * @return array 
	 * @access public
	 */
	public function getParamArr(){
		$paramArr = array(
			'nick' => $this->nick,
		);	
		return $paramArr;
	}

}

