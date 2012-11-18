<?php
namespace Snake\Package\mall;

/**
 * @author guoshuaitan@meilishuo.com
 * @since 2012-06-28
 * @version 1.0
 */

Use Snake\Libs\DB\MakeSql;
Use Snake\Package\Mall\Helper\DBMallHelper;

/**
 * Mall class 
 *
 * 品牌用户的信息
 * @author guoshuaitan@meilishuo.com
 * @since 2012-06-28
 * @version 1.0
 */

class Mall {	
	/**
	 * @var string table_name
	 * $access private
	 */
	private $table = 't_dolphin_mall_profile';
	
	public function __construct() {}

	/**
	 * 判断用户是否为品牌商
	 * @return boolean
	 * @access public
	 */
	public function isMall($user_id, $master = false, $hashkey ='') {
		if(empty($user_id)) {
			return FALSE;	
		}	
		$param = array('table' => $this->table,
					   'colum' => 'uid',
					   'where' => array('uid' => $user_id)
		);
		$sqlData = array();
		$result = $this->getData($param, $sqlData, $master, $hashkey);
		return	empty($result) ? FALSE : TRUE;
	}
	/**
	 * 获得品牌商信息
	 * @return info
	 * param $userid
	 */
	 public function getMallInfoById($user_id, $colum = '*', $master = false , $hashkey='') {
		 if (empty($user_id)) {
			 return false;
		 }
		 $param = array('table' => $this->table,
		 				'colum' => $colum,
						'where' => array('uid' => $user_id)
		 );
		 $sqlData = array();
		 $result = $this->getData($param, $sqlData, $master, $hashkey);
		 return $result;
	}
 	/*   
     * 获取搜索词对应的品牌商
     */
    function getMallBySearchWord($searchWord, $colum = '*', $isuse = 1, $fromMaster = false,$hashKey = ''){ 
		$param = array('table' => 't_dolphin_user_settop',
					   'colum' => $colum,
					   'where' => array('isuse' => $isuse,'search_word' => ':_search_word')
		);
		$sqlData['_search_word'] = $searchWord;
		$result = $this->getData($param, $sqlData, $master, $hashKey);
        return $result;
    }    
		
	/**
	 * 获取mall的相关信息
	 * @return array
	 * @access public 
	 */
	public function getData($param, $sqlData = array(), $master = false, $hashkey ="") {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType('select', $param);
		$result = DBMallHelper::getConn()->read($sql, $sqlData, $master, $hashkey);
		return $result;
	}
}
