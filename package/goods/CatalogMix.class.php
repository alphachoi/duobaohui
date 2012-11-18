<?php
namespace Snake\Package\Goods;
Use Snake\Package\Goods\Helper\DBCatalogHelper AS DBCatalogHelper;

define('CAT_PLENGTH', 3);   //每一级的长度
define('CAT_LEVEL', 5);     //级数

class CatalogMix {

	/**
	 * 页码
	 *
	 * @var int 
	 * @access private
	 */
	private $pid = 0;

	/**
	 * 设置要获取的类目id
	 * 
	 * @param int $var1 參數1
	 * @return void
	 * @access public
	 */	
	public function __construct($pid) {
		$this->pid = $pid;	
	}

	/**
	 * 获取与该分类同级别的ID范围
	 * @param $cid
	 */
	function getIdRange() {

		$ret = array();
		$ret['up'] = '1' . str_repeat('0', (CAT_LEVEL - 1) * CAT_PLENGTH);
		$ret['down'] = str_repeat('9', CAT_PLENGTH) . str_repeat('0', (CAT_LEVEL - 1) * CAT_PLENGTH);
		$ret['deep'] = CAT_LEVEL;

		if( empty($this->pid) ) {
			return $ret;
		}

		$searchDeep = 0;
		$searchPid = $this->pid;
		while ( substr($searchPid, -CAT_PLENGTH, CAT_PLENGTH) == str_repeat('0', CAT_PLENGTH) ) {
			$searchDeep += 1;
			$searchPid = substr($searchPid, 0, -CAT_PLENGTH);
		}
		if ( $searchDeep == 0 ) {
			//die('不支持超过' . CAT_LEVEL . '级的分类');
			return FALSE;
		}
		$len = ($searchDeep - 1) * CAT_PLENGTH;
		$len2 = $len + CAT_PLENGTH;

		$begin = str_pad('1', CAT_PLENGTH, '0', STR_PAD_LEFT );	//如：001
		$end = str_repeat('9', CAT_PLENGTH);	//如：999
		$ret['up'] = substr_replace( $this->pid, $begin . str_repeat('0', $len), -$len2, $len2 );
		$ret['down'] = substr_replace( $this->pid, $end . str_repeat('0', $len), -$len2, $len2 );
		$ret['deep'] = $searchDeep;
		return $ret;
	}


}
