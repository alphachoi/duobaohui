<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，增加我的标签
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Label\Label;
Use \Snake\Package\User\User;
Use \Snake\Package\Spam\MaskWords;

class Label_operation extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $labelIds = array();
	private $addLabelIds = array();
	private $deleteLabelIds = array();
	private $newLabelNames = array();
	private $cache = TRUE;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		//处理添加标签
		if (!empty($this->addLabelIds)) {
			//先删除所有标签，再根据回传的标签添加新的标签
			$user = new User();
			$labels = $user->getUserLabel($this->userId, array('label_id'));
			if (!empty($labels)) {
				foreach ($labels as $key => $value) {
					Label::getInstance()->deleteLabel($this->userId, (int) $labels[$key]['label_id']);
				}
			}
			//print_r($this->addLabelIds);die;
			$addResult = array();
			foreach ($this->addLabelIds as $key => $value) {
				$addResult[] = Label::getInstance()->addnewLabel($this->userId, (int) $this->addLabelIds[$key]);
			}
		}
		//如果标签为空，则删除原有所有标签
		else {
			$user = new User();
			$labels = $user->getUserLabel($this->userId, array('label_id'));
			if (!empty($labels)) {
				foreach ($labels as $key => $value) {
					$delResult[] = Label::getInstance()->deleteLabel($this->userId, (int) $labels[$key]['label_id']);
				}
			}
		}
		//处理添加用户自定义标签
		if (!empty($this->newLabelNames)) {
			$newResult = array();

			foreach ($this->newLabelNames as $key => $value) {
				$maskWordsHelper = new MaskWords($this->newLabelNames[$key]);
				$result = $maskWordsHelper->getMaskWords();
				if (!empty($result) && $result['typeFlag'] == 1) {
					$this->newLabelNames[$key] = $result['maskedContent'];
					if (empty($this->newLabelNames[$key])) {
						continue;
					}
				}   
				elseif (!empty($result) && $result['typeFlag'] == 2) {
					continue;
				}   
				$newResult[] = Label::getInstance()->addCustomLabel($this->newLabelNames[$key], $this->userId);
			}
		}
		if (!empty($addResult) || !empty($delResult) || !empty($newResult)) {
			$cacheKey = 'person:label_' . $this->userId;
			$memKey2 = 'person_label_' . $this->userId;
			$memKey = 'CacheKey:User_statistic:' . $this->userId;
			$cacheHelper = Memcache::instance();
			$cacheHelper->delete($cacheKey);
			$cacheHelper->delete($memKey);
			$cacheHelper->delete($memKey2);
		}
		$this->view = array('status' => 1);
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
		if (empty($this->userId)) {
			$this->setError(400, 40101, 'please login first, then add label');
			return FALSE;
		}
		$addLabelIdStr = $this->request->REQUEST['addLabel'];
		$newLabelNameStr = $this->request->REQUEST['newLabel'];
		$this->addLabelIds = !empty($addLabelIdStr) ? explode(',', $addLabelIdStr) : 0;
		$this->newLabelNames = !empty($newLabelNameStr) ? explode(',', $newLabelNameStr) : 0;
		$totalLabelNums = count($this->addLabelIds) + count($this->newLabelNames);
		//标签数量不能大于20
		if ($totalLabelNums > 21) {
			$this->setError(400, 40102, 'The total label num should less than 20');
			return FALSE;
		}
		return TRUE; 
	}
}
