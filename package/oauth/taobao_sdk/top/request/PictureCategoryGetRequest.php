<?php
/**
 * TOP API: taobao.picture.category.get request
 * 
 * @author auto create
 * @since 1.0, 2011-07-29 20:31:25.0
 */
class PictureCategoryGetRequest
{
	/** 
	 * 取二级分类时设置为对应父分类id
取一级分类时父分类id设为0
取全部分类的时候不设或设为-1
	 **/
	private $parentId;
	
	/** 
	 * 图片分类ID
	 **/
	private $pictureCategoryId;
	
	/** 
	 * 图片分类名，不支持模糊查询
	 **/
	private $pictureCategoryName;
	
	/** 
	 * 分类类型,fixed代表店铺装修分类类别，auction代表宝贝分类类别，user-define代表用户自定义分类类别
	 **/
	private $type;
	
	private $apiParas = array();
	
	public function setParentId($parentId)
	{
		$this->parentId = $parentId;
		$this->apiParas["parent_id"] = $parentId;
	}

	public function getParentId()
	{
		return $this->parentId;
	}

	public function setPictureCategoryId($pictureCategoryId)
	{
		$this->pictureCategoryId = $pictureCategoryId;
		$this->apiParas["picture_category_id"] = $pictureCategoryId;
	}

	public function getPictureCategoryId()
	{
		return $this->pictureCategoryId;
	}

	public function setPictureCategoryName($pictureCategoryName)
	{
		$this->pictureCategoryName = $pictureCategoryName;
		$this->apiParas["picture_category_name"] = $pictureCategoryName;
	}

	public function getPictureCategoryName()
	{
		return $this->pictureCategoryName;
	}

	public function setType($type)
	{
		$this->type = $type;
		$this->apiParas["type"] = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getApiMethodName()
	{
		return "taobao.picture.category.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
