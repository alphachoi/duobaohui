<?php
namespace Snake\Package\YihaodianApi;

/**
 * @example 
 *
 *   $c = new YihaodianClient;
 *
 *   $req = new OrderGetNew;
 *	 $req->setPageNo(1);
 *	 $req->setTimeInterval($s, $e);
 *   $resp = $c->execute($req);
 *
 * 清单在此結束
 * 查询淘宝客推广商品详细信息
 * @package 一号店Api
 * @author weiwang
 * @author xuanzheng
 */

class OrderGetNew extends YihaodianApi{

	/**
	 * 页码
	 *
	 * @var int 
	 * @access private
	 */
	private $pageNo = 1;

	/**
	 * 页数
	 *
	 * @var int 
	 * @access private
	 */
	private $pageSize = 20;

	/**
	 *
	 * @return ItemsGetRequest
	 */
    public function __construct() {
		$this->method = "order.get.new";
	}

	/**
	 * start time
	 * @var int
	 */
	private $s = NULL;

	/**
	 * end time
	 * @var int
	 */
	private $e = NULL;



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
	 * 设置时间间隔
	 * 
	 * @param int
	 * @param int
	 * @return TRUE
	 */
	public function setTimeInterval($s, $e) {
		$this->s = (int)$s;
		$this->e = (int)$e;
		return TRUE;
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
			'page_no' => $this->pageNo,
//			'page_size' => $this->pageSize,
			's' => $this->s,
			'e' => $this->e,
		);	
		return $paramArr;
	}

}

