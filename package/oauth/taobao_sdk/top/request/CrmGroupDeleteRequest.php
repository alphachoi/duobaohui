<?php
/**
 * TOP API: taobao.crm.group.delete request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:34:10.0
 */
class CrmGroupDeleteRequest
{
	/** 
	 * 要删除的分组id
	 **/
	private $groupId;
	
	private $apiParas = array();
	
	public function setGroupId($groupId)
	{
		$this->groupId = $groupId;
		$this->apiParas["group_id"] = $groupId;
	}

	public function getGroupId()
	{
		return $this->groupId;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.group.delete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
