<?php
namespace Snake\Package\User;

Use \Snake\Package\User\Helper\DBUserHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\Medal\MedalLib;
USE \Snake\Package\User\Helper\RedisUserFans;
USE \Snake\Package\User\Helper\RedisUserFollow;

class UserStatistic implements \Snake\Libs\Interfaces\Iobserver {

	private static $instance;
	protected $userStatistic;
	private $table = 't_dolphin_user_statistic';
	private $fields = array('follower_num', 'heart_num', 'twitter_num', 'reply_num', 'picture_num', 'goods_num', 'answer_num', 'lookpic_num', 'alert_num', 'following_num');

	public function __construct() {
		//TODO
		$this->userStatistic['follow'] = 0;
	}

	
    /** 
     * @return userStatic Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new self(); 
        }   
        return self::$instance;
    }   

	public function onChanged($server, $args) {
		switch ($server) {
			case 'Follow' :
				$this->setStaticNumber($args['user_id'], 'following_num', 'following_num+1');
				$this->setStaticNumber($args['other_id'], 'follower_num', 'follower_num+1');
				break;
			case 'CancelFollow' :
				$this->setStaticNumber($args['user_id'], 'following_num', 'following_num-1');
				$this->setStaticNumber($args['other_id'], 'follower_num', 'follower_num-1');
				break;
			case 'Register_action' :
				$this->setStaticNumber($args['user_id'], 'following_num', 'following_num+1');
				$this->setStaticNumber($args['other_id'], 'follower_num', 'follower_num+1');
				$this->setStaticNumber($args['user_id'], 'follower_num', 'follower_num+1');
				$this->setStaticNumber($args['other_id'], 'following_num', 'following_num+1');
				break;
			default :
				$this->userStatistic['user_id'] = $args;
				self::save();
		}
	}

	private static function save() {
		//TODO
		RedisUserStatisticHelper::hMSet();
	}

	public function getUserStatistic($userId) {
        $result = RedisUserStatisticHelper::getUserStatistic($userId);
        return $result;
    }   

	public function getUserStatistics($userIds, $setKey = TRUE) {
		$result = array();
		if (!empty($setKey)) {
			foreach ($userIds as $key => $value) {
				$result[$value] = RedisUserStatisticHelper::getUserStatistic($value);
			}
		}
		else {
			foreach ($userIds as $key => $value) {
				$result[] = RedisUserStatisticHelper::getUserStatistic($value);
			}
		}
        return $result;
    }   

	/**
     * 设定制定的统计数据为指定的值 <br/>
     * @param int $userId <br/>
     * @param string $statKey, 字段名字，比如heart_num, twitter_num等 <br/>
     * @param string $statNum， 数字或者字符串（比如 heart_num + 1，灵活使用
	 * <br/>
     */
    public function setStaticNumber($userId, $statKey, $statNum) {
        if (empty($userId) || empty($statKey)) {
            return FALSE;
        }

        if (!RedisUserStatisticHelper::exists($userId)) {
            $this->getUserStatisticByUid($userId);
        }

        if (is_numeric($statNum)) {
            RedisUserStatisticHelper::update($userId, $statKey, $statNum);
        }
        else {
            if (FALSE !== strpos($statNum, '+')) {
                list($field, $value) = explode('+', $statNum);
                if (trim($field) == $statKey) {
                    $value = intval(trim($value));
					RedisUserStatisticHelper::incr($userId, $statKey, $value);
                }
            }
            elseif (FALSE !== strpos($statNum, '-')) {
                list($field, $value) = explode('-', $statNum);
                if (trim($field) == $statKey) {
                    $value = intval(trim($value));
					RedisUserStatisticHelper::decr($userId, $statKey, $value);
                }
            }
            else {
				// exception, do not modify any data
            }
        }

		//小于0
		$userStatistic = RedisUserStatisticHelper::getUserStatistic($userId);
		$fansNum = $userStatistic['follower_num'];
		$followNum = $userStatistic['following_num'];
		if ($fansNum < 0) {
			$fansNum = RedisUserFans::getFansCount($userId);	
			RedisUserStatisticHelper::update($userId, 'follower_num', $fansNum);
		}
		if ($followNum < 0) {
			$followNum = RedisUserFollow::getFollowCount($userId);
			RedisUserStatisticHelper::update($userId, 'following_num', $followNum);
		}


        //$statistic = RedisUserStatisticHelper::getUserStatistic($userId);
		//print_r($statistic);die;
        //刷新勋章
        $medalHelper = new MedalLib($userId);
        switch ($statKey) {
            case 'reply_num':
                $medalHelper->refreshMedals(MEDAL_EXP_REPLY);
                break;
            case 'goods_num':
                $medalHelper->refreshMedals(MEDAL_EXP_GOODS);
                break;
            case 'question_num':
                $medalHelper->refreshMedals(MEDAL_EXP_QUESTION);
                break;
            case 'answer_num':
                $medalHelper->refreshMedals(MEDAL_EXP_ANSWER);
                break;
            case 'best_answer_num':
                $medalHelper->refreshMedals(MEDAL_EXP_BEST_ANSWER);
                break;
        }
        return TRUE;
    }

	public function getUserStatisticByUid($user_id, $getFromMaster = FALSE) {
        if (empty($user_id)) {
            return FALSE;
        }    

        // cache data in one request
        static $stats = array();

        if (!isset($stats[$user_id])) {
			$data = RedisUserStatisticHelper::getUserStatistic($user_id);
            if (empty($data)) {
                $result = $this->_getUserStatisticFromDB($user_id, $getFromMaster, FALSE);
                if ($result) {
                    $data = $result[0];
					//根据数据库数据修复对应的user_statistic redis
					$this->_setUserStatisticFromDB($user_id, $data);
                }    
            }    

            // hack for special account (美丽小精灵)
            if ($user_id == 219 && !empty($data)) {
                $data['following_num'] = intval($data['following_num'] * 3.1415926);
                $data['follower_num'] = intval($data['follower_num'] * 3.1415926);
            }    

            foreach ($data AS $k => $v) {
                if ($v < 0) { 
                    $data[$k] = 0; 
                }    
            }    
            //如果是空，设置为０
            $fileds = $this->fields;
			foreach ($fileds as $f) {
                if (!isset($data[$f])) {
                    $data[$f] = 0; 
                }
            }
            $stats[$user_id] = array($data);
        }
        return $stats[$user_id];
    }

	private function _getUserStatisticFromDB($user_id, $getFromMaster = FALSE, $hashKey = TRUE) {
        $sqlComm = "SELECT * FROM t_dolphin_user_statistic WHERE user_id = :_user_id";
        $sqlData['_user_id'] = $user_id;
        $result = array();
		if (!empty($hashKey)) {
			$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, $getFromMaster, 'user_id');
		}
		else {
			$result = DBUserHelper::getConn()->read($sqlComm, $sqlData);
		}
        return $result;
    }


	/**
	 * 在表user_statistic中为用户增加一行
	 * @param $userId 用户编号
	 *
	 * @return 影响行数
	 * @author ChaoGuo
	 */
	public function addUserStatisticRow($userId) {
	 	if (empty($userId)) {
            return FALSE;
        }
        $sqlComm = "INSERT IGNORE INTO {$this->table} (user_id) VALUES (:_user_id)";
        $sqlData['_user_id'] = $userId;

        $result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
	}

    /**  
     * 获取某一周的所有排行第一用户
     * @param unknown_type $date
     */
    public function getUserStatTopWeekly($date, $limit = 1) {
        $rows = array('goods', 'pop', 'ask', 'answer', 'best_answer');
        $ret = array();

        foreach($rows as $row) {
            $sqlComm = "SELECT * FROM t_dolphin_user_stat_weekly WHERE date={$date} ORDER BY {$row}_num DESC LIMIT {$limit}";
            $result = array();
            if ($limit == 1) {
				$result = DBUserHelper::getConn()->read($sqlComm, array());
                $ret[$row] = (isset($result[0])) ? $result[0] : array();
            } 
			else {
				$result = DBUserHelper::getConn()->read($sqlComm, array(), FALSE, 'user_id');
                $ret[$row] = $result;
            }    
        }    
        return $ret;
    }    

	private function _setUserStatisticFromDB($user_id, $data) {
		if (isset($data['reply_num'])) {
			RedisUserStatisticHelper::update($user_id, 'reply_num', $data['reply_num']);
		}
		if (isset($data['twitter_num'])) {
			RedisUserStatisticHelper::update($user_id, 'twitter_num', $data['twitter_num']);
		}
		if (isset($data['following_num'])) {
			RedisUserStatisticHelper::update($user_id, 'following_num', $data['following_num']);
		}
		if (isset($data['follower_num'])) {
			RedisUserStatisticHelper::update($user_id, 'follower_num', $data['follower_num']);
		}
		if (isset($data['message_num'])) {
			RedisUserStatisticHelper::update($user_id, 'message_num', $data['message_num']);
		}
		if (isset($data['picture_num'])) {
			RedisUserStatisticHelper::update($user_id, 'share_num', $data['picture_num']);
		}
		if (isset($data['goods_num'])) {
			RedisUserStatisticHelper::update($user_id, 'goods_num', $data['goods_num']);
		}
		if (isset($data['alert_num'])) {
			RedisUserStatisticHelper::update($user_id, 'alert_num', $data['alert_num']);
		}
		if (isset($data['heart_num'])) {
			RedisUserStatisticHelper::update($user_id, 'heart_num', $data['heart_num']);
		}
	}
}
