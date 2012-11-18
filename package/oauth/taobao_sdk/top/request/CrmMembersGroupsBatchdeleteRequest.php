<?php
/**
 * TOP API: taobao.crm.members.groups.batchdelete request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:28:18.0
 */
class CrmMembersGroupsBatchdeleteRequest
{
	/** 
	 * 买家的Id集合
	 **/
	private $buyerIds;
	
	/** 
	 * 会员需要删除的分组
	 **/
	private $groupIds;
	
	private $apiParas = array();
	
	public function setBuyerIds($buyerIds)
	{
		$this->buyerIds = $buyerIds;
		$this->apiParas["buyer_ids"] = $buyerIds;
	}

	public function getBuyerIds()
	{
		return $this->buyerIds;
	}

	public function setGroupIds($groupIds)
	{
		$this->groupIds = $groupIds;
		$this->apiParas["group_ids"] = $groupIds;
	}

	public function getGroupIds()
	{
		return $this->groupIds;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.members.groups.batchdelete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
