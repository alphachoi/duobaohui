<?php
namespace Snake\Package\Goods;
/**
 * 海报页面加tag
 * @author xuanzheng@meilishuo.com
 */

class Tag {
	//private $posters = array();

	/**
	 * 为海报添加wzz tag
	 * @author xuanzheng@meilishuo.com
	 * @param array
	 * @param int
	 * @return array
	 */
	static public function addTagWzz($posters, $frame, $page) {
		if (empty($posters) || $frame != 0) {
			return $posters;
		}	
		$p = $page;
		$r = 0;
		$c = 0;
		foreach($posters as $k => &$poster) {
			if (empty($poster['url'])) {
				$poster['url'] = "/share/{$poster['twitter_id']}?wzz=p{$p}r{$r}c{$c}";
			}
			else {
				if (strpos($poster['url'], "?") !== FALSE) {
					$poster['url'] = $poster['url'] . "&wzz=p{$p}r{$r}c{$c}";
				}
				else {
					$poster['url'] = $poster['url'] . "?wzz=p{$p}r{$r}c{$c}";
				}
			} 
			$c++;
			if ($c == 4) {
				$c = 0;	
				$r++;
			}
		}
		return $posters;
	}
	


}
