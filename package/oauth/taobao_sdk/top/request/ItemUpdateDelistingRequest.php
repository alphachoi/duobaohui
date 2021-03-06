<?php
/**
 * TOP API: taobao.item.update.delisting request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:26.0
 */
class ItemUpdateDelistingRequest
{
	/** 
	 * 商品数字ID，该参数必须
	 **/
	private $numIid;
	
	private $apiParas = array();
	
	public function setNumIid($numIid)
	{
		$this->numIid = $numIid;
		$this->apiParas["num_iid"] = $numIid;
	}

	public function getNumIid()
	{
		return $this->numIid;
	}

	public function getApiMethodName()
	{
		return "taobao.item.update.delisting";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
