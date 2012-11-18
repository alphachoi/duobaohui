<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class GoodsObject extends \Snake\Package\Base\DomainObject{

    public function __construct($goods = array()) {
		$this->row = $goods;
	}

    public function getRow($dataConvert = TRUE) {
		if ($dataConvert) {
			$this->row['goods_price'] = $this->getGoodsPrice();
		}
        return $this->row;
    }   
	public function getGoodsUrl() {
		return $this->row['goods_url'];
	}
	public function getGoodsId() {
		return $this->row['goods_id'];
	}
	public function getShareGoodsPrice() {
		if ($this->row['goods_price'] == 0) {
			return $this->row['goods_price'];
		}
		return '¥' . $this->row['goods_price']; 
	}
	public function getGoodsPrice($decimals = 2) {
		return '¥' . number_format($this->row['goods_price'], $decimals); 
	}

	public function getId() {
		return $this->row['goods_id'];
	}
	public function getGoodsPicUrl() {
		return $this->row['goods_pic_url'];
	}
	public function getGoodsTitle() {
		return $this->row['goods_title'];
	}
}
