<?php 
namespace Snake\Package\Group;

class GroupTopics {
	
	function createNewTopic($title, $userId, $groupId, $twitterId) {
			$topicSign = md5($groupId . ":" . $title);
			$topic = array(
				'group_id' => $groupId,
				'twitter_id' => $twitterId,
				'topic_author_uid' => $userId,
				'topic_title' => $title,
				'topic_sign' => $topicSign,
				'last_comment_time' => time()
			);
			$params = array(
				'fields' => $topic,
				'insert' => 1
			);
			return OperationDB::operationDataBase($params, 'GroupTopics', 'insert');
	}

    public function hackGroupTopic($title, $userId, $groupId, $twitterId) {
        $checkStatus = $this->checkIfGroupTopicExist($title, $groupId);
        if ($checkStatus === FALSE) {
			$topicId = $this->createNewTopic($title, $userId, $groupId, $twitterId);
			return $topicId;
		}
        else {
			$params = array(
				'fields' => array(
					'twitter_number' => 'twitter_number+1'
				),
				'condition' => array(
					'group_id' => $groupId,
					'topic_id' => $checkStatus[0]['topic_id']
				),
			);
			OperationDB::operationDataBase($params, 'GroupTopics', 'update');
            //这个topic已经存在了~
            return $checkStatus[0]['topic_id'];
        }
    }

   public function checkIfGroupTopicExist($title, $groupId) {
	   $topicTitle = $groupId . ':' . trim($title);
		$paramsWhere = array(
			array(
				'key' => 'topic_sign',
				'value' => md5($topicTitle),
				'operation' => 'eq'
			),
			array(
				'key' => 'group_id',
				'value' => $groupId,
				'operation' => 'eq'
			)
			);
		$fields = array('topic_id', 'group_id');
        $parameters = array(
            'where' => $paramsWhere,
            'fields' => $fields
        );
		$hashKey = "";
		$result = OperationDB::selectDataBase($parameters, 'GroupTopics', $hashKey);
		if (isset($result) && !empty($result)) {
			return $result;
		}
		else {
			return FALSE;
		}
	}

	public function deleteTopic($groupId, $topicId = 0) {
		$params['condition'] = array( 
			'group_id' => $groupId
		);
		if (!empty($topicId)) {
			$params['condition']['topic_id'] = $topicId;
		}
		OperationDB::operationDataBase($params, 'GroupTopics', 'delete');
		return TRUE;
	}

}
