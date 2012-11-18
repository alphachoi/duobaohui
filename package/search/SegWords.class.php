<?php
namespace Snake\Package\Search;
Use Snake\Package\Goods\AttrWords;

class SegWords{
	private $string = '';
	
    static function segword($str) {
		//$searchObj = new Search();
		$segObj = new Segmentation();
		$sc = $segObj->_getSegClient();
        $sc->SetLimits(0, 1, 1);
		$searchRes = $segObj->queryViaValidConnection($str, 'goods_id_dist');
		if( $searchRes == false ) {
			return false;
		}
		//var_dump($searchRes['words']);
		//return array_keys( $searchRes['words'] );
		if (!empty($searchRes['words'])) {
			return array_keys($searchRes['words']);
		}
		else {
			return array();
		} 
	}

	public function segwordToAttr($title = '') {
		$words = self::segword($title);

		$params = array();
		$params['word_name'] = $words;
		$params['isuse'] = 1;
		$wordInfos = AttrWords::getWordInfo($params, "word_id");
		$aids = array();
		if (is_array($wordInfos)) {
			foreach ($wordInfos as $winfo) {
				$aids[] = $winfo['word_id'];
			}
		}
		return $aids;
	}
}
