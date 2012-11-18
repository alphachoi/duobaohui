<?php
namespace Snake\Package\About;

USE \Snake\Package\About\Helper\DBAboutHelper AS DBAboutHelper;

class Feedback {

	private $table = 't_dolphin_user_feedback';

	public function __construct() {

	}

	/**
	 * 向t_dolphin_user_feedback插入一条数据
	 * @param $userId 用户编号
	 * @param $email 邮箱
	 * @param $content 反馈信息
	 */
	public function insertFeedback($userId, $email, $content, $agent, $parentId) {
		$sqlComm = "INSERT INTO {$this->table} (user_id, feedback_detail, agent, email, parent_id) VALUES (:_user_id, :feedback_detail, :agent, :email, :_parent_id)";
		$sqlData = array(
			'_user_id' => $userId,
			'email' => $email,
			'agent' => $agent,
			'feedback_detail' => $content,
			'_parent_id' => $parentId,
		);
		DBAboutHelper::getConn()->write($sqlComm, $sqlData);
		return TRUE;
	}

	/**
	 * 更新反馈的状态
	 * @param $feedId 反馈编号
	 * @param $type 反馈状态
	 */
	public function updateFeedback($feedId, $type) {
		$sqlComm = "UPDATE {$this->table} SET type=:_type WHERE feedback_id=:_feed_id";
		$sqlData = array(
			'_type' => $type,
			'_feed_id' => $feedId,
		);
		DBAboutHelper::getConn()->write($sqlComm, $sqlData);
		return TRUE;
	}

	/**
	 * 根据一条feed编号获取其和所有的回复
	 * @param $feedId 反馈编号
	 * @param $fields 查询字段
	 */
	public function selectFeedback($feedId, $fields = array()) {
		if (empty($feedId) || empty($fields)) {
			return FALSE;
		}	
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE (feedback_id=:_feed_id OR parent_id=:_feed_id) AND type > 0 ORDER BY feedback_id";
		$sqlData = array(
			'_feed_id' => $feedId,
		);
		$result = DBAboutHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}

	/**
	 * 根据feed编号获取详细信息
	 * @param $feedId 反馈编号
	 * @param $fields 查询字段
	 */
	public function selectFeedbackInfo($feedId, $fields = array()) {
		if (empty($feedId) || empty($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE feedback_id=:_feed_id";
		$sqlData = array(
			'_feed_id' => $feedId,
		);
		$result = DBAboutHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}
}
