<?php
/**
 * TOP API: taobao.hotel.type.add request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:53.0
 */
class HotelTypeAddRequest
{
	/** 
	 * 酒店id。必须为数字
	 **/
	private $hid;
	
	/** 
	 * 房型名称。长度不能超过30
	 **/
	private $name;
	
	private $apiParas = array();
	
	public function setHid($hid)
	{
		$this->hid = $hid;
		$this->apiParas["hid"] = $hid;
	}

	public function getHid()
	{
		return $this->hid;
	}

	public function setName($name)
	{
		$this->name = $name;
		$this->apiParas["name"] = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getApiMethodName()
	{
		return "taobao.hotel.type.add";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
