<?php
namespace Snake\Package\Url;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class Url{

	private $fields = array();
	private $url = array();

    public function __construct($fields = array(), $url = array()) {
		$this->fields = $fields;
		$this->url = $url;
	}
	

	/**
	 *  根据urlIds获取url信息
	 *  @param array $urlIds 需要获取评论信息的tid数组
	 *  @param int|string $count 需要获取的评论数量,默认为3个
	 *  @return array 以tid为键值的数组
	 */
	public function getUrlsByUrlIds($urlIds, $obj = FALSE) {
		if (empty($urlIds)) {
			return array();
		}
		$domainObjectAssembler = new DomainObjectAssembler(UrlPersistenceFactory::getFactory('\Snake\Package\Url\UrlPersistenceFactory'));
		
		$identityObject = new IdentityObject();
		$identityObject->field('url_id')->in($urlIds);
		$identityObject->col($this->fields);
		$urlCollection = $domainObjectAssembler->mysqlFind($identityObject);

		$urls = array();	
		while ($urlCollection->valid()) {
			$urlObj = $urlCollection->next();	
			if ($obj) {
				$urls[] = $urlObj;
			}
			else {
				$urls[] = $urlObj->getRow();
			}
		}
		$this->url = $urls;
		return $this->url;
	}

}
