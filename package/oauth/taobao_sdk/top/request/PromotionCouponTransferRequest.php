<?php
/**
 * TOP API: taobao.promotion.coupon.transfer request
 * 
 * @author auto create
 * @since 1.0, 2011-08-04 14:19:20.0
 */
class PromotionCouponTransferRequest
{
	/** 
	 * 优惠券编号
	 **/
	private $couponNumber;
	
	/** 
	 * 要赠送的淘宝昵称
	 **/
	private $receiveingBuyerName;
	
	private $apiParas = array();
	
	public function setCouponNumber($couponNumber)
	{
		$this->couponNumber = $couponNumber;
		$this->apiParas["coupon_number"] = $couponNumber;
	}

	public function getCouponNumber()
	{
		return $this->couponNumber;
	}

	public function setReceiveingBuyerName($receiveingBuyerName)
	{
		$this->receiveingBuyerName = $receiveingBuyerName;
		$this->apiParas["receiveing_buyer_name"] = $receiveingBuyerName;
	}

	public function getReceiveingBuyerName()
	{
		return $this->receiveingBuyerName;
	}

	public function getApiMethodName()
	{
		return "taobao.promotion.coupon.transfer";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
