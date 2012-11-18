<?php

use \Snake\Libs\Base\MultiClient;
use \Snake\Libs\Thrift\Packages\ZooRequest;

require_once('../../../config/testing/platformservice.config.php');
require_once('../MultiClient.class.php');

$requests = array(
    new ZooRequest(array('method'=>'get',
                        'url'=>'http://zoo.wwwtest.meilishuo.com/twitters/likes_state?user_id=1226148&twitter_id=7150106',
                        'params'=>null,
                        'headers'=>array('MEILISHUO'=>'UID:1226148'))),
    new ZooRequest(array('method'=>'get',
                        'url'=>'http://zoo.wwwtest.meilishuo.com/twitters/likes_state?user_id=1226148&twitter_id=7150106',
                        'params'=>null,
                        'headers'=>array('MEILISHUO'=>'UID:1226148'))),
);

$client = MultiClient::getClient(1226148);
$responses = $client->batchHttpReq($requests);
var_dump($responses);
