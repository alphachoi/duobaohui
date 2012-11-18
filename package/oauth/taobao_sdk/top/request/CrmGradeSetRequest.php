<?php
/**
 * TOP API: taobao.crm.grade.set request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:27:44.0
 */
class CrmGradeSetRequest
{
	/** 
	 * 升级到下一个级别的需要的交易额,必须全部填写.例如1000,2000,3000，其中1000表示普通会员升级到高级会员需要达到1000的交易额。至尊VIP为最高等级，不需要填写。会员等级越高，所需交易额必须越高。
	 **/
	private $amount;
	
	/** 
	 * 升级到下一个级别的需要的交易量,必须全部填写. 以逗号分隔,例如100,200,300，其中100表示普通会员升级到高级会员需要100笔交易。至尊VIP为最高等级，不需要填写。会员等级越高，交易量必须越高。
	 **/
	private $count;
	
	/** 
	 * 会员级别折扣率。会员等级越高，折扣必须越低。
	 **/
	private $discount;
	
	/** 
	 * 会员等级,必须全部填写。用逗号分隔。买家会员级别 1：普通会员 2 ：高级会员 3：VIP会员 4：至尊VIP
	 **/
	private $grade;
	
	private $apiParas = array();
	
	public function setAmount($amount)
	{
		$this->amount = $amount;
		$this->apiParas["amount"] = $amount;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setCount($count)
	{
		$this->count = $count;
		$this->apiParas["count"] = $count;
	}

	public function getCount()
	{
		return $this->count;
	}

	public function setDiscount($discount)
	{
		$this->discount = $discount;
		$this->apiParas["discount"] = $discount;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function setGrade($grade)
	{
		$this->grade = $grade;
		$this->apiParas["grade"] = $grade;
	}

	public function getGrade()
	{
		return $this->grade;
	}

	public function getApiMethodName()
	{
		return "taobao.crm.grade.set";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
