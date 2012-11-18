<?php
namespace Snake\Package\Media;

/**
 * 关于我们页新闻报道数据
 * @author yishuliu@meilishuo.com
 * @since 2012-08-02
 * @version 1.0
 */

Use \Snake\Package\Media\Helper\DBMediaHelper;
Use \Snake\libs\Cache\Memcache;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-08-02
 * @version 1.0
 */

class Media {

	public function __construct() {
	}

	public function getMedia($start = 0, $limit = 0, $orderBy = 'desc', $params = array('id', 'title', 'linkurl', 'news_source', 'time', 'sortno', 'add_time')) {
		if (empty($params)) {
			return FALSE;
		}
		$col = implode(',', $params);
        $sqlComm = "SELECT $col FROM t_dolphin_aboutus_news WHERE 1 = 1";
		if ($orderBy == 'desc') {
			$sqlComm .= " ORDER BY time DESC";
		}
		if (!empty($limit)) {
			$sqlComm .= " LIMIT {$start}, {$limit}";
		}
		
        $result = DBMediaHelper::getConn()->read($sqlComm, array());    
        return $result;
    }  

	public function getNumOfMedia() {
        $sqlComm = "SELECT count(*) as num FROM t_dolphin_aboutus_news WHERE 1 = 1";
        $result = DBMediaHelper::getConn()->read($sqlComm, array());    
        return $result[0]['num'];
	}
}
