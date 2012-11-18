<?php
namespace Snake\Package\Welfare;
/**
 *author gstan
 *email guoshuaitan@meilishuo.com
 *福利社右侧信息接口
 *version 1.0
 */
Use Snake\Package\Welfare\Welfare;
Use Snake\Package\User\User;
class SideBar {
	
	/**
	 *获得最新的各种状态的使用的mm
	 *status = 0 申请中 1成功申请
	 */
	public function getNewTakeInWelfareInfo($limit, $status) {
			
		//获得各种状态最新参加福利社的人
		$welfareHelper = Welfare::getInstance();
		$new = $welfareHelper->getNewTakeInWelfare($limit, $status);
		if (empty($new)) {
			return false;	
		}
		$userIds = $userActivityId = $userCtime = array();
		foreach($new as $v){
			$userIds[] = $v['user_id'];
			$userActivityId[] = $v['activity_id'];
			$userCtime[] = $v['ctime'];
		}
		$useHelper = new User();
		$param = array('user_id', 'nickname', 'avatar_c');
		$userInfo  = array();
		if (!empty($userIds)) {
			$userInfo = $useHelper->getUserInfos($userIds, $param);
			foreach($userIds as $key => $value){
				$new[$key]['nickname'] = $userInfo[$value]['nickname'];
				$new[$key]['avatar'] = $userInfo[$value]['avatar_c'];
			}
		}
		$titleInfo = $welfareHelper->getWelfareInfoByIds('title,activity_id/*welfare_list-gstan*/', $userActivityId, FALSE,'activity_id');
		foreach ($new as $key => $value) {
			if (isset($titleInfo[$value['activity_id']])) {
				$new[$key]['activityName'] = $titleInfo[$value['activity_id']]['title'];
			} 
		}
		if (!empty($userCtime)) {
			foreach ($userCtime as $key => $value) {
				$value = \Snake\Libs\Base\Utilities::timeStrConverter(strtotime($value));
				$new[$key]['ctime'] = $value;
			}
		}
		return $new;
		
		
	}	
	
	
}
