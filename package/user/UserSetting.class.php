<?php
namespace Snake\Package\User;

Use \Snake\Package\User\Helper\DBUserHelper;

class UserSetting {

	private $table = 't_dolphin_user_settings';	
	private static $instance = NULL;    
    private function __construct() {}

    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new self();   
        }   
        return self::$instance;
    }   
	
	/**
     * 在表user_settings中为用户增加一行
     * @param $user_id 用户编号
     * @param $notice_new_comment 新评论提醒
     * @param $notice_reference @提到我
     * @param $notice_private_mail 新私信
     * @param $comment_by_all 评论
     * @param $payme_status 提现
     */
    public function insertSettings($user_id, $notice_new_comment = 0, $notice_reference = 0, $notice_private_mail = 0, $comment_by_all = 0, $payme_status = 0) {
        if (empty($user_id) || !isset($notice_new_comment, $notice_reference, $notice_private_mail, $comment_by_all)) {
            return FALSE;
        }
        $sqlComm = "REPLACE INTO {$this->table} (user_id, notice_new_comment, notice_reference, notice_private_mail, comment_by_all, payme_status) " .
            "VALUES (:_user_id, :_notice_new_comment, :_notice_reference, :_notice_private_mail, :_comment_by_all, :_payme_status)";
        $sqlData = array(
            '_user_id' => $user_id,
            '_notice_new_comment' => $notice_new_comment,
            '_notice_reference' => $notice_reference,
            '_notice_private_mail' => $notice_private_mail,
            '_comment_by_all' => $comment_by_all,
            '_payme_status' => $payme_status,
        );
        $result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }

	/**
	 * 更新用户设置
	 * @param $userId integer 用户编号
	 * @param $type integer 互联类型 
	 * @param $setting array 设置信息
	 */
	public function updateSettings($userId, $type, array $settings) {
		if (empty($userId) || empty($type) || empty($settings)) {
			return FALSE;
		}
        $sqlComm = "UPDATE {$this->table} SET
            sync_goods=:_sync_goods,
            sync_collect=:_sync_collect,
            sync_like=:_sync_like,
            sync_ask=:_sync_ask,
            sync_answer=:_sync_answer,
            sync_medal=:_sync_medal
            WHERE user_id=:_user_id ";
		$sqlData = array(
			'_sync_goods' => $settings['sync_goods'],
			'_sync_collect' => $settings['sync_collect'],
			'_sync_like' => $settings['sync_like'],
			'_sync_ask' => $settings['sync_ask'],
			'_sync_answer' => $settings['sync_answer'],
			'_sync_medal' => $settings['sync_medal'],
			'_user_id' => $userId,
		);
		$result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
		return $result;
	}
}
