<?php 
namespace Snake\Package\Edm;
use  Snake\Package\Seo\Helper\DBSeoHelper;
use  Snake\Package\User\User;
use  Snake\Package\msg\GetUserMsg;
use  Snake\Package\msg\Helper\RedisUserPrivateMsg;
use  Snake\Package\User\Helper\RedisUserFans;
use  Snake\Package\User\Helper\RedisUserFollow;
use Snake\Package\Twitter\Twitter;
use  Snake\Package\Msg\Alert;
use  Snake\Package\Edm\Helper\DBEdmHelper;
class  Edm {
	/**
	 *获取用户的消息的详细信息
	 */
    public function getDetailInfo($info, $type, $userHelper="", $alertHelper ="") {
        if (empty($userHelper) || empty($alertHelper)) {
            $alertHelper = new Alert();
            $userHelper = new User();
        }
		$email = $this->getAllIgnore(1, 'email', false, 'email');
        foreach ($info as &$userInfo) {
			if  (isset($email[$userInfo['email']])) continue;
			$last_id = $tuid = $fid = $fanid = "";
			foreach ($type as $typekey) {
				$userInfo[$typekey] = NULL;	
			}
            $id = $userInfo['user_id'];
            $msgHelper = new GetUserMsg($id);
            $msgHelper->getInfoByUid();
            $msgInfo = $msgHelper->getMsgInfo();
			if (!empty($type['pmsg_num'])) { 
				$userInfo['pmsg_num'] = $msgInfo['pmsg_num'];
			}
			if (!empty($type['last_id']) || !empty($type['pmsgnickname'])) {
				$last_id_arr = RedisUserPrivateMsg::getConversationTimeline($id,0,1);
				if (!empty($last_id_arr)) {
					$last_id = $last_id_arr[0];	
				}
			}
			if (!empty($type['last_id']) && !empty($last_id)) {
				 $userInfo['last_id'] = $last_id;	
			}
			if (!empty($type['pmsgnickname']) && !empty($last_id)) {
					$pmsgnickname = $userHelper->getUserInfo($last_id,array('nickname')); 
					$userInfo['pmsgnickname'] = $pmsgnickname['nickname'];
			}
			if (!empty($type['atme_num'])) {
				$userInfo['atme_num'] = $msgInfo['atme_num'];
			}
			if (!empty($type['tuid']) || !empty($type['replynickaname'])) {
				$tuidarr = $alertHelper->getAtMeTids($id, 0, 1, '*');
				if (!empty($tuidarr)) {
					$tid = $tuidarr[0]['twitter_id'];
					$twitterHelper = new Twitter(array('twitter_id','twitter_author_uid'));
					$tuid = $twitterHelper->getTwitterByTids(array($tid)); 
					$tuid = $tuid[0]['twitter_author_uid'];
				}
			}
			if (!empty($type['tuid']) && !empty($tuid)) {
					$userInfo['tuid'] = $tuid;
			}
			if (!empty($type['replynickanme']) && !empty($tuid)) {
					$replynickname = $userHelper->getUserInfo($tuid, array('nickname')); 
					$userInfo['replynickname'] = $replynickname['nickname'];
					$userInfo['tuid'] = $tuid;
			}
			if (!empty($type['fans_num'])) {
				$userInfo['fans_num'] = $msgInfo['fans_num'];
			}
			if (!empty($type['fid']) || !empty($type['fnickname'])) {
				$fidarr = RedisUserFollow::getFollow($id, 'DESC', 0, 1);
				if (!empty($fidarr)) {
					$fid = $fidarr[0];
				}
			}
			if (!empty($type['fid']) && !empty($fid)) {
					$userInfo['fid'] = $fid;
			}
			if (!empty($type['fnickname']) && !empty($fid)) {
					$fnickname = $userHelper->getUserInfo($fid, array('nickname'));
					$userInfo['fnickname'] = $fnickname['nickname'];
			}

			if (!empty($type['recemmend_num'])) {
				$userInfo['recemmend_num'] = $msgInfo['recommend_num'];
			}
			if (!empty($type['fanid']) || !empty($type['fannickname'])) {
				$fanidarr = RedisUserFans::getFans($id, 'DESC', 0, 1);
				if (!empty($fanidarr)) {
					$fanid = $fanidarr[0];
				}
			}
			if (!empty($type['fanid']) && !empty($fanid)) {
					$userInfo['fanid'] = $fanid;
			}
			if (!empty($type['fannickname']) && !empty($fanid)) {
					$fannickname =$userHelper->getUserInfo($fanid,array('nickname')); 
					$userInfo['fannickname'] = $fannickname['nickname'];
			}
			if (!empty($type['sysmesg'])) {
				$userInfo['sysmesg'] = $msgInfo['sysmesg'];
			}

        }
        return $info;
        
	}
	/** 
	 *获得活跃用户
	 */
    public function getActiveUser($type, $day, $limit = 0) {
        $time = $day;
        $mintime = $time - 30*24*3600;
        $maxtime = $time;
        $midtime = $time - 7*24*3600;
        $sqlComm = "select user_id, email,nickname  from t_dolphin_user_profile where  is_actived != 2 and level = 0 and email like '$type' and  ((login_times >=2 and unix_timestamp(last_logindate) > $mintime and unix_timestamp(last_logindate) < $maxtime ) or (login_times <2 and login_times > 0 and unix_timestamp(last_logindate) < $maxtime and unix_timestamp(last_logindate) > $midtime)) ";
        if (!empty($limit)) {
            $sqlComm .=  " limit $limit";   
        }
        var_dump($sqlComm);
        $result = DBSeoHelper::getConn()->read($sqlComm, array());
        return $result;
    }
	/**
	 *获得较活跃用户
	 */

    public function getSecondActiveUser($type, $day, $limit = 0) {
        $time = $day;
        $mintime = $time - 180*24*3600;
        $maxtime = $time - 30*24*3600;
        $midtime = $time - 90*24*3600;
        $othertime = $time - 7*24*3600;
        $sqlComm = "select user_id, email,nickname from t_dolphin_user_profile where  is_actived != 2 and level = 0 and email like '$type' and  ((login_times >=2 and unix_timestamp(last_logindate) > $mintime and unix_timestamp(last_logindate) < $maxtime ) or (login_times <2 and login_times > 0 and unix_timestamp(last_logindate) < $othertime and unix_timestamp(last_logindate) > $maxtime)) ";
        if (!empty($limit)) {
            $sqlComm .=  " limit $limit";   
        }
        $result = DBSeoHelper::getConn()->read($sqlComm, array());
        return $result;
        
    }

	public function outputcsv($file_name, $userInfo) {
        
		$dir = '/home/work/logs/new_edm/';
        $file_name = $dir . $file_name;
		$fp = fopen($file_name, 'a');
		foreach ($userInfo as $info) {
			fputcsv($fp,$info);
		}
		fclose($fp);
		
	}
	
}

