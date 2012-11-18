<?php
namespace Snake\Package\Banner;

Use \Snake\Package\Banner\Helper\DBBannerHelper AS DBBannerHelper;

class TopBanner{
	
	public function __construct() {

	}

	public function save() {
		//TODO
	}

	
    /** 
     * 得到一定时间内的有效的TopBanner
	 * @param $currentTime unix_timestamp
	 * @param $deadline unix_timestamp
	 * @table t_dolphin_home_banner
	 * @author yishuliu@meilishuo.com
	 *
     **/
    public function getTopBannerInfobyTime($currentTime, $deadline, $table = 't_dolphin_home_banner') { 
        if (empty($deadline) || empty($currentTime)) {
            return FALSE;
        }   
        $sqlComm = "select /*activity-lys*/ * from $table where UNIX_TIMESTAMP(start_date) >= $currentTime and UNIX_TIMESTAMP(start_date) <= $deadline and status = 1";
        //$sqlData = array();
        //$sqlData['currentTime'] = $currentTime;
        //$sqlData['deadline'] = $deadline;
        $result  = array();
		$result = DBBannerHelper::getConn()->read($sqlComm, array());
        return $result;
    }   

	/**
	 * 得到我的首页，逛宝贝页activity滚动广告
	 * @param $time unix_timestamp
	 * @table t_dolphin_topbanner_activity
	 * @author yishuliu@meilishuo.com
	 **/
	public function getTopbannerTimeInterval($time, $limit = 0, $table = 't_dolphin_topbanner_activity') {  
		$sqlData = array('table' => $table, 'time' => $time);
        $sqlComm = "select * from :table where :time >=begin_time and :time <=end_time and imgurl is not null and imgurl != ''";
        if ($limit != 0) { 
            $sqlComm .=" limit {$limit}";
        }   
        $result = array();
		$result = DBBannerHelper::getConn()->read($sqlComm, $sqlData);
        return $result;
    }   
}
