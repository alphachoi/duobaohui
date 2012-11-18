<?php
/**
 * TOP API: taobao.wlb.wlborder.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:43.0
 */
class WlbWlborderGetRequest
{
	/** 
	 * 物流宝订单编码
	 **/
	private $wlbOrderCode;
	
	private $apiParas = array();
	
	public function setWlbOrderCode($wlbOrderCode)
	{
		$this->wlbOrderCode = $wlbOrderCode;
		$this->apiParas["wlb_order_code"] = $wlbOrderCode;
	}

	public function getWlbOrderCode()
	{
		return $this->wlbOrderCode;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.wlborder.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
