<?php
namespace Snake\Package\Medal;
/**
 * 发推时刷新勋章相关的接口
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Medal\Helper\DBMedalHelper 	   AS DBMedalHelper;
Use \Snake\Package\Medal\Helper\MedalStatistic;
Use \Snake\Package\User\User;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Medal\MedalLib;

/**
 * 勋章相关的接口
 * @author yishuLiu@meilishuo.com
 */
class ObsRefreshMedal implements \Snake\Libs\Interfaces\Iobserver {
	
	public function __construct() {
    }   

    public function onChanged($sender, $params) {
        $this->refresh($params['user_id']);    
    }   

	//TODO
	public function refresh($userId) {
        //刷新标签勋章
        $medalHelper = new MedalLib($userId, TRUE);
        $medalHelper->refreshMedals(MEDAL_EXP_TOPIC);
        //新人勋章
        $medalHelper->refreshMedals(MEDAL_EXP_PERSON);
	}
}
