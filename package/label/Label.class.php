<?php
namespace Snake\Package\Label;

Use Snake\Package\Label\Helper\DBLabelHelper 	   AS DBLabelHelper;

class Label {
	
	private $label = array();
	private static $instance = NULL;
	private $table = 't_dolphin_personal_label';
	private $labelTable = 't_dolphin_label_info';
    
    /** 
     * @return Label Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new Label(); 
        }   
        return self::$instance;
    }   

	private function __construct() {
    }   

	public function __get($name) {
        if (array_key_exists($name, $this->label)) {
            return $this->label[$name];
        }   
        return NULL;
    }   
    
    public function __set($name, $value) {
        $this->label[$name] = $value;
    }   

    public function getLabel() {
        return $this->label;
    }   

    public function getLabelInfoByType($type) {
        $sqlComm = "select * from t_dolphin_label_info where type !=:type";
        $sqlData['type'] = $type;
        $result = array();
		$result = DBLabelHelper::getConn()->read($sqlComm, $sqlData);
        return $result;
    } 

	public function addnewLabel($userId, $labelId) {
        if (empty($userId) || empty($labelId)) {
            return FALSE;
        }   
        $sqlComm = "INSERT IGNORE INTO t_dolphin_personal_label VALUES (:user_id, :label_id,:ctime)";

		$sqlData = array();
        $sqlData['user_id'] = $userId;
        $sqlData['label_id'] = $labelId;
        $sqlData['ctime'] = time();
		$result = DBLabelHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }   


	public function getUserLabel($userIds, $fields = array('user_id', 'label_id')) {
		if (empty($userIds) || empty($fields)) {
			return FALSE;
		}
		!is_array($userIds) && $userIds = array($userIds);
		!is_array($fields) && $fields = array($fields);

		$userIds = implode(',', $userIds);
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE user_id IN ({$userIds})";
		$result = array();
		$result = DBLabelHelper::getConn()->read($sqlComm, array());
		return $result;
	}


    public function checkUserHasLabel($userId, $labelId) {
        $sqlComm = "SELECT * FROM t_dolphin_personal_label WHERE user_id =:user_id AND label_id =:label_id";
        $sqlData['user_id'] = $userId;
        $sqlData['label_id'] = $labelId;
        $result = array();
		$result = DBLabelHelper::getConn()->read($sqlComm, $sqlData);
		if (!empty($result[0]['user_id']) && !empty($result[0]['ctime'])) {
			return TRUE;
		}
        return FALSE;
    } 

	public function deleteLabel($userId, $labelId) {
        if (empty($userId) || empty($labelId)){
            return FALSE;
        }
        $sqlComm = "DELETE FROM t_dolphin_personal_label where user_id=:user_id and label_id =:label_id";

        $sqlData['user_id'] = $userId;
        $sqlData['label_id'] = $labelId ;
		$result = DBLabelHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }   

	public function getLabelByName($labelName, $fromMaster = FALSE, $hashKey = TRUE) {
        $sqlComm = "SELECT * FROM t_dolphin_label_info WHERE label_name=:label_name";
        $sqlData['label_name'] = $labelName;
        $result = array();
		if (!empty($hashKey)) {
			$result = DBLabelHelper::getConn()->read($sqlComm, $sqlData, $fromMaster, 'label_id');
		}
		else {
			$result = DBLabelHelper::getConn()->read($sqlComm, $sqlData, $fromMaster);
		}
        return $result;
    }   

	public function addLabel($labelName, $type) {
        $sqlComm = "INSERT INTO t_dolphin_label_info (label_name, type, ctime) VALUES (:label_name, :type, :ctime)";
		
		$sqlData = array();
        $sqlData['label_name'] = $labelName;
        $sqlData['type'] = $type;
        $sqlData['ctime'] = $_SERVER['REQUEST_TIME'];
		
		DBLabelHelper::getConn()->write($sqlComm, $sqlData);
		$insertResult = DBLabelHelper::getConn()->getAffectedRows();
		if (!empty($insertResult)) {
			$insertId = DBLabelHelper::getConn()->getInsertId();
		}
		else {
			$insertId = $insertResult;
		}
		
		return $insertId;
    }

	public function addCustomLabel($labelName, $userId) {
		$labelInfo = $this->getLabelByName($labelName, TRUE, FALSE);
        if (!empty($labelInfo[0])) {
            $label = $labelInfo[0];
            $this->addnewLabel($userId, $label['label_id']);
            return $label['label_id'];
        }   
        $labelId = $this->addLabel($labelName, 5); 
        $this->addnewLabel($userId, $labelId);
        return $labelId;
	}
} 
