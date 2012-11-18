<?php
/**
 *  取杂志社中推的package
 *  @author huazhulin@meilishuo.com
 *  @since 2012-5
 *  @version 1.0
 */
namespace Snake\Package\Group;

Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;


class OperationDB {

	public static function operationDataBase($params, $objName, $operation = "update") {
		$factoryName = '\Snake\Package\Group\\' . $objName . "PersistenceFactory";
		$objName = "\Snake\Package\Group\Topic" . $objName . "Object";
		$domainObject = new $objName($params);
		$domainObjectAssembler = new DomainObjectAssembler($factoryName::getFactory($factoryName));
		$result = $domainObjectAssembler->$operation($domainObject);
		return $result;
	}

	public static function selectDataBase($parameters, $objName, $hashKey = "") {
		$groupCollection = self::getData($parameters, $objName, $hashKey);
        while ($groupCollection->valid()) {
            $groupObj = $groupCollection->next();
			if (!empty($hashKey)) {
				$info = $groupObj->getInfo();
				$key = $info[$hashKey];
				if (empty($key)) {
					return "hashKey is empty!";
				}
				$infomations[$key][] = $info;
			}
			else {
				$infomations[] = $groupObj->getInfo();
			}
        }
        $result = $infomations;
		return $result;
	}

	public static function selectDataBaseHV($parameters, $objName, $hashKey = "") {
		$groupCollection = self::getData($parameters, $objName, $hashKey);
        while ($groupCollection->valid()) {
            $groupObj = $groupCollection->next();
			if (!empty($hashKey)) {
				$info = $groupObj->getInfo();
				$key = $info[$hashKey];
				if (empty($key)) {
					return "hashKey is empty!";
				}
				$infomations[$key] = $info;
			}
			else {
				$infomations[] = $groupObj->getInfo();
			}
        }
        $result = $infomations;
		return $result;
	}

	private static function getData($parameters, $objName, $hashKey = "") {
		$identityObject = new IdentityObject();
		$paramsWhere = $parameters['where'];
		$extCondition = $parameters['ext_where'];
		$paramsEx = $parameters['ext_condition'];
		$fields = $parameters['fields'];
        foreach ($paramsWhere AS $params) {
            $identityObject->field($params['key'])->$params['operation']($params['value']);
        }   
        if (!empty($extCondition)) {
            foreach ($extCondition AS $params) {
                $identityObject->field($params['key'])->$params['operation']($params['value']);
            }   
        }   
		if (!empty($paramsEx)) {
			foreach ($paramsEx AS $key => $value) {
				if (!empty($value)) {
					$identityObject->$key($value);
				}   
			}
		}
        $identityObject->col($fields);
		$factoryName = '\Snake\Package\Group\\' . $objName . "PersistenceFactory";
        $domainObjectAssembler = new DomainObjectAssembler($factoryName::getFactory($factoryName));
        $groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
		return $groupCollection;
	}


}
