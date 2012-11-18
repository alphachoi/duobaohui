<?php
namespace Snake\Package\Famous\Helper;

class RedisFamousActivity extends \Snake\Libs\Redis\Redis {
    protected static $prefix = 'FamousAct';

    /** 
     * 添加用户关注
     * @param 用户编号 <br/>
     * @param 关注人编号 <br/>
     * @param Unix时间 <br/>
     *
     * @return Long 1 if the element is added. 0 otherwise.
     */
    public static function addVote($userId, $followId, $score = -1) {
        if (empty($userId) || empty($followId)) {
            return FALSE;
        }
        $score == -1 && $score = time();
        return self::zAdd($userId, $score, $followId);
    }

    /**
     * 移除关注用户
     * @param 用户编号
     * @param 关注人编号
     *
     * @return LONG 1 on success, 0 on failure.
     */
    public static function removeVote($userId, $followId) {
        if (empty($userId) || empty($followId)) {
            return FALSE;
        }   
        return self::zDelete($userId, $followId);
    }   

    /** 
     * 判断是否关注某人 
     * @param $userId 用户编号
     * @param $followId 关注人编号
     *
     */
    public static function isVoted($userId, $followId) {
        if (empty($userId) || empty($followId)) {
            return FALSE;
        }   
        $score = self::zScore($userId, $followId);
        return $score !== FALSE;
    }   

    /** 
     * 获取用户粉丝
     */
    public static function getVotes($user_id, $order = 'DESC', $start = 0, $limit = 0, $withscore = FALSE) {
        if (empty($user_id)) {
            return FALSE;
        }   
        if (empty($limit) || $limit > 200000) {
            $limit = 100000;
        }   
        $end = $start + $limit - 1;
        if ($order == 'DESC') {
            return self::zRevRange($user_id, $start, $end, $withscore);
        }   
        else {
            return self::zRange($user_id, $start, $end, $withscore); 
        }   
    }   

    /** 
     * 获取用户vote总数
     * @param $userId 用户编号
     */
    public static function getVoteCount($userId) {
        if (empty($userId)) {
            return FALSE;
        }   
        return (int) self::zCard($userId);
    }   
}
