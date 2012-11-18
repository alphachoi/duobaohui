<?php
namespace Snake\Package\Base;

abstract class DomainObject{

	//数据库中的一行纪录
	protected $row = array();

    public function __construct() {
	}

	public function getRow() {
		return $this->row;	
	}
}
