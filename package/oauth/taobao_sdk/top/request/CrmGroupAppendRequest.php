<?php
/**
 * TOP API: taobao.crm.group.append request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:34:19.0
 */
class CrmGroupAppendRequest
{
	/** 
	 * 添加的来源分组
	 **/
	private $fromGroupId;
	
	/** 
	 * 添加的目标分组
	 **/
	private $toGroupId;
	
	private $apiParas = array();
	
	public function setFromGroupId($fromGroupId)
	{
		$this->fromGroupId = $fromGroupId;
		$this->apiParas["from_group_id"] = $fromGroupId;
	}

	public function getFromGroupId()
	{
		return $this->fromGroupId;
	}

	public function setToGroupId($toGroupId)
	{
		$this->toGroupId = $toGroupId;
		$this->apiParas["to_group_id"] = $toGroupId;
	}

	public function getToGroupId()
	{
		return $this->toGroupId;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.group.append";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
