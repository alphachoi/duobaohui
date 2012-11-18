<?php
namespace Snake\Package\User;

/**
 * 达人页面本周小红心榜
 * @author yishuliu@meilishuo.com
 * @since 2012-07-09
 * @version 1.0
 */

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\User\Helper\DBUserHelper;
Use \Snake\Package\User\User;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Twitter\TwitterRecommend;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Famous\FamousUser;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-07-09
 * @version 1.0
 */

class TopMm {

    private static $instance = NULL;
	const topMmPageSize = 10;
    
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

    public function getTotalAmount($type) {
        $sqlComm = "SELECT count(*) as total FROM t_dolphin_user_topmm where type = :type AND number = :number";
        $sqlData = array();
        $sqlData['type'] = $type;
        if ($type == 'week') {
            $sqlData['number'] = date('W');
        }   
        else {
            $sqlData['number'] = date('n');
        }   

        $result =  array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData);
        return $result[0]['total'];
    }   

    /**
     * 获取topMM
     * @param string $type // week or month
     * @param int $page //0 means first page while 4 means the fifth page
     * @param int $page_size // number of items in one page
     * @return array $result // return current page items
     */
    function getCurrentTopMm($type, $page, $pageSize) {
        $start = $page * $pageSize;
        $sqlComm = "SELECT user_id, share_number, heart_number, type, number FROM t_dolphin_user_topmm where type = :type AND number = :number AND user_id NOT IN (2529795, 2293944) ORDER BY heart_number DESC LIMIT :_start, :_pageSize";
        $sqlData = array();
        $sqlData['type'] = $type;
        if ($type == 'week') {
            $sqlData['number'] = date('W');
        }
        else {
            $sqlData['number'] = date('n');
        }
        $sqlData['_start'] = $start;
        $sqlData['_pageSize'] = $pageSize;
        $topMms = array();
		$topMms = DBUserHelper::getConn()->read($sqlComm, $sqlData);
        $lastTopMms = self::getInstance()->getLastPeriodTopMm($type);
        $oldUserIds = array_keys($lastTopMms);
        // user info pictures etc.
        foreach ($topMms as $key => $item) {
			$static = UserStatistic::getInstance()->getUserStatisticByUid($item['user_id']);
			$userHelper = new User();
			$userInfo = $userHelper->getUserInfo($item['user_id'], array('nickname', 'avatar_b', 'is_taobao_buyer'));

			$static[0]['nickname'] = $userInfo['nickname'];
			$static[0]['avatar_b'] = $userInfo['avatar_b'];
			$static[0]['is_taobao_buyer'] = $userInfo['is_taobao_buyer'];

            $topMms[$key]['user'] = $static;
            $topMms[$key]['nickname'] = $static[0]['nickname'];
            $topMms[$key]['avatar'] = $static[0]['avatar_b'];
            $topMms[$key]['is_taobao_buyer'] = $static[0]['is_taobao_buyer'];
            $topMms[$key]['up'] = TRUE;

            if (in_array($item['user_id'], $oldUserIds) && $key + $page * $pageSize > $lastTopMms[$item['user_id']]) {
                $topMms[$key]['up'] = FALSE;
            }

            $topMms[$key]['key'] = $key + $page * $pageSize + 1;
            $topMms[$key]['top_three'] = FALSE;
            if ($key < 3 && $page == 0) {
                $topMms[$key]['top_three'] = TRUE;
            }
        }
        return $topMms;
    }

    /**
     * 得到上一期的排行榜
     * @param string $type //week or month
     * @return array $lastTopMm // key is user_id, value is order number. 0 means the top one, while 49 is the last
     */
    function getLastPeriodTopMm($type = 'week'){
        $sqlComm = "SELECT user_id FROM t_dolphin_user_topmm WHERE type = :type AND number = :number ORDER BY heart_number DESC";
        $sqlData = array();
        $sqlData['type'] = $type;
        if ($type == 'week') {
            $sqlData['number'] = date('W', time() - 3600 * 24 * 7);
        }
        else {
            $sqlData['number'] = date('n') == 1 ? 12 : date('n') - 1;
        }

        $lastTopMm = $result = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData);
        if (!empty($result)) {
            $i = 0;
            foreach ($result as $item) {
                $lastTopMm[$item['user_id']] = $i;
                $i++;
            }
        }
        return $lastTopMm;
    }

	/**
	 * 得到一定时间内用户的发宝发推数量
	 */
    public function getShareNumsByUids($userIds, $type, $end, $userType) {
        $sqlData = array();
        if ($type == 'week') {
            $sqlData['start'] = $end - 604800;
            $sqlData['end'] = $end;
        }
        else {
            $sqlData['start'] = $end - 2592000; //30 days
            $sqlData['end'] = $end;
        }
		$twitterHelper = new Twitter();
		$result = array();
        foreach ($userIds as $key => $item) {
			//得到限定时间内用户发宝发推总数量
			$share_num = $twitterHelper->getUserShareNum($sqlData['start'], $sqlData['end'], $item);
            $result[$item] = $share_num[0]['COUNT(*)'];
        }
        $userHelper = new User();
		if ($userType == 'pinkV') {
        	$userInfo = $userHelper->getUserInfos($userIds, array('nickname', 'avatar_b', 'is_taobao_buyer', 'verify_icons'));
			foreach ($userInfo as $key => $value) {
				if (empty($value['verify_icons'])) {
					unset($result[$value['user_id']]);
					continue;
				}
				$resultIcons = is_array($value['verify_icons']) ? $value['verify_icons'] : explode(',' , $value['verify_icons']);		
				if (!in_array('s', $resultIcons)) {
					unset($result[$value['user_id']]);
				}
			}
		}
		$newResult = $this->_sortArray($result);
        return $newResult;
    }

	private function _sortArray($arrayList) {
		if (!is_array($arrayList)) {
			return FALSE;
		}
        //sort, 取出前5项
        arsort($arrayList);
		$uids = array_keys($arrayList);
        $newResult = array_slice($arrayList, 0, 5, TRUE);	
		return $newResult;
	}

	/**
     * 根据传入userType得到分享数最多的Top5用户信息
     * @param string $userType: pinkV代表全部达人, editor代表超级主编用户
	 */
	public function getCurrentTopFamousByType($userType) {
	    $cacheKey = "TopMm:getCurrentTopFamousByType:" . $userType;
        $cacheHelper = Memcache::instance();
        $userInfo = $cacheHelper->get($cacheKey);	
		if (empty($userInfo)) {
			$userIdsArr = FamousUser::getInstance()->getFamousUids($userType);
			$userIds = \Snake\Libs\Base\Utilities::DataToArray($userIdsArr, 'data_id');
		
			$result = $this->getShareNumsByUids($userIds, 'week', time(), $userType);
			
			//得到top5的user_id
			$topUserIds = array_keys($result);

			$userHelper = new User();
			$userInfo = $userHelper->getUserInfos($topUserIds, array('nickname', 'avatar_b', 'is_taobao_buyer', 'verify_icons'));
			
			foreach ($userInfo as $key => $value) {
				$userInfo[$key]['share_number'] = $result[$key];
			}
			$cacheHelper->set($cacheKey, $userInfo, 3600 * 24);
		}
		return $userInfo; //($userType == 'pinkV') ? $pinkVResult : $editorResult;
	} 

    public function getPreTopMm($type, $end) {
        $sqlData = array();
        if ($type == 'week') {
            $sqlData['start'] = $end - 604800;
            $sqlData['end'] = $end;
        }
        else {
            $sqlData['start'] = $end - 2592000; //30 days
            $sqlData['end'] = $end;
        }
		//查询达人页小红心榜
		$twitterRecommendHelper = new TwitterRecommend();
        $result = $twitterRecommendHelper->getTopMm($sqlData['start'], $sqlData['end']);
		$twitterHelper = new Twitter();
        foreach ($result as $key => $item) {
			//得到限定时间内用户发宝总数量
			$share_num = $twitterHelper->getUserHeartNum($sqlData['start'], $sqlData['end'], $item['twitter_author_uid']);
            $result[$key]['share_number'] = $share_num[0]['COUNT(*)'];
            if ($type == 'week') {
                $result[$key]['number'] = date('W', $end);
            }
            else {
                $result[$key]['number'] = date('n', $end);
            }
        }
        return $result;
    }

    /** 
     * check whether empty
     * @return boolean TRUE or FALSE //TRUE means empty
     */
    public function isEmpty() {
        $sqlComm = "SELECT count(*) as size FROM `t_dolphin_user_topmm`";
		$result = DBUserHelper::getConn()->read($sqlComm, array());
        if (empty($result[0]['size'])) {
            return TRUE;
        }   
        return FALSE;
    }   

    /** 
     * cron script will run it: dolphin/app/scripts/user/preLoadTopMm.php
     */
    public function cronPreLoadTopMm() {
        $isEmpty = $this->isEmpty();
        if ($isEmpty) {
            //前一自然周
            $end = strtotime('last Monday');
            $end -= 604800;
            $topMmWeek = $this->getPreTopMm('week', $end);
            $this->insertTopMm($topMmWeek);
            //前一自然月
            $str = date('Y') .'-'. (date('m') -1) .'-01';
            $end = strtotime($str);
            $topMmMonth = $this->getPreTopMm('month', $end);
            $this->insertTopMm($topMmMonth, 'month');

            //上一自然周
            $end = strtotime("last Monday");
            $topMmWeek = $this->getPreTopMm('week', $end);
            $this->insertTopMm($topMmWeek);
            //上一自然月
            $str = date('Y') .'-'. date('m') .'-01';
            $end = strtotime($str);
            $topMmMonth = $this->getPreTopMm('month', $end);
            $this->insertTopMm($topMmMonth, 'month');
        }   

        if (date('D') == 'Mon') {
            $topMms = $this->getCurrentTopMm('week', 0, self::topMmPageSize);
            if (empty($topMms)) {
                $topMmWeek = $this->getPreTopMm('week', time());
                $this->insertTopMm($topMmWeek);
                $number = date('W', time() - 3600 * 24 * 14);
                $this->deleteTopMm('week', $number);
            }   
        }   
        if (date('j') == '1') {
            $topMms = $this->getCurrentTopMm('month', 0, self::topMmPageSize);
            if (empty($topMms)) {
                $topMmMonth = $this->getPreTopMm('month', time());
                $this->insertTopMm($topMmMonth, 'month');
                $number = date('n', time() - 3600 * 24 * 58);
                $this->deleteTopMm('month', $number);
            }   
        }   
    }   

    /** 
     * Insert data
     */
    public function insertTopMm($rows, $type = 'week') {
        if (empty($rows)) return FALSE;

        $sqlComm = "INSERT INTO `t_dolphin_user_topmm` (`user_id`, `share_number`, `heart_number`, `type`, `number`) VALUES ";

        $sql_array = array();
        foreach ($rows as $row) {
            $sql_array[] = "({$row['twitter_author_uid']}, {$row['share_number']}, {$row['heart_number']}, '{$type}', {$row['number']})";
        }   
        $sqlComm .= implode(', ', $sql_array);
        $sqlData = array();
        DBUserHelper::getConn()->read($sqlComm, $sqlData);
        return TRUE;
    }   

    /** 
     * delete expired and useless records. 删除某周或某月的数据
     */
    public function deleteTopMm($type, $number) {
        $sqlComm = "DELETE FROM `t_dolphin_user_topmm` WHERE type = :type AND number = :number";
        $sqlData = array('type' => $type, 'number' => $number);
        DBUserHelper::getConn()->read($sqlComm, $sqlData);
        return TRUE;
    }   
}
