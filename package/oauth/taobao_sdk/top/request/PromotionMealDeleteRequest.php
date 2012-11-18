<?php
/**
 * TOP API: taobao.promotion.meal.delete request
 * 
 * @author auto create
 * @since 1.0, 2011-07-20 16:44:53.0
 */
class PromotionMealDeleteRequest
{
	/** 
	 * 搭配套餐id。
	 **/
	private $mealId;
	
	private $apiParas = array();
	
	public function setMealId($mealId)
	{
		$this->mealId = $mealId;
		$this->apiParas["meal_id"] = $mealId;
	}

	public function getMealId()
	{
		return $this->mealId;
	}

	public function getApiMethodName()
	{
		return "taobao.promotion.meal.delete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
