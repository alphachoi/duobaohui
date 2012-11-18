<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 

class CataWords {
	//要查询的字段
	private $fields = array();				
	//查询参数
	private $params = array();

	public function setValue($name, $value) {
		$this->params[$name] = $value;	
	}

	public function setValues($values) {
		$this->params = $values;	
	}

	public function setFields(array $fields) {
		$this->fields = $fields;	
	}

	public function getCatalogInfo() {
		if (empty($this->fields)) {
			return array();
		}

		$identityObject = new IdentityObject();
		if (isset($this->params['catalog_name'])) {
			$identityObject->field('catalog_name')->eq($this->params['catalog_name']);
		}

		//设置需要获取的列
		$identityObject->col($this->fields);

		$domainObjectAssembler = new DomainObjectAssembler(KeywordsPersistenceFactory::getFactory('\Snake\Package\Goods\CatalogPersistenceFactory'));
		$catalogCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($catalogCollection->valid()) {
			$rowObj = $catalogCollection->next();	
			$catalog[] = $rowObj->getRow();
		}
		return $catalog;
	}   
}
