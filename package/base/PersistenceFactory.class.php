<?php
namespace Snake\Package\Base;

abstract class PersistenceFactory{

    abstract function getMapper();
    //abstract function getDomainObjectFactory();
    abstract function getCollection(array $array);
    abstract function getObject(array $array);
    abstract function getQueryFactory();

    static function getFactory($target_class) {
		return new $target_class;
        /*switch ($target_class) {
            case "Twitter";
                return new TwitterPersistenceFactory();
                break;
			case "Goods";
                return new GoodsPersistenceFactory();
                break;
        }*/
    }
}

?>
