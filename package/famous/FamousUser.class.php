<?php
namespace Snake\Package\Famous;

/**
 * 达人页面相关sql查询操作
 * @author yishuliu@meilishuo.com
 * @since 2012-07-06
 * @version 1.0
 */

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\User\Helper\CacheUserHelper;
Use \Snake\Package\User\User;
Use \Snake\Package\Famous\Helper\DBFamousHelper;
Use \Snake\Package\User\Helper\DBUserHelper;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Package\Spam\SpamUser;
Use \Snake\Libs\Cache\Memcache;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-07-06
 * @version 1.0
 */

class FamousUser {

	private $superUserParams = array('s_id', 'data_id', 'data_type', 'page_type', 'msg', 'imgurl', 'sortno', 'ctime', 'operatorid', 'user_type');
	private $superUserType = array('id', 'type_name', 'sortno', 'page_type');
	private static $instance;
	private $no_cache = TRUE;
	private $darenProperty = array(19 => '媒体', 20 => '品牌', 21 => '福利');

    /** 
     * @return Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new self(); 
        }   
        return self::$instance;
    }   
   
    private function __construct() {
    }   

	public function getSuperTypeByName($type_name, $params = array('id', 'type_name', 'sortno', 'page_type')) {
		$str = implode(',', $params);
		$type_name = trim($type_name);
		$sql = "SELECT {$str} FROM t_dolphin_cms_showlist_type WHERE type_name = '{$type_name}'";
		//print_r($sql);die('##');
		$result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
		$noKeyResult = array();
		foreach ($result as $key => $value) {
			$noKeyResult[] = $result[$key]['id'];
		}
		return $noKeyResult;
	}

	/** 
     * 根据达人类别选出相应类别的达人
     * @author yishuliu@meilishuo.com
     * @param $pageType int 页面位置(100:美丽说首页；200:我的首页；201:4个置顶达人；202:达人列表；300:挑衣服；400:当红好店；500:美丽Q&A；600:美丽一起团)
     * @param $userType int 达人类型
     * @param $dataType int 达人类型
     * @param $params array 需要的达人信息(nickname需要在t_dolphin_user_profile中取)
     * @param $paramsExt array 包括项:start limit 倒序输出 必须参数
     * @return array 
     */  
	public function getSuperUserByType($userType = 0, $start = 0, $length = 0, $pageType = 202, $dataType = 1, $params = array('data_id', 'data_type', 'page_type', 'msg', 'imgurl', 'sortno', 'user_type', 'img_height')) {
		$str = implode(',', $params);
		if (empty($str)) {
			return FALSE;
		}
        $sqlComm = "SELECT {$str} FROM t_dolphin_cms_showlist WHERE page_type =:_page_type AND data_type =:_data_type";
		if ($userType == 0) {
			$sqlComm .= " AND user_type in (1,2,3,5)";
			$sqlData = array('_page_type' => $pageType, '_data_type' => $dataType);
		}
		else {
			$sqlComm .= " AND user_type =:_user_type";
			$sqlData = array('_user_type' => $userType, '_page_type' => $pageType, '_data_type' => $dataType);
		}
		$sqlComm .= " AND imgurl is not null AND imgurl != '' ORDER BY sortno ASC, ctime DESC limit {$start}, {$length}";
        $result = array();
        $result = DBFamousHelper::getConn()->read($sqlComm, $sqlData, FALSE, 'data_id');

		if (empty($result)) {
			return FALSE;
		}
		$userIds = array_keys($result);
		$user = new User();
		$userInfos = $user->getUserInfos($userIds, array('nickname', 'verify_icons', 'verify_msg'));

		//merge两次查询结果
		foreach ($result as $key => $value) {
			$result[$key]['nickname'] = $userInfos[$key]['nickname'];
			$result[$key]['verify_icons'] = $userInfos[$key]['verify_icons']; 
			$result[$key]['verify_msg'] = $userInfos[$key]['verify_msg'];
			$extraMsg = $user->assembleMsg($userInfos[$key], $key);
			foreach ($extraMsg as $item => $evalue) {
				$result[$key][$item] = $evalue;
			}
		}
		//把数组转化成默认键值的数组
		$noKeyResult = array();
		foreach ($result as $key => $value) {
			$noKeyResult[] = $result[$key];
		}
		return $noKeyResult;
	}

	public function getCmsShowList($params, $cols = '*', $orderBy = 'sortno') {
        $sqlComm = "SELECT {$cols} FROM t_dolphin_cms_showlist ";
        $where = ''; 
        $sqlData = array();
        if (!empty($params['page_type'])) {
            $where .= " AND page_type=:_pageType";
            $sqlData['_pageType'] = $params['page_type'];
        }   

        if (!empty($params['data_type'])) {
            $where .= " AND data_type=:_dataType";
            $sqlData['_dataType'] = $params['data_type'];
        }   
        if (!empty($params['user_type'])) {
            $where .= " AND user_type=:_userType";
            $sqlData['_userType'] = $params['user_type'];
        }   
        if (!empty($where)) {
            $where = substr($where, 4); 
            $sqlComm .= ' WHERE '. $where;
        }   
        if (!empty($orderBy)) {
            $sqlComm .= " ORDER BY ".$orderBy;
        }   
        if (isset($params['limit'])) {
            $sqlComm .= ' LIMIT '. $params['limit'];
        }   
        $result = array();
        $result = DBFamousHelper::getConn()->read($sqlComm, $sqlData);
        return $result;
    }   

	//查询符合条件的所有达人totalnum
	public function getCmsListTotal($userType) {
		if (!empty($userType)) {
            $sql = "SELECT count(distinct(data_id)) as totalNum FROM t_dolphin_cms_showlist WHERE data_type = 1 and page_type = 202 and user_type ={$userType} and imgurl is not null and imgurl != ''";
        }   
        else {
            $sql = "SELECT count(distinct(data_id)) as totalNum FROM t_dolphin_cms_showlist WHERE data_type = 1 and page_type = 202 and user_type in (1,2,3,5) and imgurl is not null and imgurl != ''";          
		}
        $result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
        return $result[0]['totalNum'];
	}

	public function getFamousUids($userType) {
		if ($userType == 'editor') {
			//array('all' => 0, 'jiepai' => 1, 'cosmetic' => 2, 'fashion' =>
			//3, 'editor' => 4, 'prof' => 5);
            $sql = "SELECT data_id FROM t_dolphin_cms_showlist WHERE data_type = 1 and page_type = 202 and user_type = 4";
        }   
        elseif ($userType == 'pinkV') {
            $sql = "SELECT data_id FROM t_dolphin_cms_showlist WHERE data_type = 1 and page_type = 202 and user_type in (1,2,3,5)";          
		}
        $result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
        return $result;
	}

	public function getHeaderRecommendList($limit) {
		$cacheHelper = Memcache::instance();
		$cacheKey = "FamousUser:getHeaderRecommendList";
		$result = $cacheHelper->get($cacheKey);

		if ($this->no_cache || empty($result)) {
			$sql = "SELECT msg, data_id, imgurl, sortno FROM t_dolphin_cms_showlist WHERE page_type = 203 AND data_type = 4 ORDER BY sortno ASC, ctime DESC limit {$limit}";
			$result = DBFamousHelper::getConn()->read($sql, array());
			$dataIds = array_keys($this->darenProperty);
			foreach ($result as $key => $value) {
				in_array($value['data_id'], $dataIds) && $result[$key]['type_name'] = $this->darenProperty[$value['data_id']];
			}
			$cacheHelper->set($cacheKey, $result, 3600);
		}
        return $result;
	}
}

