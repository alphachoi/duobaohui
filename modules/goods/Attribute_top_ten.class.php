<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Goods\PopularItems;
/**
 * 
 * 10个top属性词的接口类
 * 
 * 取得后台维护的10个人气属性词排行
 *
 * @author Xuan Zheng
 * @package 宝库
 * @request_url http://snake.meilishuo.com/goods/attribute_top_ten
 * @request_method GET
 * @request_param NULL
 */
class Attribute_top_ten extends \Snake\Libs\Controller {
	
//	public function run_bk() {
//		$fakeData = array(
//			array('word_name' => '连衣裙', 'word_id' => 34295, 'total' => 92325, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => 'T恤', 'word_id' => 34347, 'total' => 102601, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => '连体裤', 'word_id' => 34404, 'total' => 31613, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => '雪纺衫', 'word_id' => 34339, 'total' => 85934, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => '凉鞋', 'word_id' => 34476, 'total' => 79825, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => '欧美', 'word_id' => 33887, 'total' => 132156, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => '韩系', 'word_id' => 33885, 'total' => 88364, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			array('word_name' => 'H&M', 'word_id' => 34722, 'total' => 18610, 'tag' => '?frm=section4_hotWords', 'isBrand' => TRUE),
//			array('word_name' => 'ZARA', 'word_id' => 34721, 'total' => 23704, 'tag' => '?frm=section4_hotWords', 'isBrand' => TRUE),
//			array('word_name' => '呛口小辣椒', 'word_id' => 36165, 'total' => 38156, 'tag' => '?frm=section4_hotWords', 'isBrand' => FALSE),
//			);
//		$this->view = $fakeData;
//		return TRUE;
//	}

	/**
	 * 接口(一系列艰苦的心路历程)
	 * @access public
	 * @param NULL
	 * @return boolean
	 */
	public function run() {
		$response = $this->getTopWords();
		$this->view = $response;
		return TRUE;
	}

	/**
	 * 取得置顶数据
	 * @param NULL
	 * @return array
	 */
	private function getTopWords() {
		$popularItems = new PopularItems();
		$items = $popularItems->getPopularItemData();

		$wordNames = array();
		foreach ($items as $item) {
			$wordNames[] = $item['item_name'];
		}
		$params = array();
		$params['word_name'] = $wordNames;
		$params['isuse'] = 1;
		$wordInfosTmp = AttrWords::getWordInfo($params, "word_name,word_id,label_id");

		foreach ($wordInfosTmp as $wordTmp) {
			if (AttrWords::IsBrandWordsByLabel($wordTmp['label_id'])) {
				$wordTmp['isBrand'] = TRUE;	
			}
			else {
				$wordTmp['isBrand'] = FALSE;
			}
			//改版新加,unset label_id
			unset($wordTmp['label_id']);
			$wordInfos[$wordTmp['word_name']] = $wordTmp;	
		}

		$wordData = array();
		foreach ($items as $i) {
			if (empty($wordInfos[$i['item_name']])) {
				continue;
			}
			$wordDataTmp = array();
			$wordDataTmp['word_name'] = $i['item_name'];
//			$wordDataTmp['word_id'] = $wordInfos[$i['item_name']]['word_id'];
			$wordDataTmp['total'] = $i['item_number'];
			$wordDataTmp['url'] = $i['item_link'];
//			$wordDataTmp['isBrand'] = $wordInfos[$i['item_name']]['isBrand'];
			$wordData[] = $wordDataTmp;
		}
		return $wordData;	
	}
}
