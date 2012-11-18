<?php
namespace Snake\Package\TaobaoApi;

/**
 *
 *
 * {@link http://api.taobao.com/apidoc/api.htm?path=cid:5-apiId:47 [淘宝api网址]}	 
 * 
 * @example 
 *
 *   $c = new TopClient;
 *
 *   一般情况下不需要设置如下两个变量
 *   $c->appkey = appkey;
 *   $c->secretKey = secret;
 *
 *   $req = new TradeGetRequest;
 *   $req->setTradeId(113131313131344);
 *   $req->setFields("seller_nick");
 *   $resp = $c->execute($req);
 *
 * 清单在此結束
 * 获取单笔交易的部分信息 
 * 查询淘宝客推广商品详细信息
 * @package TaobaoApi 
 * @author weiwang
 * @since 2012.08.01
 */

class TradeGetRequest extends TaobaoApi{

	/**
	 * 要获取的交易id
	 *
	 * @var bigint 
	 * @access private
	 */
	private $tradeId = 0;

	/**
	 * 淘宝的session
	 *
	 * @var string 
	 * @access private
	 */
	private $session = "";

	/**
	 *
	 * @return TradeGetRequest 
	 */
    public function __construct() {
		$this->method = "taobao.trade.get";
	}

	/**
	 * 设置淘宝的交易id
	 *
	 * @param bigint $tradeId 参数1
	 * @return void
	 * @access public
	 */	
	public function setTradeId($tradeId) {
		$this->tradeId = $tradeId;
	}

	/**
	 * 设置淘宝的session
	 *
	 * @param string $session 参数1
	 * @return void
	 * @access public
	 */	 
	public function setSession($session) {
		$this->session = $session;	
	}

	/**
	 * 设置淘宝相应api的私有变量
	 *
	 * @return array 
	 * @access public
	 */	 
	public function getParamArr(){
		if (empty($this->tradeId) || empty($this->session)) {
			return array();
		}
		$paramArr = array(
			'nick' => $this->nickname,
		    'tid'  =>  $this->tradeId,
		    'session' => $this->session
		);	
		return $paramArr;
	}

}

