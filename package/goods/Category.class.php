<?php
namespace Snake\Package\Goods;
Use \Snake\Package\Goods\Helper\DBGoodsHelper;
class Category{
	private $table	= 'tb_goods_category';
	public function getCategory($categoryTopId){
		$sql = "SELECT	id AS word_id, name AS word_name, is_red  AS isred 
				FROM	{$this->table}
				WHERE	pid = :pid
		";
		$sqlData = array('pid'=>$categoryTopId);
        $data       = DBGoodsHelper::getConn()->read($sql, $sqlData , FALSE) ;
		return $data;
	}
}
