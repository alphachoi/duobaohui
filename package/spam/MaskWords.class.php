<?php
namespace Snake\Package\Spam;

use \Snake\Package\Spam\Helper\DBSpamWordsHelper;

/**
 * version 1.0 author 石铮
 * version 2.0 author yitengtao
 * version 3.0 author jianxu
 * moved to snake and modify by jianxu
 * 屏蔽词类
 * relational table: t_dolphin_maskwords
 */

Class MaskWords {

	const twitterContentTable = "(1, 2, 3, 4, 5)";
	const nickNameTable = "(6)";
	const goodsTitleTable = "(7)";

	/**
	 * @var string contents 传入的字符串
	 * @access private
	 */
	private $contents = NULL;

	/**
	 * @var string maskTable 词缓存表
	 * @access private
	 */
	private $maskTable = NULL;

	/**
	 * @var string table 指定词表
	 * @access private
	 */
	private $table = NULL;

	/**
	 * @var int maskLevel 屏蔽级别
	 * @access private 
	 */
	private $maskLevel = 0;

	/**
	 * @var array maskWords 屏蔽词
	 * @access private
	 */

	private $cache = NULL;

	private $tableInCache = array();
	
	/**
	 * @param string content
	 * @param string maskTable
	 * @return array
	 * @access public
	 */
	public function __construct($contents, $table = 'DFA_table') {
		$this->contents = $contents;
		$this->table = $table;
		$this->cache = \Snake\Libs\Cache\Memcache::instance();
		$this->getMaskTable();
	}

	private function getMaskTable() {
		$this->getCache() || $this->fillCache();
	}

	private function getCache() {
		$tableInCache = $this->cache->get($this->table);
		if (empty($tableInCache)) {
			return FALSE;
		}
		else {
			$this->tableInCache = $tableInCache;
			return TRUE;
		}
	}

	private function getMaskWordsFromDB() {
		switch ($this->table) {
			case 'DFA_table':
				$filter = self::twitterContentTable;
				break;
			case 'DFA_register':
				$filter = self::nickNameTable;
				break;
			case 'DFA_goodstitle':
				$filter = self::goodsTitleTable;
				break;
			default:
				$filter = self::twitterContentTable;
				break;
		}
		$sql = "SELECT /*maskWords-xj*/mask_word, mask_type FROM t_dolphin_maskwords WHERE verify IN {$filter}";
		$result = DBSpamWordsHelper::getConn()->read($sql, array());
		return $result;
	}

	private function fillCache() {
		$wordsInfo = $this->getMaskWordsFromDB();
		if (empty($wordsInfo)) {
			return FALSE;
		}
		$status_num = 0;
		foreach ($wordsInfo as $word) {
			$ptr = 0; //当前状态指针
			$word['mask_word'] = trim($word['mask_word']);
			$word['mask_word'] = strtolower($word['mask_word']);
			$length = strlen($word['mask_word']);
			for ($i = 0; $i < $length; ++$i) {
				if ((ord($word['mask_word']{$i}) & 0xf0) == 224) {
					//a chinese char contains 3 * 8bit
					$sword = $word['mask_word']{$i} . $word['mask_word']{$i+1} . $word['mask_word']{$i+2};
					$hash_num = self::hashChar($sword);
					$i += 2;
				}
				else {
					//a normal char contains 1 * 8bit
					$hash_num = self::hashChar($word['mask_word']{$i});
				} 
				if (empty($this->tableInCache[$ptr][$hash_num])) {
					//a new char has not exist in the hashtable 
					if ($i < $length - 1) {
						//the 1st or 2nd char in a chinese word
						++$status_num;
						$this->tableInCache[$ptr][$hash_num] = $status_num;
						$ptr = $status_num; 
					}
					else {
						//a normal char or the 3rd char in a chinese word
						$this->tableInCache[$ptr][$hash_num] = -1 - intval($word['mask_type']);
					} 
				}
				elseif ($this->tableInCache[$ptr][$hash_num] < 0) {
					//TODO now, the 'abcd' is not work is exist 'abc';
					break;
				}
				else {
					$ptr = $this->tableInCache[$ptr][$hash_num]; 
				} 
			}
		}

		$this->cache->set($this->table, $this->tableInCache, 86400);

	}

	//hash the character
	private function hashChar($word) { 
		switch(strlen($word)) { 
			case 1:  //this is non-chinese character, just return the ascii code
				return ord($word);
				break;
			case 3:  //if chinese character, the hash = ((first bit)-224)*64*64+((second bit)-128)*64+(third bit) 
				$ret = ((ord($word{0}) & 0x1f) << 12) + ((ord($word{1}) & 0x7f) << 6) + (ord($word{2}) & 0x7f); 
				return $ret;
		}
	}
	
	/**
	 * @return array
	 * @access public
	 */
	public function getMaskWords() {
		$string = strtolower($this->contents);
		$length = strlen($string);
		$maskWords = array();
		$typeFlag = 0;
		for ($i = 0; $i < $length; ++$i) {
			$ptr = 0;
			if ((ord($string{$i}) & 0xf0) == 224) {
				$sword = $string{$i} . $string{$i+1} . $string{$i+2};
				$hash_num = self::hashChar($sword);
				$i += 2;
				$temp = -2;
			}
			else {
				$hash_num = self::hashChar($string{$i});
				$temp = 0;
			}
			$j = $i + 1;
			while (isset($this->tableInCache[$ptr][$hash_num])) {
				$ptr = $this->tableInCache[$ptr][$hash_num];
				if ($ptr < 0) {
					$maskWord = "";
					for ($k = $i + $temp; $k < $j; ++$k) {
						$maskWord .= $string{$k};
						$string{$k} = "*";
					}
					$maskType = -1 - $ptr;
					$maskWords[] = array(
						"mask_word" => $maskWord,
						"mask_type" => $maskType,
					);
					if (($maskType) > $typeFlag) {
						$typeFlag = $maskType;
					}
					break;
				}
				else {
					if ((ord($string{$j}) & 0xf0) == 224 ) {
						$sword = $string{$j} . $string{$j+1} . $string{$j+2};
						$hash_num = self::hashChar($sword);
						$j += 3;
					}
					else {
						$hash_num = self::hashChar($string{$j});
						++$j;
					}
				}
			}
		}

		$this->mergeMaskedString($string);
		$ret = array(
			"typeFlag" => $typeFlag,
			"maskedContent" => $this->contents,
			"maskWords" => $maskWords,
		);

		return $ret;
	}

	private function mergeMaskedString($string) {
		if (strlen($string) != strlen($this->contents)) {
			return TRUE;
		}
		for($i = 0; $i < strlen($this->contents); $i++){
			($string[$i] == "*") && $this->contents[$i] = "*";
		}
		$this->contents = preg_replace("/\*+/", "", $this->contents);
	}

}
