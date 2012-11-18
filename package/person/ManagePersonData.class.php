<?php
namespace Snake\Package\Person;

Use \Snake\libs\Cache\Memcache AS Memcache;
Use \Snake\Package\User\UserStatistic;

class ManagePersonData implements \Snake\Libs\Interfaces\Iobserver{
	private $statKey = NULL;
	private $showTypes = array(2, 5, 7, 8, 9);

	public function __construct() {

	}

	public function setPersonRedis($user_id, $type = 0, $operator = 'add') {
        switch ($type) {
            case '2' :
				$this->statKey = 'picture_num'; //图片推
                break;
            case '5' :
				$this->statKey = 'twitter_num'; //文字推
                break;
            case '7' :
				$this->statKey = 'goods_num'; //宝贝推
                break;
            case '8' :
				$this->statKey = 'twitter_num'; //转发推
                break;
            case '9' :
				$this->statKey = 'twitter_num'; //喜欢推
                break;
            default:
        }
		if ($operator == 'add') {
			//相关redis操作
			if ($this->statKey !== 'twitter_num') {
				UserStatistic::getInstance()->setStaticNumber($user_id, 'twitter_num', 'twitter_num + 1');
			}
			UserStatistic::getInstance()->setStaticNumber($user_id, $this->statKey, $this->statKey . '+ 1');
			UserStatistic::getInstance()->setStaticNumber($user_id, 'share_num', 'share_num + 1');
		}
		else {
			if ($this->statKey !== 'twitter_num') {
				UserStatistic::getInstance()->setStaticNumber($user_id, 'twitter_num', 'twitter_num - 1');
			}
			UserStatistic::getInstance()->setStaticNumber($user_id, $this->statKey, $this->statKey . '- 1');
			UserStatistic::getInstance()->setStaticNumber($user_id, 'share_num', 'share_num - 1');
		}
		//TODO 清除相关cache
        $cacheObj = Memcache::instance();
        $cacheKey = 'person:share_data' . $user_id;
		$memKey = 'USER:BOOKS:NEW' . $user_id;
		$snakeTwitterKey = 'USER:BOOKS:NUM:' . $user_id;
        $cacheObj->delete($cacheKey);
        $cacheObj->delete($memKey);
        $cacheObj->delete($snakeTwitterKey);
	}

    public function onChanged($sender, $params) {
		if (!in_array($params['twitter']['twitter_show_type'], $this->showTypes)) {
			return FALSE;
		}
		$operator = 'add';
		if (!empty($params['twitter']['delete_twitter'])) {
			$operator = 'delete';
		}
        //print_r("running onChanged Operation!\n");
        $this->setPersonRedis($params['twitter']['twitter_author_uid'], $params['twitter']['twitter_show_type'], $operator);
    }   

}
