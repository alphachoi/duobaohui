<?php
namespace Snake\Package\Picture;

class PictureObject extends \Snake\Package\Base\DomainObject{

    public function __construct($picture = array()) {
		$this->row = $picture;
	}
     
	public function getWidth() {
		return $this->row['nwidth'];	
	}
	public function getHeight() {
		return $this->row['nheight'];	
	}
	public function getPid() {
		return $this->row['picid'];
	}
	public function getPicFile() {
		return $this->row['n_pic_file'];	
	}
}
