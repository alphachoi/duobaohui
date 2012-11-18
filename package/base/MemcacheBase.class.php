<?php
namespace Snake\Package\Base;

Use \Snake\libs\Cache\Memcache;

abstract class MemcacheBase {

	protected $memcache = NULL;
	//存储key的class 
	protected $memIdentityObject = NULL;	
	
    public function __construct(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
		$this->memcache = Memcache::instance();
		$this->memIdentityObject = $memidobj;
	}
	public static function create(\Snake\Package\Base\MemcacheIdentityObject $memidobj) {
		return new static($memidobj);
	}
	//abstract function put(\Snake\Package\Base\Collection $collection); 
    //abstract function get(); 
	abstract function del();	

	public function get() {
		$memRes = $this->memcache->getMulti($this->memIdentityObject->getKeys());	
		$notInCache = array();
		foreach ($this->memIdentityObject->getSuffix() as $suffix) {
			$key = $this->memIdentityObject->getPrefix() . $suffix;
			if (isset($memRes[$key])) {
				if (!empty($memRes[$key])) {
					$twitter[$suffix] = $memRes[$key];			
				}
				else {
					$twitter[$suffix] = array();
				}
			}
			else {
				$notInCache[] = $suffix;	
			}
		}
		return array($notInCache, $twitter);
	}

}
