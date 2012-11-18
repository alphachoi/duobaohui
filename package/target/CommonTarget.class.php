<?php
namespace Snake\Package\Target;

class CommonTarget {

	private $uri = '';


	/**
	 * @param $uri /guang/catalog/dress?cata_id=2000000000000
	 *
	 */
	public function __construct($uri = '') {
		$this->uri = $uri;	
	}

	public function getTargetUri() {
		return $this->uri;
	}

	/**
	 * 生成web对应wap链接
	 */
	public function targetMap() {
		if (empty($this->uri)) {
			return '';
		}
		$uri = trim($this->uri, '/');
		//统计frm
		$frmPos = strpos($uri, 'frm=');
		$frmStr = '';
		if ($frmPos !== FALSE) {
			$frmStr = substr($uri, $frmPos);	
			$uri = substr($uri, 0, $frmPos - 1);
		}

		$urlArray = explode('/', $uri);	
		$action = array_shift($urlArray);
		$method = array_shift($urlArray);
		$third = array_shift($urlArray);
		
		//http://www.meilishuo.com/share/627881449?wzz=p0r0c1, method都会有?
		$method = str_replace('?', '&', $method);
		//http://www.meilishuo.com/guang/catalog/shoes?cata_id=6000000000000&frame=0&price=all&section=hot&page=0
		//http://www.meilishuo.com/guang/attr/33899?frm=section4_keyWords
		$pos = strpos($third, '?');
		if ($pos !== FALSE) {
			$third = substr($third, 0, $pos);
		}

		$map = array(
			'goods' => array(
				'popular' => '/wapHot/hot',
				'list' => '/wapHot/hot',
				'hot' => '/wapHot/latest',
				'catelog' => '/wapCatalog/' . $third,
			),  
			'guang' => array(
				'hot' => '/wapHot/hot',
				'new' => '/wapHot/latest',
				'catalog' => '/wapCategory/' . $third,
				'attr' => '/wapAttrNew/attr?attr_id=' . $third, 
			), 
			'group' => '/wapGroup/group?group_id=' . $method,
			'share' => '/wapSingleGoods/show?t=' . $method,		
			'book' => '/wapBook/book?book_id=' . $method,
		);
		
        $size_3 = array('guang', 'goods');  
        $size_2 = array('group', 'share', 'book');
        if (in_array($action, $size_3) && isset($map[$action][$method])) {
            $uri = $map[$action][$method];
        }   
        elseif (in_array($action, $size_2) && isset($map[$action])) {
            $uri = $map[$action];
        }
        else {
            $uri = '';
        }

		//存在frm
		if (!empty($frmStr)) {
			$frmPrefix = '?';
			if (strpos($uri, '?') !== FALSE) {
				$frmPrefix = '&';	
			}
			$uri = $uri . $frmPrefix . $frmStr;
		}
		$this->uri = $uri;
	}
}
