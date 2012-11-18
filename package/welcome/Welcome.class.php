<?php
namespace Snake\Package\Welcome;

/**
 * welcome 数据库链接基类
 * @ignore
 */
use \Snake\Package\Welcome\Helper\DBWelcomeHelper;
Use \Snake\Package\Picture\PictureConvert;

class Welcome {
 
	protected $pageType = NULL;
	protected $dateType = NULL;
	protected $dateTypeExt = NULL;
	protected $twitterType = NULL;
	protected $limit = 0;
	protected $orderBy = 'sortno';
	protected $dataId = NULL;

	public $values = array();

	protected function setPageType($pageType = 0) {
		$this->pageType = $pageType;
	}

	protected function setDateType($dateType = 0) {
		$this->dateType = $dateType;
	}

	protected function setTwitterType($twitterType = 0) {
		$this->twitterType = $twitterType;
	}

	protected function setDateTypeExt($dateTypeExt = 0) {
		$this->dateTypeExt = $dateTypeExt;
	}

	protected function setLimit($limit = 0) {
		$this->limit = $limit;
	}

	protected function setOrderBy($orderBy = NULL) {
		$this->orderBy = $orderBy;
	}

	protected function setDataId($dataId = 0) {
		$this->dataId = $dataId;
	}

	protected function getWelcomeSection($col) {
		list($sql, $sqlData) = $this->getSql($col);
		$values = DBWelcomeHelper::getConn()->read($sql, $sqlData);
		return $values;
	}

	private function getSql($col) {
		$sql = "SELECT /*welcome-xj*/{$col} FROM t_dolphin_cms_index_welcome WHERE 1";
		$sqlData = array();

		$whereAndLimit = new WhereAndLimit();
		$whereAndLimit->addFilter(new pageTypeFilter(), $this->pageType)
					  ->addFilter(new dateTypeFilter(), $this->dateType)
					  ->addFilter(new dateTypeExtFilter(), $this->dateTypeExt)
					  ->addFilter(new twitterTypeFilter(), $this->twitterType)
					  ->addFilter(new dataIdFilter(), $this->dataId)
					  ->addFilter(new orderByFilter(), $this->orderBy)
					  ->addFilter(new limitFilter(), $this->limit);
		$whereAndLimit->setSql($sql);
		$sql = $whereAndLimit->getSql();
		return array($sql, $sqlData);
	}

	public function getPicUrl($pic) {
		$picObj = new PictureConvert($pic);
		return $picObj->getPictureO();
	}
		
}

class WhereAndLimit {
	protected $_filters = array();
	protected $sql = "";

	public function addFilter(Filter $filter, $restriction) {
		$this->_filters[] = array($filter, $restriction);
		return $this;
	}

	public function setSql($sql) {
		$this->sql = $this->_filter($sql);
	}

	protected function _filter($sql) {
		foreach ($this->_filters as $filter) {
			$sql = $filter[0]->filter($sql, $filter[1]);
		}
		return $sql;
	}

	public function getSql() {
		return $this->sql;
	}
}

interface Filter {
	public function filter($where, $restriction);
}

class pageTypeFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " AND page_type={$restriction}";
		}
		return $where;
	}
}

class dateTypeFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " AND date_type={$restriction}";
		}
		return $where;
	}
}

class dataIdFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " AND data_id={$restriction}";
		}
		return $where;
	}
}

class dateTypeExtFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " AND date_type_ext={$restriction}";
		}
		return $where;
	}
}

class twitterTypeFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " AND twitter_type={$restriction}";
		}
		return $where;
	}
}

class orderByFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " ORDER BY {$restriction}";
		}
		return $where;
	}
}

class limitFilter implements Filter {
	public function filter($where, $restriction) {
		if (!empty($restriction)) {
			$where .= " LIMIT {$restriction}";
		}
		return $where;
	}
}
