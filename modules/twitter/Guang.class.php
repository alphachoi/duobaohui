<?php
namespace Snake\Modules\Twitter;
Use Snake\Package\User\User							AS User;
Use Snake\Package\Twitter\Twitter					AS Twitter;
Use \Snake\libs\Cache\Memcache;  

class Guang extends \Snake\Libs\Controller {
	private $page	= 0;
	private $frame	= 0;
	private $cataId = 0;

	public function run() {
		$this->_init();
        $this->view		= $this->_getTwitter();
    }

	private function _init(){

		!empty($this->request->REQUEST['page'])		&& $this->page = $this->request->REQUEST['page'];
		!empty($this->request->REQUEST['frame'])	&& $this->frame = $this->request->REQUEST['frame'];
		!empty($this->request->REQUEST['cata_id'])	&& $this->cataId = $this->request->REQUEST['cata_id'];
	}

	private function _getTwitter(){
		$limit			= 20;
		$pageFrame		= 6;		// 一页6个 frame
		$offset			= ($this->page * $pageFrame + $this->frame) * $limit;
		$aTwitterList	= Twitter::getInstance()->getTwitterList($this->cataId,$offset,$limit);
        return $aTwitterList;
	}
}
