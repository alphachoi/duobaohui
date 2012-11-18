<?php
namespace Snake\Modules\Seo;

use \Snake\libs\Cache\Memcache AS Memcache;

class Sitemap extends \Snake\Libs\Controller {

	//显示总记录数
	private $_num = 9000;
	//属性词百分比
	private $_attr_percent = 0.2;
	//杂志百分比
	private $_group_percent = 0.2;
	//hot页百分比
	private $_hot_percent = 0.1;
	//dict页百分比
	private $_dict_percent = 0.5;

	private $_seo_word_num = 8470053;

	public function run() {
		$this->main();
	}	

	private function main() {
		$cache = Memcache::instance();
		$last = $cache->get('site_map_last_update');
		$cron = FALSE;
		if (date('G') == 4 && $_SERVER['REQUEST_TIME'] - $last > 7200) {
			$cron = TRUE;
		}
		$allInfo = $this->getAllInfo($cron);
		shuffle($allInfo);

		$this->view = $allInfo;
		return TRUE;
	}

	private function getAllInfo($cron = FALSE) {
		$cache = Memcache::instance();
		$allInfo = $cache->get('ALL_ATTR_WORD');
		$params = array();
		if (empty($allInfo) || $cron) {
			//属性词
			$params['isuse'] = 1;
			$selectComm = 'word_id, word_name, label_id';
			//TODO 调用接囗
			$allAttriTitle = '';
			shuffle($allAttrTitle);
			$allAttrTitle = array_splice($allAttrTitle, 0, $this->_num * $this->_attr_percent);
			
			//杂志
			$selectComm = 'group_id, name';
			$cacheGroupNum = $cache->get('GROUP_NUM');
			if (empty($cacheGroupNum)) {
				//TODO 调用接囗
				$groupNum = 0;
				$cache->set('GROUP_NUM', $groupNum, 7200);
			}	
			else {
				$groupNum = $cacheGroupNum;
			}
			$groupLength = $this->_num * $this->_group_percent;
			if ($groupNum <= $groupLength) {
				$randNum = 0;
				$groupLength = $groupNum;
			}
			else {
				$randNum = $groupNum - $groupLength;
			}
			$params = array(
				'start' => rand(0, $randNum),
				'length' => $groupLength,
			);
			//TODO 调用接囗
			$groupName = '';
			shuffle($groupName);

			//hot
			$selectComm = 'id, word_name';
			$limit = $this->_num * $this->_hot_percent;
			$params = array(
				'orderby' => 'id',
				'limit' => $limit,
				'type' => 1,
				'offset' => rand(0, $this->_seo_word_num - $limit),
			);
			//TODO 调用接囗
			$seoWords = array();
			shuffle($seoWords);

			//dict
			$selectComm = 'id, word_name';
			$limit = $this->_num * $this->_dict_percent;
			$params = array(
				'orderby' => 'id',
				'limit' => $this->_num * $this->_dict_percent,
				'offset' => rand(0, $this->_seo_word_num - $limit),
				'type' => 1,
			);
			//TODO 调用接囗
			$dictWords = array();
			shuffle($dictWords);

			//标记品牌
			foreach ($allAttrTitle as $key => $value) {
				//TODO 调用接囗
				$isBrand = 0;
				if ($isBrand) {
					$allAttrTitle[$key]['type'] = 3;
				}
				else {
					$allAttrTitle[$key]['type'] = 1;
				}
			}

			foreach ($groupName as $key => $value) {
				$groupName[$key]['type'] = 2;
			}

			foreach ($seoWords as $key => $value) {
				$seoWords[$key]['type'] = 4;
			}

			foreach ($dictWords as $key => $value) {
				$dictWords[$key]['type'] = 5;
			}

			$allInfo = array_merge($allAttrTitle, $groupName, $seoWords, $dictWords);
			$cache->set('ALL_ATTR_WORD', $allInfo, 30 * 3600);
			$last = $cache->set('site_map_last_update', $_SERVER['REQUEST_TIME'], 72 * 3600);
		}
		return $allInfo;
	}
}
?>
