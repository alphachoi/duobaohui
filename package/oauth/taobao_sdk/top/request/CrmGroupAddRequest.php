<?php
/**
 * TOP API: taobao.crm.group.add request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:29:14.0
 */
class CrmGroupAddRequest
{
	/** 
	 * 分组名称，每个卖家最多可以拥有100个分组
	 **/
	private $groupName;
	
	private $apiParas = array();
	
	public function setGroupName($groupName)
	{
		$this->groupName = $groupName;
		$this->apiParas["group_name"] = $groupName;
	}

	public function getGroupName()
	{
		return $this->groupName;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.group.add";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
