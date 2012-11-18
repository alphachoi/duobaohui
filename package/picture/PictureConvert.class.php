<?php
namespace Snake\Package\Picture;

class PictureConvert {
	//图片地址
	private $picUrl = "";

    public function __construct($picUrl) {
		$this->picUrl = $picUrl;
	}
	public function getPictureM() {
		return $this->getPictureUrl("m");	
	}
	public function getPictureE() {
		return $this->getPictureUrl("e");	
	}

	public function getPicture($type) {
		return $this->getPictureUrl($type);	
	}

	public function getPictureT() {
		return $this->getPictureUrl("t");
	}

	public function getPictureJ() {
		return $this->getPictureUrl("j");
	}

	public function getPictureR() {
		return $this->getPictureUrl("r");
	}


	public function getPictureC() {
		return $this->getPictureUrl("c");
	}


	public function getPictureL() {
		return $this->getPictureUrl("l");
	}

	public function getPictureG() {
		return $this->getPictureUrl("g");
	}

	public function getPictureB() {
		return $this->getPictureUrl("b");
	}


	public function getPictureO() {
		return $this->getPictureUrl("_o");
	}


	private function getPictureUrl($thumbType){
		if(empty($this->picUrl) || empty($thumbType)){
			return '';
		}
		return \Snake\Libs\Base\Utilities::getPictureUrl($this->picUrl, $thumbType);
	}

}
