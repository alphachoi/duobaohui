<?php
namespace Snake\Package\Cms;

Use Snake\Package\Cms\Helper\DBCmsIndexWelcomeHelper;

class CmsIndexWelcomeMapper extends \Snake\Package\Base\Mapper {
	private $enfore =  array('id');
	private $data = NULL;

	public function __construct() {
		parent::__construct($this->enfore);
	}

	private function doCreateObject(array $data) {
		$obj = new CmsIndexWelcomeObject($data);
		return $obj;
	}

	public function get($sql, $sqlData) {
		$this->data = DBCmsIndexWelcomeHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);	
		return $this->data;
	}

	public function doInsert($sql, array $sqlData) {
	
	}

	public function doUpdate() {
	}


	public function doGet($sql, array $sqlData) {
	
	}







}
