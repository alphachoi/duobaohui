<?php
namespace Snake\Modules\Connect;

/**
 * @package connect 互联接口
 * @author yishuliu@meilishuo.com
 * 用户互联授权获得request_token, 并用request_token换取access_token的过程
 *
 **/

use \Snake\Package\Connect\ConnectFactory AS ConnectFactory;
use \Snake\Package\Shareoutside\ShareHelper;
use \Snake\Package\Connect\WeiboAuth;

class Share_to_weibo extends \Snake\Libs\Controller {

    private $userId = NULL;
	private $type = NULL;
	private $refer = NULL;
	private $Ip = NULL;
	private $state = NULL;
	private $requestParams = array();
	private $queryCookie = NULL;
	private $Santor = NULL;
	protected $outSites = array('renren', 'weibo', 'qzone', 'baidu', 'taobao', 'wangyi', 'txweibo', 'douban');
    
    public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$weiboAuthHelper = new WeiboAuth();
		$result = $weiboAuthHelper->shareToWeibo($this->Santor);
		ShareHelper::sync('', '', '', 3, 0, $result['content'], array($result['access_token']), array('image' => $result['image']));
        $this->view = array('status' => 1);
		return TRUE;
    }   

	/*
     * 初始化变量
     */
    private function _init() {
		//$this->setRefer();
		$this->setSantorini();
		if (empty($this->Santor)) {
			$this->setError(400, 40109, 'santorini_mm is empty');
		}
        return TRUE;
    }

	private function setSantorini() {
		$this->Santor = isset($this->request->COOKIE['santorini_mm']) ? $this->request->COOKIE['santorini_mm'] : '';
		return TRUE;
	}
}
