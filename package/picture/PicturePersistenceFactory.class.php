<?php
namespace Snake\Package\Picture;

class PicturePersistenceFactory extends \Snake\Package\Base\PersistenceFactory{
    function getMapper() {
        return new PictureMapper();
    }

	function getQueryFactory() {
		return new PictureQueryFactory();
	}
	function getObject(array $picture) {
		return new PictureObject($picture);	
	}
    /*function getDomainObjectFactory() {
        return new TwitterObjectFactory();
    }*/

    function getCollection(array $array) {
        return new PictureCollection($array, $this->getObject(array()));
    }
}
?>
