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
 *   $req = new ItemsGetRequest;
 *	 $req->setPageNo(1);
 *	 $req->setNicks(array('3435','卖家昵称'));
 *	 $req->setFields("num_iid,detail_url,title,nick,volume,pic_url,delist_time,price,score,post_fee,type");
 *   $resp = $c->execute($req);
 *
 * 清单在此結束
 * 查询淘宝客推广商品详细信息
 * @package TaobaoApi 
 * @author weiwang
 * @since 2012.08.01
 */


class ItemsGetRequest extends TaobaoApi{

	/**
	 * 页码
	 *
	 * @var int 
	 * @access private
	 */
	private $pageNo = 0;

	/**
	 * 页数
	 *
	 * @var int 
	 * @access private
	 */
	private $pageSize = 20;

	/**
	 * 昵称
	 *
	 * @var array
	 * @access private
	 */

	private $nicks = array();

	/**
	 *
	 * @return ItemsGetRequest
	 */
    public function __construct() {
		$this->method = "taobao.items.get";
	}

	/**
	 * 设置要获取页码
	 *
	 * @param int $pageNo 页码
	 * @return void
	 * @access public
	 */	
	public function setPageNo($pageNo) {
		$this->pageNo = $pageNo;	
	}

	/**
	 * 设置每页获取的多少
	 *
	 * @param int $pageSize 每页的数量
	 * @return void
	 * @access public
	 */	
	public function setPageSize($pageSize) {
		$this->pageSize = $pageSize;	
	}

	/**
	 * 设置要获取的淘宝nick
	 *
	 * @param array $nicks 參數1
	 * @return void
	 * @access public
	 */	
	public function setNicks(array $nicks) {
		$this->nicks = $nicks;
	}

	/**
	 * 设置淘宝相应api的私有变量
	 *
	 * @return array 
	 * @access public
	 */	
	public function getParamArr(){
		if (empty($nicks)) {
			return array();
		}
		$paramArr = array(
			'nick' => $this->nickname,
			'page_no' => $this->pageNo,
			'page_size' => $this->pageSize,
		    'nicks' => $nicks
		);	
		return $paramArr;
	}

}

