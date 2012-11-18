<?php
namespace Snake\Package\Goods;

Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler;

class AttrKeyWords{
        
    private $keywords = array();

    public function getKeywords(IdentityObject $identityObject) {        $domainObjectAssembler = new DomainObjectAssembler(KeywordsPersistenceFactory::getFactory('\Snake\Package\Goods\KeywordsPersistenceFactory'));
        $keywordsCollection = $domainObjectAssembler->mysqlFind($identityObject);
        $keywords = array();    
        while ($keywordsCollection->valid()) {
            $keywordsObj = $keywordsCollection->next();
            $keywords[] = $keywordsObj->getRow();
        }
        $this->keywords = $keywords;
        return $this->keywords;
    }

} 
