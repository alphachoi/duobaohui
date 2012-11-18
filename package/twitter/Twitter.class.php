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
    public function getTwitterList($cataTopId, $cataId, $offset, $limit){
		$data			= array();
		$offset			= (int)$offset;
		$limit			= (int)$limit;
		$cataId			= (int)$cataId;
		$cataTopId		= (int)$cataTopId;
		$where			= '';

		$where	= ' WHERE 1=1 ';
		if($cataTopId!=0){
			$where .= " AND goods_category_topid='{$cataTopId}' ";
		}
		if($cataId!=0){
			$where .= " AND goods_category_id='{$cataId}' ";
		}
		// count
        $sql            = "SELECT count(*) AS totalNum FROM tb_twitter $where  ";
        $totalNumD		= DBTwitterHelper::getConn()->read($sql, array() , FALSE) ;
		$data['totalNum'] = $totalNumD[0]['totalNum'];

		// list
        $sql            = "SELECT * FROM tb_twitter $where  limit {$offset}, {$limit} ";
        $twitterD       = DBTwitterHelper::getConn()->read($sql, array() , FALSE) ;
		foreach($twitterD as $k=>$v){
			$twitterIds[]	= $v['twitter_id'];
			$arrGoodsId[]	= $v['twitter_goods_id'];
			$arrUserId[]	= $v['twitter_author_uid'];
		}
		$strGoodsId		= implode(',', $arrGoodsId);
		$strUserId		= implode(',', $arrUserId);
		if(isset($twitterIds)){
			$strTwitterIds	= implode(',', $twitterIds);
			$sql            = "SELECT * FROM tb_twitter_count WHERE twitter_id IN({$strTwitterIds})";
			$twitterCountD  = DBTwitterHelper::getConn()->read($sql, array() , FALSE);

			$sql            = "SELECT * FROM tb_goods WHERE goods_id IN({$strGoodsId})";
			$goodsD         = DBTwitterHelper::getConn()->read($sql, array() , FALSE);
			foreach($goodsD as $v){
				$ginfo[$v['goods_id']] = $v;
			}

			$sql            = "SELECT user_id, nickname, avatar_c,is_taobao_seller FROM tb_user_account WHERE user_id IN({$strUserId})";
			$userD          = DBTwitterHelper::getConn()->read($sql, array() , FALSE);
			foreach($userD as $v){
				$uinfo[$v['user_id']] = $v;
			}

			foreach($twitterD as $k=>$v){
				$data['tInfo'][$k] = array();
				$data['tInfo'][$k] = $v + $twitterCountD[$k];
				$data['tInfo'][$k]['ginfo'] = $ginfo[$v['twitter_goods_id']];
				$data['tInfo'][$k]['uinfo'] = $uinfo[$v['twitter_author_uid']];
				$data['tInfo'][$k]['comments'] = array();
			}
		}

        return $data;
    }
}
