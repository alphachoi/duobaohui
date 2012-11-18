<?php
namespace Snake\Package\Topic;
use \Snake\Libs\Base\Face;

class TopicInfoObject extends \Snake\Package\Base\DomainObject{

    public function __construct($topicInfo = array()) {
		$this->row = $topicInfo;
	}
}
