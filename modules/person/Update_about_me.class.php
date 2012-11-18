<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，更新美丽宣言
 * @author yishuliu@meilishuo.com
 **/

Use \Snake\Package\User\User;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Spam\MaskWords;

class Update_about_me extends \Snake\Libs\Controller {
	private $aboutMe = NULL;
	private $userId = NULL;
	private $cache = TRUE;

	public function run()  {
		if (!$this->_init()) {
			return FALSE;
		}
		//TODO verify about_me first by words filter
		$user = new User();
		$maskWordsHelper = new MaskWords($this->aboutMe);
		$result = $maskWordsHelper->getMaskWords();
		if (!empty($result) && $result['typeFlag'] == 1) {
			$this->aboutMe = $result['maskedContent'];
		}
		elseif (!empty($result) && $result['typeFlag'] == 2) {
			$this->aboutMe = "这里是你在美丽说的签名档，宣布你的美丽态度。";
		}
		$data = array('user_id' => $this->userId, 'about_me' => $this->aboutMe);
		$result = $user->updateUserExtInfo($data);

        $cacheKey = 'person:user_info_' . $this->userId;
		$memKey = 'User:getUserInfo:' . $this->userId;
		$memExtKey = 'User:getUserExtInfo:' . $this->userId;
		$memExtKey2 = 'User:getUserInfo:new' . $this->userId;
		$cacheMem = 'user_info_new' . $this->userId;
        $cacheHelper = Memcache::instance();
		$cacheHelper->delete($cacheKey);
		$cacheHelper->delete($cacheMem);
		$cacheHelper->delete($memKey);
		$cacheHelper->delete($memExtKey);
		$cacheHelper->delete($memExtKey2);
		$this->view = array('status' => $result);
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
        if (empty($this->userId)) {
            $this->setError(400, 40101, 'please login first, then add label');
            return FALSE;
        }
		$this->aboutMe = !empty($this->request->REQUEST['about_me']) ? $this->request->REQUEST['about_me'] : '';
		$this->aboutMe = trim($this->aboutMe);
		if (empty($this->aboutMe)) {
			$this->setError(400, 40113, 'about_me can not be empty');
			return FALSE;
		}
		if (mb_strlen($this->aboutMe, 'utf-8') > 40) {
			$this->setError(400, 40114, 'about_me should less than 40 characters');
			return FALSE;
		}
		return TRUE;
	}

}
