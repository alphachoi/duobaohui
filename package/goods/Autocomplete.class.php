<?php
namespace Snake\Package\Goods;

class Autocomplete{

	private $word = "";

    public function __construct($word = "") {
		$this->word = $word;
	}

	public function complete() {
		if (empty($this->word)) {
			return array();
		}
		$index = rand(0, count($GLOBALS['AUTOCOMPLETE']) - 1);
		$completeUrl = "http://" . $GLOBALS['AUTOCOMPLETE'][$index]['host'] . ":" . $GLOBALS['AUTOCOMPLETE'][$index]['port'] . "/search/prompt?query=" . $this->word;
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $completeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
        $completeData = curl_exec($ch);		
		return json_decode($completeData);	
	}
	
}
