<?php
/**
 * TOP API: taobao.crm.groups.get request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:28:55.0
 */
class CrmGroupsGetRequest
{
	/** 
	 * 显示第几页的分组，如果输入的页码大于总共的页码数，例如总共10页，但是current_page的值为11，则返回空白页，最小页码为1
	 **/
	private $currentPage;
	
	/** 
	 * 每页显示的记录数，其最大值不能超过100条，最小值为1
	 **/
	private $pageSize;
	
	private $apiParas = array();
	
	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
		$this->apiParas["current_page"] = $currentPage;
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["page_size"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.groups.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
