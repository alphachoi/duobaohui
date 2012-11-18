<?php
namespace Snake\Package\Goods;

Use Snake\Package\Goods\Helper\DBKeywordsHelper AS DBKeywordsHelper;

class KeywordsMapper extends \Snake\Package\Base\Mapper{

	private $enforce  = array('word_id','word_name');	
	private $keywords = NULL;


    public function __construct($wordId = 0) {
		parent::__construct($this->enforce);
		$this->wordId = $wordId;
	}


	//生成单个推对象
	protected function doCreateObject(array $keywords) {
		$obj = new KeywordsObject($keywords);	
		return $obj;
	}

    public function get($sql, $sqlData) {
		$this->keywords = DBKeywordsHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
		//var_dump($this->keywords);die('asdfasdf');
	    return $this->keywords;
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

