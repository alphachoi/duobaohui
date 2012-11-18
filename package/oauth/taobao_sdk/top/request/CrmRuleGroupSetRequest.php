<?php
/**
 * TOP API: taobao.crm.rule.group.set request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:21:17.0
 */
class CrmRuleGroupSetRequest
{
	/** 
	 * 需要添加到规则的分组
	 **/
	private $addGroups;
	
	/** 
	 * 需要删除的分组
	 **/
	private $deleteGroups;
	
	/** 
	 * 规则id
	 **/
	private $ruleId;
	
	private $apiParas = array();
	
	public function setAddGroups($addGroups)
	{
		$this->addGroups = $addGroups;
		$this->apiParas["add_groups"] = $addGroups;
	}

	public function getAddGroups()
	{
		return $this->addGroups;
	}

	public function setDeleteGroups($deleteGroups)
	{
		$this->deleteGroups = $deleteGroups;
		$this->apiParas["delete_groups"] = $deleteGroups;
	}

	public function getDeleteGroups()
	{
		return $this->deleteGroups;
	}

	public function setRuleId($ruleId)
	{
		$this->ruleId = $ruleId;
		$this->apiParas["rule_id"] = $ruleId;
	}

	public function getRuleId()
	{
		return $this->ruleId;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.rule.group.set";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
