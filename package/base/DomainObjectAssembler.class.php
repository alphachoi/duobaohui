<?php
namespace Snake\Package\Base;

class DomainObjectAssembler {
    
    function __construct(PersistenceFactory $factory) {
        $this->factory = $factory;
	}

	/**
	* 从mlsstorage中获取数据
	* @return 
	* @param IdentityObject
	*/
	function storageMultiRowFind(IdentityObject $idobj) {
        $selfact = $this->factory->getQueryFactory();
        $fields = $selfact->getFields($idobj);
		$forceIndex = $selfact->getForceIndex();
        $raw = $this->factory->getMapper()->storageMultiRowGet($selfact::TABLE, $keyName, $keyVal, $filter, $forceIndex, $start, $limit, $orderBy, $columnNames);
		return $this->factory->getCollection($raw);
	}

	function storageUniqRowFind(IdentityObject $idobj, $keyVals, $filter) {
		$selfact = $this->factory->getQueryFactory();
        $fields = $selfact->getFields($idobj);
		$comps = $idobj->getComps();
		$keyName = $comps[0]['name'];
		$raw = $this->factory->getMapper()->storageUniqRowGet($selfact::TABLE, $keyName, $keyVals, $filter, $fields);
		return $this->factory->getCollection($raw);
	}

	/**
	* 从mlsstorage中获取数据
	* @return 
	* @param IdentityObject
	*/
	function storageFind(IdentityObject $idobj) {
		$idobj->setPrefix(":");
        $selfact = $this->factory->getQueryFactory();
        $fields = $selfact->getFields($idobj);
        list ($selection, $values) = $selfact->newSelection($idobj);
		$sql = strtr($selection, $values);
        $raw = $this->factory->getMapper()->storageGet($selfact::TABLE, $fields, $sql);
		return $this->factory->getCollection($raw);
	}

	function storageQueryRead(IdentityObject $idobj, $hashKey = "") {
		$idobj->setPrefix(":");
        $selfact = $this->factory->getQueryFactory();
        $fields = $selfact->getFields($idobj);
        list ($selection, $values) = $selfact->newSelection($idobj);
		$sql = strtr($selection, $values);
        $raw = $this->factory->getMapper()->storageQueryRead($sql, $haskKey);
		return $this->factory->getCollection($raw);
	}

    function findOne(IdentityObject $idobj) {
        $collection = $this->find($idobj);
        return $collection->next();
    }
	/**
	* 从mysql中获取数据
	* @return Collection
	* @param IdentityObject
	*/
    function mysqlFind(IdentityObject $idobj) {
        $selfact = $this->factory->getQueryFactory();
        list ($selection, $values) = $selfact->newSelection($idobj);
        $raw = $this->factory->getMapper()->get($selection, $values); 
        return $this->factory->getCollection($raw);
    }
	/**
	* 向memcache中插入数据
	* @return boole true/false
	* @param DomainObject
	*/
	function put(Collection $collection, MemcacheIdentityObject $memIdobj) {
        return $this->factory->getMemcache($memIdobj)->put($collection);
	}
	/**
	* 向mysql中插入数据
	* @return DomainObject 
	* @param DomainObject
	*/

	function insert(DomainObject $obj) { 
        $upfact = $this->factory->getQueryFactory();  
        list($update, $values) = $upfact->newUpdate($obj);  
		$id = $this->factory->getMapper()->insert($update, $values);
		if (method_exists($obj, "getId")) {
			$oldId = $obj->getId();
			if (!isset($oldId)) { 
				$obj->setId($id);  
			}   
		}
		return $id;
        //$obj->markClean();*/
    }   	

	function update(DomainObject $obj) {
		$upfact = $this->factory->getQueryFactory();
		list($update, $values) = $upfact->newUpdate($obj);
		$result = $this->factory->getMapper()->update($update, $values);
		return $result;
	}

	function delete(DomainObject $obj) {
		$upfact = $this->factory->getQueryFactory();
		list($update, $values) = $upfact->delete($obj);
		$result = $this->factory->getMapper()->update($update, $values);
		return $result;
	}
}
?>
