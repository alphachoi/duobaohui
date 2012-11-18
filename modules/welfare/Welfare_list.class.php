<?php
namespace  Snake\Modules\Welfare;

use  \Snake\Package\Welfare\Welfare AS Welfare;
use  \Snake\Package\Picture\PictureConvert AS PictureConvert;
use  \Snake\libs\Cache\Memcache AS Memcache;

class Welfare_list extends \Snake\Libs\Controller {
	
	private $welfareHelper;
	private $page_size = 10;

	public function run() {
		$this->welfareHelper = Welfare::getInstance();
		$page = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		$offset = $page*$this->page_size;
		//获得使用得福利社
		$AllInfo = $this->welfareHelper->getTryOnWelfare($offset, $this->page_size);
		$num = $this->welfareHelper->getAllWelfare('count(*) as num');
		//排除85假的福利设
		$num = intval($num[0]['num']) - 1;
		$info = $this->assmbleInfo($AllInfo, $page);
		$info = array('list' => $info, 'totalNum' => $num);
		$this->view = $info;
	}	


	public function assmbleInfo($info, $page) {
		if (empty($info)) {
			return FALSE;	
		}		
		//如果都没有结束，按开始时间排序
		$time = time();
		$j = $k = 0;
		$end = $noend = array();
		foreach ($info as $key=>$value) {
			if ($value['end_time'] > $time) {
				$noend[$j] = $value;
				$j++;
			}	
			else {
				$end[$k] = $value;	
				$k++;
			}
		}
		//将还没结束的按开始时间倒序
		usort($noend, function($a, $b) {
			if ($a['begin_time'] == $b['begin_time']) {
				return 0;
			}
			return ($a['begin_time'] > $b['begin_time']) ? -1 : 1;
			}		
		);
		$info = array();
		$info = array_merge($noend, $end);
		//得到这些活动id,已经个活动对应的总人数
		$cache = Memcache::instance();
		$key = 'welfare_list_num'.$page;
        $aidsNums = $cache->get($key);
        if(empty($aidsNums)) {
            $aids = \Snake\Libs\Base\Utilities::DataToArray($info, 'activity_id');
            $aidsNums = $this->welfareHelper->getNumsByAids($aids);
            $cache->set($key, $aidsNums, 1800);
        }
		foreach ($info as $key => $value) {
			$key1 = 'welfare_list_person_' . $value['activity_id'];
			$result = $cache->get($key1);
			if (empty($result)) {
				$result = $this->welfareHelper->getAvatarInWelfare($value['activity_id'],'user_id,activity_id', 10);
				$cache->set($key1, $result, 1800);
			}
			$pictureHelper = new PictureConvert($value['index_banner']);
			$imgurl = $pictureHelper->getPictureO();
			$info[$key]['index_banner'] = $imgurl;	
			$aid = $value['activity_id'];
			if (isset($aidsNums[$aid])) {
				$info[$key]['num'] = $aidsNums[$aid]['num'];	
			}
			else {
				$info[$key]['num'] = 0;
			}

			if ($aid == 13) {
				$info[$key]['num'] += 1431;	
			}
			elseif ($aid == 19) {
				$fakeNum = $this->fake();
				$info[$key]['num'] += $fakeNum;
			}
			$date = getdate($info[$key]['begin_time']);
			$month = $date['mon'];
			$day = $date['mday'];
			$info[$key]['date'] = $month . "月" . $day . "日";
			$info[$key]['open'] = $_SERVER["REQUEST_TIME"] < $value['end_time'] ? 1 : 0;
			$info[$key]['user'] = $result;
			if ($value['activity_type'] == 2)  {
				$info[$key]['is_editor'] = 1;
				$info[$key]['num'] = floor(3.7*intval($info[$key]['num']));
			}
			else $info[$key]['is_editor'] = 0;
			unset($info[$key]['activity_type']);
		}
		return $info;
	}
	function fake(){
		$time = mktime(7,0,0,1,13,2012);
		$hours = (int)((time()-$time)/3600);
		$hours = $hours > 0 ? $hours : 0; 
		$fakeNum = $hours*40 < 3300 ? $hours*40:3300;
		return $fakeNum;
	}
	
}
