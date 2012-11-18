<?php
namespace Snake\Package\User;

USE \Snake\Package\User\Helper\DBUserHelper AS DBUserHelper;

/**
 * 邀请关系
 *
 * `invite_id` char(32) NOT NULL COMMENT 'invite_code',
 * `invited` int(11) NOT NULL COMMENT 'invited person id',
 * `invite_time` datetime DEFAULT NULL,
 * `is_succ` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:invite success; 1:invite failure',
 * `invite_from` int(4) NOT NULL COMMENT '0-普通邀请，1-许愿树',
 * `refund` float(8,4) NOT NULL COMMENT '给邀请用户的返现金额',*
 *
 */
class UserInvite {

	private $table = 't_dolphin_user_invite';	

	public function __construct() {}

	/**
	 * 插入一条邀请关系
	 * @param $inviteCode 邀请人的invite_code
	 * @param $inviteId 被邀请人的user_id
	 * @param $from 来源
	 */
	public function insertUserInvite($inviteCode, $inviteId, $from = 0) {
		if (empty($inviteCode) || empty($inviteId)) {
			return FALSE;
		}	
        $sqlComm = "INSERT INTO {$this->table}(invite_id, invited, invite_time, is_succ, invite_from)
                    VALUES (:invite_id, :invited, now(), 0, :invite_from)";
        $sqlData ['invite_id'] = $inviteCode;
        $sqlData ['invited'] = $inviteId;
        $sqlData ['invite_from'] = $from;	

		DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return TRUE;
	}
}
