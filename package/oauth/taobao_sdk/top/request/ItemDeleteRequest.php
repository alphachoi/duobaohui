<?php
/**
 * TOP API: taobao.item.delete request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:23.0
 */
class ItemDeleteRequest
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
		return "taobao.item.delete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
