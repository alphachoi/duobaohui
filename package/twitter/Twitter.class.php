<?php
namespace Snake\Package\Twitter;
Use \Snake\Package\Twitter\Helper\DBTwitterHelper;

class Twitter {
	private static $instance = NULL;
	private function __construct() {}	
	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new self(); 	
		}
		return self::$instance;
	}
    /*  
    * 测试数据
    */
    public function getTwitterList($cataId, $offset, $limit){
		$offset			= (int)$offset;
		$limit			= (int)$limit;
		$cataId			= (int)$cataId;
		$where			= '';
		if($cataId!=0){
			$where = " WHERE goods_category_topid='{$cataId}' ";
			
		}
        $sql            = "SELECT * FROM tb_twitter $where  limit {$offset}, {$limit} ";
        $twitterD       = DBTwitterHelper::getConn()->read($sql, array() , FALSE) ;
		foreach($twitterD as $k=>$v){
			$twitterIds[] = $v['twitter_id'];
		}
		if(isset($twitterIds)){
			$strTwitterIds	= implode(',', $twitterIds);
			$sql            = "SELECT * FROM tb_twitter_count WHERE twitter_id IN({$strTwitterIds})";
			$twitterCountD  = DBTwitterHelper::getConn()->read($sql, array() , FALSE);

			$sql            = "SELECT * FROM tb_goods WHERE twitter_id IN({$strTwitterIds})";
			$goodsD         = DBTwitterHelper::getConn()->read($sql, array() , FALSE);

			foreach($twitterD as $k=>$v){
				$data['tInfo'][$k] = $v + $twitterCountD[$k];
				$data['tInfo'][$k]['ginfo'] = $goodsD[$k];
				$data['tInfo'][$k]['uinfo'] = array(
										'user_id'   => 11783173,
										'nickname'  => '玛丽奥',
										'avatar_c'  => 'http://imgtest.meiliworks.com/ap/c/e1/5d/0709e46d36ec861aabed3359fa4b_334_334.jpg',
										'is_taobao_seller' => 0
									);
				$data['tInfo'][$k]['comments'] = array();
			}
		}
        $data['totalNum'] = 3000;

        return $data;

    }
}
