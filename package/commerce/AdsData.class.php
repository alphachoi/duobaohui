<?php

namespace Snake\Package\Commerce;

Use Snake\Package\Commerce\Helper\DBAdsHelper AS DBAdsHelper;
Use Snake\Package\Picture\PictureConvert AS PictureConvert;

class AdsData {
    /**
     * 商业Hot广告接口
     * @author sun shaolei<shaoleisun@melishuo.com>
     * @param type $pageType
     * @param type $pagecode
     * @param type $keyword
     * @param type $page
     * @return boolean 
     */

    function getAdsData($pageType = "", $pagecode = "", $keyword = "", $page = 0) {
        $return = array();
        $adsInfo = $this->getadsPageListAPI($pageType, $pagecode, $keyword, $page);
        if (empty($adsInfo)) {
            return FALSE;
        }
        $adsdata = array();
        if (!empty($adsInfo)) {
            foreach ($adsInfo as $ads) {
                $rownum = $ads['rownum'];
                $oid = $ads['oid'];
                $obj_sytle = intval($ads['obj_style']);

                if (strpos($ads['materid'], ",")) {
                    $materials = $ads['materiels'];
                    $index = mt_rand(0, count($materials) - 1);

                    $picConvertHelper = new PictureConvert($materials[$index]['paths']);
                    $picUrl = $picConvertHelper->getPictureO();
                    $adsdata[$obj_sytle][] = array('pic_url' => $picUrl,
                        'url' => urlencode($materials[$index]['links']),
                        'intro' => $materials[$index]['info'],
                        'oid' => $oid,
                        'extid' => $materials[$index]['extid'],
                        'columns' => $ads['columns'],
                        'rownum' => $rownum);
                } else {
                    $picConvertHelper = new PictureConvert($ads['materiels'][0]['paths']);
                    $picUrl = $picConvertHelper->getPictureO();
                    $adsdata[$obj_sytle][] = array('pic_url' => $picUrl,
                        'url' => urlencode($ads['materiels'][0]['links']),
                        'intro' => $ads['materiels'][0]['info'],
                        'oid' => $oid,
                        'extid' => $ads['materiels'][0]['extid'],
                        'columns' => $ads['columns'],
                        'rownum' => $rownum);
                }
            }
            $step = 0;
            if (!empty($adsdata[1])) {
                foreach ($adsdata[1] as &$commonAds) {
                    $meilishuoUrl = MEILISHUO_URL;
                    $commonAds['url'] = "{$meilishuoUrl}/api/redirect/{$commonAds['oid']}?r={$commonAds['url']}&wza=p{$page}r{$step}c{$commonAds['columns']}";
                    $step += 1;
                    unset($commonAds['oid'], $commonAds['rownum'], $commonAds['columns']);
                }
            }

            $return['common_ads'] = $adsdata[1];
            $return['mall_ads'] = $adsdata[2];
            return $return;
        }
    }

    function getadsPageListAPI($pagetype, $pagecode = '', $keyword = '', $pageno = 0) {
        $pageno = intval($pageno) + 1;
        if (!is_numeric($keyword)) {
            $keyword_code = substr(md5($keyword), 20);
        } else {
            $keyword_code = $keyword;
        }
        $where = array();
        $where['status'] = 1;
        $where['pagetype'] = $pagetype;
        $where['pageno'] = $pageno;
        $whereSql = '';
        if ($pagetype == 1) {
            $where['pagecode'] = $pagecode;
            $whereSql .= " AND pagecode=:pagecode";
        } elseif ($pagetype == 2) {
            $where['pagecode'] = $pagecode;
            $where['keywold_code'] = $keyword_code;
            $whereSql .= " AND pagecode=:pagecode AND keywold_code=:keywold_code";
        } elseif ($pagetype == 3) {
            $where['keywolds'] = $keyword;
            $whereSql .= " AND keywolds=:keywolds";
        }
        $sql = "SELECT pid,oid,pagetype,pagecode,keywolds,pageno,rownum,columns,materid,isroll1 isroll FROM t_dolphin_adsys_position WHERE oid IS NOT NULL AND materid IS NOT NULL AND status=:status AND pageno=:pageno AND pagetype=:pagetype " . $whereSql . " ORDER BY rownum ASC";
        //$posiPage = $this->runSelect(array('table'=>$this->posiTableName,'where'=>$where,'data'=>'pid,oid,pagetype,pagecode,keywolds,pageno,rownum,columns,materid'));
        $posiPage = array();
        $posiPage = DBAdsHelper::getConn()->read($sql, $where);
        $posiReturn = $rollArr = $return = array();
		$time = strtotime(date("Y-m-d")." 00:00:00");
        foreach ($posiPage as $key => &$ad) {
            $sqlCom = "SELECT obj_style FROM t_dolphin_adsys_orders WHERE oid = {$ad['oid']} and status=1 and statetime<={$time} and endtime>={$time}";
            $order = DBAdsHelper::getConn()->read($sqlCom, array());
            $ad['obj_style'] = $order[0]['obj_style'];
            $mids = $ad['materid'];
            if (!empty($mids) && !empty($ad['oid'])) {
                $sql = "SELECT paths, links, info, ext_c extid FROM t_dolphin_adsys_materiel WHERE status=1 AND mid IN ($mids)";
                $result = array();
                $result = DBAdsHelper::getConn()->read($sql, array());
                $ad['materiels'] = $result;
                if ($result) {
                    if ($ad['isroll'] == 1) {
                        $rollArr[] = $key;
                    }
                    $posiReturn[$key] = $ad;
                }
            } else {
                $ad['materiels'] = array();
            }
        }
        shuffle($rollArr);
        //轮循显示
        $k = 0;
        foreach ($posiReturn as $pk => $pv) {
            if ($pv['isroll'] == 0) {
                $return[$pk] = $pv;
            }
            if ($pv['isroll'] == 1) {
                $return[$pk] = $posiReturn[$rollArr[$k]];
                $k++;
            }
        }
        return $return;
    }
	
	/** 
	 *获取banner广告接口
	 *@param unknown_type $class_code 我的首页(home) 逛宝贝（guang）类目（catalog）
     * @param unknown_type $page_code 如果没有页面默认和class_code一样，类目页以标识ID为主 如衣服2000000000000
     * @param unknown_type $position top 项部banner bottom 底部buttor
	 */
	public function getWebBannerAPI($class_code='home',$page_code='',$position='top') {
		if(empty($page_code)) $page_code = $class_code;
		$side = $this->getSiteInfo('sid,is_time', $class_code, $page_code, $position);
		if(empty($side)) return false;
		$sid = $side[0]['sid'];
		$time = date('Y-m-d H:i:s');
		$colum = "title,pic_path,links,bg_time,end_time,activeid,exts,orderby,sid,mid,adid";
		if($side[0]['is_time'] == 1){
			$result = $this->getAdsInfo($colum, $sid, $time);
		}
		else {
			$result = $this->getAdsInfo($colum, $sid);
		}
		return $result;
	}
	/**
     *获得广告的信息,从主库读
	 */
	public function getAdsInfo($colum, $sid, $time='', $master = true) {
	
		$sqlComm = "select $colum from t_dolphin_site_materiel where sid=:sid  and status=:status";
		if(!empty($time)) {
			$sqlComm .= " and bg_time <= :bg and end_time >= :end ";
			$sqlData['bg'] = $time;
			$sqlData['end'] = $time;
		} 
		$sqlComm .= "  order by orderby asc";
		$sqlData['sid'] = $sid;
		$sqlData['status'] = 1;
		$result =  DBAdsHelper::getConn()->read($sqlComm, $sqlData, $master);
		return $result;
	}

	/**
     * 获得广告位置信息
	 */
	public function getSiteInfo($colum, $class_code, $page_code, $position) {
	
		$sqlComm = "select $colum from t_dolphin_site_position where status=:status and class_code=:class_code and page_code=:page_code and position_code=:position_code";
		$sqlData = array('status' => 1,
						 'class_code' => $class_code,
						 'page_code' => $page_code,
						 'position_code' => $position
		);
		$result = DBAdsHelper::getConn()->read($sqlComm, $sqlData);	
		return $result;
	}
}
