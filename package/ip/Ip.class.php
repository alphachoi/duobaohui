<?php
namespace Snake\Package\Ip;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class Ip{

	private $ip = ''; 

	private static $instance = NULL;

	private function __construct() {
	}

	public static function instance(){
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function setIp( $ip = '') {
		$this->ip = $ip;	
		return TRUE;
	}

	private function getIpInfo(IdentityObject $identityObject, $collection = FALSE) {
		$domainObjectAssembler = new DomainObjectAssembler(IpPersistenceFactory::getFactory('\Snake\Package\Ip\IpPersistenceFactory'));
		$IpCollection = $domainObjectAssembler->mysqlFind($identityObject);

		if ($collection) {
			return $twitterCollection;
		}
		$ip = array();	
		while ($IpCollection->valid()) {
			$rowObj = $IpCollection->next();	
			$ip[] = $rowObj->getRow();
		}
		return $ip;
	}

	public function getCountryByIp() {
		    $ipInt = ip2long($this->ip);
			$identityObject = new IdentityObject();
			$identityObject->field('from_ip')->lte($ipInt);
			$identityObject->orderby(' from_ip DESC ');
			$identityObject->limit(1);
			$identityObject->col(array('city', 'city_name'));
			$ipInfos = $this->getIpInfo($identityObject);
			//mb_substr($result, 0, 2, 'UTF-8');
			if (!empty($ipInfos[0])) {
				$country = $ipInfos[0];
			}
			return $country;
	}

	public function checkIpByPlace( $placeName = '') {
		$placeName = trim($placeName);
		if (empty($placeName)) {
			return FALSE;
		}
		$at = FALSE;
		$city = $this->getCountryByIp();
		$city = $city['city'];
		if(strpos($city, $placeName) !== FALSE) {
			$at = TRUE;
		}
		return $at;	
	}

	public function checkIpByCityName( $placeName = '') {
		$placeName = trim($placeName);
		if (empty($placeName)) {
			return FALSE;
		}
		$at = FALSE;
		$city = $this->getCountryByIp();
		$city = $city['city_name'];
		if(strpos($city, $placeName) !== FALSE) {
			$at = TRUE;
		}
		return $at;	
	}

}
