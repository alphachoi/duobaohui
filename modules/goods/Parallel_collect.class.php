<?php
namespace Snake\Modules\Goods;
Use \Snake\Libs\Base\MultiClient;
Use \Snake\Package\Goods\Goods;

/**
 * 单推页面的主页面展现
 * @package goods
 * @author weiwang
 * @since 2012.09.03
 * @example curl 
 */
class Parallel_collect extends \Snake\Libs\Controller{

	public function run() {
		//$tids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : 0;
		//$mergedTids = explode(",", $tids);
		$mergedTids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : array();

		$userId = isset($this->request->REQUEST['uid']) ? (int)$this->request->REQUEST['uid'] : 0;

		$client = MultiClient::getClient($userId);
        $collectInfo = array();
        $myCollectInfo = array();
		$stat = array(
				'multi_func' => 'twitters_stat',
				'method' => 'POST',
				'twitter_id' => implode(',', $mergedTids),
				'self_id' => $userId,
				);
		$collect = array(
				'multi_func' => 'twitter_likes_state',
				'method' => 'GET',
				'twitter_id' => implode(',', $mergedTids),
				'user_id' => $userId,
				'self_id' => $userId,
				);
		if (!empty($userId)) {
			list($tempInfo, $myCollectInfo) = $client->router(
					array($stat, $collect)
					);
		}
		else {
			list($tempInfo, $myCollectInfo) = $client->router(
					array($stat)
					);
			$myCollectInfo = array();
		}
		foreach ($mergedTids AS $mergedTid) {
			if (!empty($tempInfo[$mergedTid])) {
				$collectInfo[$mergedTid] = $tempInfo[$mergedTid];
			}
			else {
				$collectInfo[$mergedTid] = array(
					'twitter_id' => $mergedTid,
					'twitter_goods_id' => 0,
					'count_discuss' => 0,
					'count_forward' => 0,
					'beauty' => array(
						'num' => 0,
					)
				);
			}
		}
		$this->view = array($collectInfo, $myCollectInfo);
		return TRUE;
	}

}
