<?php
namespace Snake\Scripts;
//This is a demo, code the run function

use \Snake\Libs\Base\ZooClient;
use \Snake\Package\Goods\AttrWords;
use \Snake\Package\Goods\Attribute;

class UpdateAttrMix extends \Snake\Libs\Base\Scripts {
	
	protected $attrIds = array();
	protected $client = NULL;

	public function run() {
		$this->setClient();
		$this->getAttrIds();
		$this->getAttrTwitters();

	}

	private function setClient() {
		$this->client = ZooClient::getClient(0);
	}

	private function getAttrIds() {
		$this->attrIds = AttrWords::getWordInfo(array('all'));
	}

	private function getAttrTwitters() {
		foreach ($this->attrIds as $attrId) {
			$attrRequest = array(
				array(
					'word_id' => $attrId['word_id'], 
					'word_name' => $attrId['word_name']
				)
			);
			$attrWordsInfo = Attribute::getTwittersByAttrIds($attrRequest, 0 ,9, 'weight');
			if (!empty($attrWordsInfo[$attrId['word_id']]['tid'])) {
				$twitterIds = $attrWordsInfo[$attrId['word_id']]['tid'];
				if (!empty($twitterIds)) {
					$this->client->push_group_twitter($attrId['word_id'], 'attr', $twitterIds);
				}
			}
		}
	}

}
