<?php

namespace Snake\Package\Seo;

use Snake\Package\Seo\Helper\DBSeoHelper;
Use Snake\Libs\Cache\Memcache AS Memcache;

class SeoModel {

    private static $instance = NULL;

    /**
     * @return SeoModel Object
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new SeoModel();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    public function seoWords($limit) {
        $cache = Memcache::instance();
        $key = "seowordsnum";
        $num = $cache->get($key);
        if (!$num) {
            $seowords = $this->selectSeoWords(array(), 'count(*) num');
            $num = $seowords[0]['num'];
            $cache->set($key, $num, 12 * 3600);
        }
        $length = empty($limit) ? 100 : $limit;
        $offset = rand(0, $num - $length);
        $seowords = $this->selectWords("/*seo sunsl*/id, word_name", $offset, $length);
        return $seowords;
    }

	public function updateSeowordTypeById($id, $type) {
		$table = "t_dolphin_seo_words";
		$sqlComm = "update $table set type = $type where id = $id";
		$data = array();
		$result = DBSeoHelper::getConn()->write($sqlComm, $data);
		return $result;
	}

    function insertSeowords($insertData, $table = 't_dolphin_seo_words') {
        if (empty($insertData)) {
            return false;
        }
        $sqlComm = "insert ignore into $table(";
        foreach ($insertData as $key => $value) {
            $sqlComm .=$key . ',';
        }
        $sqlComm = rtrim($sqlComm, ',') . ')' . ' values (';
        foreach ($insertData as $key => $value) {
            $sqlComm .=':' . $key . ',';
        }
        $sqlComm = rtrim($sqlComm, ',') . ')';

        $result = DBSeoHelper::getConn()->write($sqlComm, $insertData);
        return $result;
    }

    function selectWords($col = '*', $start, $length) {
        $sqlComm = "select $col from t_dolphin_seo_words where type = 1 and id > $start limit $length";
        $sqlData = array();
        $result = array();
        $result = DBSeoHelper::getConn()->read($sqlComm, $sqlData);
        return $result;
    }

    function selectSeoWords($param = array(), $col = '*') {
        $sqlComm = "select $col from t_dolphin_seo_words where 1";
        $sqlData = array();
        if (isset($param['word_name'])) {
            $sqlData['word_name'] = $param['word_name'];
            $sqlComm .= " AND word_name =:word_name";
        }
        if (isset($param['id'])) {
            $sqlData['_id'] = $param['id'];
            $sqlComm .= " AND id =:_id";
        }
		if (isset($param['elite'])) {
            $sqlData['_elite'] = $param['elite'];
            $sqlComm .= " AND elite =:_elite";
		}
		if (isset($param['type'])) {
			$sqlData['_type'] = $param['type'];
			$sqlComm .= " AND type =:_type";
		}
        if (isset($param['orderby'])) {
            $orderby = $param['orderby'];
            if ($orderby == 'rand()') {
                return array();
            }
            $sqlComm .= " ORDER BY $orderby";
        }
        if (isset($param['limit']) && isset($param['offset'])) {
            $limit = $param['limit'];
            $offset = $param['offset'];
            $sqlComm .= " LIMIT $offset , $limit";
        }
        $result = array();
        $result = DBSeoHelper::getConn()->read($sqlComm, $sqlData);
        return $result;
    }
	 public function deleteBadWords($ids = array()){
	        if (empty($ids) && !is_array($ids)) {
	                  return false;
	        }
	        $ids = implode(',', $ids);
	        $sqlComm = "delete from t_dolphin_seo_words  where id in ($ids) ";
	        $data = array();
	        DBSeoHelper::getConn()->write($sqlComm, $data);
    }

}

?>
