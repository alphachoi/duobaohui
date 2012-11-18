<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler;

class KeyWords{
        
    private $keywords = array();

    public function getKeywords(IdentityObject $identityObject, $obj = FALSE) {        
		$domainObjectAssembler = new DomainObjectAssembler(KeywordsPersistenceFactory::getFactory('\Snake\Package\Goods\KeywordsPersistenceFactory'));
        $keywordsCollection = $domainObjectAssembler->mysqlFind($identityObject);
        $keywords = array();    
        while ($keywordsCollection->valid()) {
            $keywordsObj = $keywordsCollection->next();
			if ($obj) {
				$keywords[] = $keywordsObj;	
			}
			else {
				$keywords[] = $keywordsObj->getRow();
			}
        }
        $this->keywords = $keywords;
        return $this->keywords;
    }

} 
