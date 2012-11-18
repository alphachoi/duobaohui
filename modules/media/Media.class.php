<?php
namespace Snake\Modules\Media;

/**
 * 关于我们页新闻报道数据
 * @author yishuliu@meilishuo.com
 * @since 2012-08-02
 * @version 1.0
 */

Use \Snake\Package\Media\Media;			
Use \Snake\Libs\Cache\Memcache;

class Media extends \Snake\Libs\Controller {
	private $visitedUserId = NULL;
	private $cache = 0; //TRUE;

	public function run()  {
		//$init = $this->_init();
		$mediaInfo = $this->_getMediaInfo();
		if (empty($mediaInfo)) {
			$this->view = array();
			return FALSE;
		}
		$this->view = $mediaInfo;
	}
	
	private function _init() {
		return;
		//current login userId
		$this->visitedUserId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($this->visitedUserId)) {
			$this->setError(400, 40101, 'userId is empty');
			return FALSE;
		}
		return $this->visitedUserId;
	}
	
	/**
	 *	cache handle
	 **/
	private function _getMediaInfo() {
		$cacheKey = 'Media:media';
		$cacheHelper = Memcache::instance();
		$mediaInfo = $cacheHelper->get($cacheKey);
		if ($this->cache && !empty($mediaInfo)) {
			return $mediaInfo;
		}
		$mediaHelper = new Media();
		$mediaInfo = $mediaHelper->getMedia();
		if (empty($mediaInfo)) {
			//$this->setError(400, 40104, 'user is not exist or user medal is empty');
			return FALSE;
		}
		$cacheHelper->set($cacheKey, $mediaInfo, 3600);
		return $mediaInfo;
	}

}
