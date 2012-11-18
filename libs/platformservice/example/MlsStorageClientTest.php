<?php

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\AnyVal;
use \Snake\Libs\Thrift\Packages\BoolOperation;
use \Snake\Libs\Thrift\Packages\FilterOpcode;

require_once('../../../config/testing/platformservice.config.php');
require_once('../MlsStorageService.class.php');

function TestUniqRowGetUniq() {
    $start = microtime(true);
    $i = rand(0, 50);
    $key_val = new AnyVal();
    $key_val->SetI32($i);
    $query_result = MlsStorageService::UniqRowGetUniq('t_twitter', 'twitter_id', $key_val, '*', 'twitter_id');
    if (FALSE === $query_result) {
        echo 'false', PHP_EOL;
    }
    var_dump($query_result);
    echo 'UniqRowGetUniq:' . (microtime(true) - $start) . "\n";
    return;
}

function TestUniqRowGetMulti() {
    $start = microtime(true);
    $i = rand(0, 50);
    $key_vals = array();
    for ($j = 0; $j < 20; $j++) {
        $key_vals[$j] = new AnyVal();
        $key_vals[$j]->SetI32($i + $j);
    }
    $val = new Anyval();
    $val->SetByte(0);
    $filter[0] = new BoolOperation();
    $filter[0]->column_name_ = 'twitter_show_type';
    $filter[0]->opcode_ = FilterOpcode::FILTER_OP_GT;
    $filter[0]->val_ = $val;
    $query_result = MlsStorageService::UniqRowGetMulti('t_twitter', 'twitter_id', $key_vals, $filter, '*', 'twitter_id');
    if (FALSE === $query_result) {
        echo 'false', PHP_EOL;
    }
    var_dump($query_result);
    echo 'UniqRowGetMulti:' . (microtime(true) - $start) . "\n";
    return;
}

function TestMultiRowGetUniq() {
    $start = microtime(true);
    $i = rand(1, 1000000);
    $key_val = new AnyVal();
    $key_val->SetI32($i);
    $query_result = MlsStorageService::MultiRowGetUniq('t_twitter', 'twitter_source_tid', $key_val, array(), '', 0, 2, 'twitter_id', 0, '*', 'twitter_id');
    var_dump($query_result);
    if (FALSE === $query_result) {
        echo 'false', PHP_EOL;
    }
    echo 'MultiRowGetUniq:' . (microtime(true) - $start) . "\n";
    return;
}

function TestGetQueryData() {
    $start = microtime(true);
    $l = rand(1, 100);
    $query_result = MlsStorageService::GetQueryData('t_twitter', array(), '*', 'select * from t_twitter where twitter_id=1', 'twitter_id');
    var_dump($query_result);
    if (FALSE === $query_result) {
        echo 'false', PHP_EOL;
    }
    echo 'GetQueryData:' . (microtime(true) - $start) . "\n";
    return;
}

function TestQueryRead() {
    $start = microtime(true);
    $l = rand(1, 100);
    $query_result = MlsStorageService::QueryRead('select twitter_id,twitter_author_uid from t_twitter where twitter_id=1', 'twitter_id');
    var_dump($query_result);
    if (FALSE === $query_result) {
        echo 'false', PHP_EOL;
    }
    echo 'QueryRead:' . (microtime(true) - $start) . "\n";
    return;
}

$s = rand(1, 4);
if ($s == 1) {
    #TestUniqRowGetUniq();
    TestUniqRowGetMulti();
    #TestQueryRead();
}
elseif ($s == 2) {
    #TestQueryRead();
    TestUniqRowGetMulti();
}
elseif ($s == 3) {
    #TestQueryRead();
    TestUniqRowGetMulti();
    #TestGetQueryData();
}
else {
    #TestQueryRead();
    TestUniqRowGetMulti();
    #TestMultiRowGetUniq();
}

?>
