<?php
namespace Snake\Package\Goods;

Use Snake\Package\Goods\Helper\DBAttrWeightHelper;

class AttrWeightMapper extends \Snake\Package\Base\Mapper{

	private $enforce  = array('word_id','word_name');	
	private $words = NULL;


    public function __construct($wordId = 0) {
		parent::__construct($this->enforce);
		$this->wordId = $wordId;
	}


	protected function doCreateObject(array $words) {
		$obj = new AttrWeightObject($words);	
		return $obj;
	}


    public function get($sql, $sqlData) {
		$this->words = DBAttrWeightHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
		//var_dump($this->keywords);die('asdfasdf');
	    return $this->words;
    }


	//生成多个推对象的集合
	//private function doCreateCollection(array $keywords) {
	//	$collection = new KeywordsCollection($keywords, $this);
	//	return $collection;
	//}


	//插入到数据库的操作
	public function doInsert($sql, array $sqlData) {
	
	}


	//更新到数据库的操作
	public function doUpdate() {
	}


	function doGet($sql, array $sqlData) {
	
	}			
}

