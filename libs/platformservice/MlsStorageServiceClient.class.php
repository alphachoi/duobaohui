<?php

namespace Snake\Libs\PlatformService;

use \Snake\Libs\Thrift;
use \Snake\Libs\Thrift\Transport\TFramedTransport;
use \Snake\Libs\Thrift\Protocol\TBinaryProtocol;
use \Snake\Libs\Thrift\Protocol\TBinaryProtocolAccelerated;
use \Snake\Libs\Thrift\Packages\MlsStorageClient;
use \Snake\Libs\PlatformService\ServiceRandomGenerator;
use \Snake\Libs\PlatformService\TMlsSocket;
use Exception;

require_once($GLOBALS['THRIFT_ROOT'] . '/Thrift.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/packages/mlsstorage/MlsStorage.php');
require_once(PLATFORM_SERVICE_PATH . '/ServiceRandomGenerator.class.php');
require_once(PLATFORM_SERVICE_PATH . '/TMlsSocket.class.php');

class MlsStorage {
    /* 服务进程集合 */
    var $nodes_;
    /* 服务进程数 */
    var $nodesCount_ = 0;
    /* MlsStorage对象 */
    static $instance_ = NULL;

    /* 
     * description: 获取MlsStorage服务对象
     * @param array configNodes: 配置的服务节点的ip与port
     * @return MlsStorage: MlsStorage对象
     */
    static public function GetInstance($configNodes) {
        if (is_null(self::$instance_)) {
            self::$instance_ = new MlsStorage($configNodes);
        }
        return self::$instance_;
    }
    
	/**
	 * @change by Chen Hailong opt field: replace "COUNT(*) as num" to "num" 
	 */
    static public function FormatColumns($columns) {
        $patterArr = array (
            '/\/\*(.*?)\*\//is', 
            '/\sas\s|\s+|count\(\*\)/is');
        $columns = preg_replace($patterArr, '', $columns);
        return $columns;
    }

    static public function FormatSql($sql) {
        $patterArr = array (
            '/\/\*(.*?)\*\//is',
            '/\s+/is' );
        $sql = preg_replace($patterArr, ' ', $sql);
        return $sql;
    }

    static public function RemoveSpace($columns) {
        return str_replace(' ','',$columns);
    }
    
    static public function CheckEqual($former, $now) {
      $diff = array_diff($former, $now);
      return ($diff == array());
    }
    public function __construct($nodes) {
        $this->nodes_ = $nodes;
        $this->nodesCount_ = count($this->nodes_);
    }

    /* 
     * description: 根据ip与port创建一个连接
     * @param string ip: ip
     * @param string port: port
     * @return MlsStorageClient: 连接
     */
    protected function GetConnByAddr($host, $port) {
        $socket = new TMlsSocket($host, $port);
        if (isset($GLOBALS['STORAGESENDTIMEOUT']) && $GLOBALS['STORAGESENDTIMEOUT'] > 400) {
            $socket->setSendTimeout($GLOBALS['STORAGESENDTIMEOUT']);
        } else {
            $socket->setSendTimeout(400);
        }
        if (isset($GLOBALS['STORAGERECVTIMEOUT']) && $GLOBALS['STORAGERECVTIMEOUT'] > 1000) {
            $socket->setRecvTimeout($GLOBALS['STORAGERECVTIMEOUT']);
        } else {
            $socket->setRecvTimeout(3000);
        }

        $transport = new TFramedTransport($socket, 10240, 4096);
        //$protocol = new TBinaryProtocol($transport);
        $protocol = new TBinaryProtocolAccelerated($transport);
        $client = new MlsStorageClient($protocol);
        try {
            $socket->open();
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "fail to connect ip={$host} port={$port} exception={$e->getMessage()}");
            return NULL;
        }
        if (!$socket->isOpen()) {
            MlscacheLog('ERROR', "fail to connect ip={$host} port={$port}");
            return NULL;
        }
        $client->id_ = $socket->getID();
        return $client;
    }

    public function GetClient() {
        $randomGenerator = new ServiceRandomGenerator($this->nodesCount_ - 1);
        while(1) {
            $nodeIdx = $randomGenerator->GetRandom();
            if (-1 == $nodeIdx) {
                return NULL;
            }
            $client = $this->GetConnByAddr($this->nodes_[$nodeIdx]['host'], $this->nodes_[$nodeIdx]['port']);
            if ($client != NULL) {
                return $client;
            }
        }
        return NULL;
    }

    public function UniqRowGetUniqWithChoice($type, $keyName, $keyVal, $columns, $retStr, $hashKey) {
        if (!isset($keyVal) || empty($keyVal)) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->UniqRowGetUniq($type, $keyName, $keyVal, $columnsFormated);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "UniqRowGetUniq fail, type={$type} keyname={$keyName} keyval={$keyVal->GetValue()} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetUniq fail, type={$type} keyname={$keyName} keyval={$keyVal->GetValue()} exception={$e->getMessage()}");
            return FALSE;
        }

        $result = array();
        if ('*' == $columnsFormated) {
            $arrayColumns = explode(',', $resp->selected_columns_);
        }
        else {
            $arrayColumns = explode(',', $columnsFormated);
        }
        $columnCount = count($arrayColumns);
        if ($columnCount != count($resp->item_->value_->column_values_)) {
            return FALSE;
        }
        $i = 0;
        foreach($resp->item_->value_->column_values_ as $column) {
            if ($retStr) {
                $result[$arrayColumns[$i]] = $column->GetStringValue();
            }
            else {
                $result[$arrayColumns[$i]] = $column->GetValue();
            }
            ++$i;
        }

        if (isset($hashKey) && !empty($hashKey)) {
            $resultHashKey = array();
            /* 防止传入的hashKey不正确&&不期望hashKey对应的值为空 */
            if (is_null($result[$hashKey])) {
                return FALSE;
            }
            $resultHashKey[$result[$hashKey]]= $result;
            $result = $resultHashKey;
        }
        else {
            $result2D = array();
            $result2D[0] = $result;
            $result = $result2D;
        }
        return $result;
    }

    public function UniqRowGetUniqWithChoice2($client, $type, $keyName, $keyVal, $columns, $retStr, $hashKey) {
        if (!isset($keyVal) || empty($keyVal) || $client == NULL) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $resp = $client->UniqRowGetUniq($type, $keyName, $keyVal, $columnsFormated);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "UniqRowGetUniq fail, type={$type} keyname={$keyName} keyval={$keyVal->GetValue()} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetUniq fail, type={$type} keyname={$keyName} keyval={$keyVal->GetValue()} exception={$e->getMessage()}");
            return FALSE;
        }

        $result = array();
        if ('*' == $columnsFormated) {
            $arrayColumns = explode(',', $resp->selected_columns_);
        }
        else {
            $arrayColumns = explode(',', $columnsFormated);
        }
        $columnCount = count($arrayColumns);
        if ($columnCount != count($resp->item_->value_->column_values_)) {
            return FALSE;
        }
        $i = 0;
        foreach($resp->item_->value_->column_values_ as $column) {
            if ($retStr) {
                $result[$arrayColumns[$i]] = $column->GetStringValue();
            }
            else {
                $result[$arrayColumns[$i]] = $column->GetValue();
            }
            ++$i;
        }

        if (isset($hashKey) && !empty($hashKey)) {
            $resultHashKey = array();
            /* 防止传入的hashKey不正确&&不期望hashKey对应的值为空 */
            if (is_null($result[$hashKey])) {
                return FALSE;
            }
            $resultHashKey[$result[$hashKey]]= $result;
            $result = $resultHashKey;
        }
        else {
            $result2D = array();
            $result2D[0] = $result;
            $result = $result2D;
        }
        return $result;
    }

    public function UniqRowGetMultiWithChoice($type, $keyName, $keyVals, $filter, $columns, $retStr, $hashKey) {
        if (!is_array($keyVals) || empty($keyVals)) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $result = $this->UniqRowGetMultiRows($type, $keyName, $keyVals, $filter, $columnsFormated, "", 0, 0, -1, false, false, $retStr, $hashKey);
            return $result;
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMulti fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
    }

    public function UniqRowGetMultiKeyWithChoice(
            $type,
            $columns,
            $keyName,
            $keyVals,
            $filter,
            $retStr,
            $hashKey) {
        if (!is_array($keyVals) || empty($keyVals)) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $result = $this->UniqRowGetMultiKey($type,
                $columnsFormated,
                $keyName,
                $keyVals,
                $filter,
                null,
                false,
                false,
                $retStr,
                $hashKey);
            return $result;
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMultiKey fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
    }

    public function UniqRowGetMultiKeyExWithChoice(
            $type,
            $columns,
            $keyName,
            $keyVals,
            $filter,
            $query_conditions,
            $retStr,
            $hashKey) {
        if (!is_array($keyVals) || empty($keyVals)) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $result = $this->UniqRowGetMultiKey($type,
                $columnsFormated,
                $keyName,
                $keyVals,
                $filter,
                $query_conditions,
                true,
                false,
                $retStr,
                $hashKey);
            return $result;
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMultiKeyEx fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
    }

    private function UniqRowGetMultiKey(
            $type,
            $columnsFormated,
            $keyName,
            $keyVals,
            $filter,
            $query_conditions,
            $withEx,
            $withSharding,
            $retStr,
            $hashKey) {
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            if ($withEx) {
                $resp = $client->UniqRowGetMultiKeyEx($type,
                    $columnsFormated,
                    $keyName,
                    $keyVals,
                    $filter,
                    $query_conditions);
            } elseif ($withSharding) {
                MlscacheLog('ERROR', 'UniqRowGetMultiKey failed, not support sharding yet');
                return FALSE;
            } else {
                $resp = $client->UniqRowGetMultiKey($type,
                    $columnsFormated,
                    $keyName,
                    $keyVals,
                    $filter);
            }
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "UniqRowGetMultiKey fail, type={$type} keyname={$keyName}  columnNames={$columnsFormated} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMultiKey fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
        $result = array();
        if ('*' == $columnsFormated) {
            $arrayColumns = explode(',', $resp->selected_columns_);
        }
        else {
            $arrayColumns = explode(',', $columnsFormated);
        }
        $columnCount = count($arrayColumns);
        $j = 0;
        foreach($resp->items_ as $keyValue) {
            if($columnCount != count($keyValue->value_->column_values_)) {
                return FALSE;
            }
            $result[$j] = array();
            $i = 0;
            foreach($keyValue->value_->column_values_ as $column) {
                if ($retStr) {
                    $result[$j][$arrayColumns[$i]] = $column->GetStringValue();
                }
                else {
                    $result[$j][$arrayColumns[$i]] = $column->GetValue();
                }
                ++$i;
            }
            ++$j;
        }

        if (isset($hashKey) && !empty($hashKey)) {
            $resultHashKey = array();
            foreach($result as $row) {
                /* 防止传入的hashKey不正确&&不期望hashKey对应的值为空 */
                if (is_null($row[$hashKey])) {
                    return FALSE;
                }
                $resultHashKey[$row[$hashKey]]= $row;
            }
            $result = $resultHashKey;
        }
        return $result;
    }

    public function UniqRowGetMultiShardingWithChoice($type, $keyName, $keyVals, $filter, $columns, $order_by, $order_dir, $start, $limit, $retStr, $hashKey) {
        if (!is_array($keyVals) || empty($keyVals)) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $result = $this->UniqRowGetMultiRows($type, $keyName, $keyVals, $filter, $columnsFormated, $order_by, $order_dir, $start, $limit, false, true, $retStr, $hashKey);
            return $result;
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMultiSharding fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
    }

    public function UniqRowGetMultiExWithChoice($type, $keyName, $keyVals, $filter, $columns, $order_by, $order_dir, $start, $limit, $retStr, $hashKey) {
        if (!is_array($keyVals) || empty($keyVals)) {
            return array();
        }
        $columnsFormated = $this->FormatColumns($columns);
        try {
            $result = $this->UniqRowGetMultiRows($type, $keyName, $keyVals, $filter, $columnsFormated, $order_by, $order_dir, $start, $limit, true, false, $retStr, $hashKey);
            return $result;
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMultiEx fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
    }

    static private function GetResultMultiRows(&$result, $rows, $columnsFormated, $retStr, $hashKey) {
        $arrayColumns = explode(',', $columnsFormated);

        $j = 0;
        $columnCount = count($arrayColumns);
        foreach($rows as $rowSingle) {
            if($columnCount != count($rowSingle->column_values_)) {
                return FALSE;
            }
            $result[$j] = array();
            $i = 0;
            foreach($rowSingle->column_values_ as $column) {
                if ($retStr) {
                    $result[$j][$arrayColumns[$i]] = $column->GetStringValue();
                }
                else {
                    $result[$j][$arrayColumns[$i]] = $column->GetValue();
                }
                ++$i;
            }
            ++$j;
        }
        if (isset($hashKey) && !empty($hashKey)) {
            $resultHashKey = array();
            foreach($result as $row) {
                /* 防止传入的hashKey不正确&&不期望hashKey对应的值为空 */
                if (is_null($row[$hashKey])) {
                    return FALSE;
                }
                $resultHashKey[$row[$hashKey]]= $row;
            }
            $result = $resultHashKey;
        }
    }
   
    static private function GetResultMultiRowGetMulti(&$result, $items, $columnsFormated, $retStr, $hashKey) {
        $arrayColumns = explode(',', $columnsFormated);
        $columnCount = count($arrayColumns);
        $j = 0;
        foreach($items as $multiRowKV) {
            foreach($multiRowKV->values_ as $rowSingle) {
                if ($columnCount != count($rowSingle->column_values_)) {
                    return FALSE;
                }
                $result[$j] = array();
                $i = 0;
                foreach($rowSingle->column_values_ as $column) {
                    if ($retStr) {
                        $result[$j][$arrayColumns[$i]] = $column->GetStringValue();
                    } else {
                        $result[$j][$arrayColumns[$i]] = $column->GetValue();
                    }
                    ++$i;
                }
                ++$j;
            }
        }
        if (isset($hashKey) && !empty($hashKey)) {
            $resultHashKey = array();
            foreach($result as $row) {
                /* 防止传入的hashKey不正确&&不期望hashKey对应的值为空 */
                if (is_null($row[$hashKey])) {
                    return FALSE;
                }
                $resultHashKey[$row[$hashKey]]= $row;
            }
            $result = $resultHashKey;
        }
    }
    
    private function UniqRowGetMultiRows($type, $keyName, $keyVals, $filter, $columnsFormated, $order_by, $order_dir, $start, $limit, $withEx, $withSharding, $retStr, $hashKey) {
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            if ($withEx) {
                $resp = $client->UniqRowGetMultiEx($type, $keyName, $keyVals, $filter, $columnsFormated, $order_by, $order_dir, $start, $limit);
            } elseif ($withSharding) {
                $resp = $client->UniqRowGetMultiSharding($type, $keyName, $keyVals, $filter, $columnsFormated, $order_by, $order_dir, $start, $limit);
            } else {
                $resp = $client->UniqRowGetMulti($type, $keyName, $keyVals, $filter, $columnsFormated); 
            }
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "UniqRowGetMultiRows fail, type={$type} keyname={$keyName}  columnNames={$columnsFormated} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "UniqRowGetMultiRows fail, type={$type} keyname={$keyName} exception={$e->getMessage()}");
            return FALSE;
        }
        $result = array();
        if ('*' == $columnsFormated) {
            $arrayColumns = explode(',', $resp->selected_columns_);
        }
        else {
            $arrayColumns = explode(',', $columnsFormated);
        }
        $columnCount = count($arrayColumns);
        $j = 0;
        foreach($resp->items_ as $keyValue) {
            if($columnCount != count($keyValue->value_->column_values_)) {
                return FALSE;
            }
            $result[$j] = array();
            $i = 0;
            foreach($keyValue->value_->column_values_ as $column) {
                if ($retStr) {
                    $result[$j][$arrayColumns[$i]] = $column->GetStringValue();
                }
                else {
                    $result[$j][$arrayColumns[$i]] = $column->GetValue();
                }
                ++$i;
            }
            ++$j;
        }

        if (isset($hashKey) && !empty($hashKey)) {
            $resultHashKey = array();
            foreach($result as $row) {
                /* 防止传入的hashKey不正确&&不期望hashKey对应的值为空 */
                if (is_null($row[$hashKey])) {
                    return FALSE;
                }
                $resultHashKey[$row[$hashKey]]= $row;
            }
            $result = $resultHashKey;
        }

        return $result;
    }

    public function MultiRowGetUniqWithChoice($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnNames, $withSharding, $retStr, $hashKey) {
        $columnsFormated = $this->FormatColumns($columnNames);
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            if (!$withSharding) {
                $resp = $client->MultiRowGetUniq($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnsFormated);
            }
            else {
                $resp = $client->MultiRowGetUniqSharding($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnsFormated);
            }
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "MultiRowGetUniq fail, type={$type} keyname={$keyName} keyVal={$keyVal->GetValue()} columnNames={$columnsFormated} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "MultiRowGetUniq fail, type={$type} keyname={$keyName} keyVal={$keyVal->GetValue()} exception={$e->getMessage()}");
            return FALSE;
        }
        if ('*' == $columnsFormated) {
            $columnsFormated = $resp->selected_columns_;
        }
        $result = array();
        self::GetResultMultiRows($result, $resp->item_->values_, $columnsFormated, $retStr, $hashKey); 
        return $result;
    }

    public function MultiRowGetUniqKeyWithChoice(
            $type,
            $columnNames,
            $keyName,
            $keyVal,
            $force_index,
            $filter,
            $query_conditions,
            $withSharding,
            $retStr,
            $hashKey) {
        $columnsFormated = $this->FormatColumns($columnNames);
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            if (!$withSharding) {
                $resp = $client->MultiRowGetUniqKey($type,
                        $columnsFormated,
                        $keyName,
                        $keyVal,
                        $force_index,
                        $filter,
                        $query_conditions);
            }
            else {
                MlscacheLog('ERROR', "err=not support sharding yet");
                return FALSE;
            }
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "MultiRowGetUniqKey fail,type={$type} keyname={$keyName} keyVal={$keyVal->GetValue()} columnNames={$columnsFormated} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', 
                    "MultiRowGetUniqKey fail, type={$type} keyname={$keyName} keyVal={$keyVal->GetValue()} exception={$e->getMessage()}");
            return FALSE;
        }
        if ('*' == $columnsFormated) {
            $columnsFormated = $resp->selected_columns_;
        }
        $result = array();
        self::GetResultMultiRows($result,
                $resp->item_->values_,
                $columnsFormated,
                $retStr,
                $hashKey);
        return $result;
    }

    public function GetQueryDataWithChoice($type, $shard_vals, $column_names, $sql, $retStr, $hashKey) {
        $columnsFormated = $this->FormatColumns($column_names);
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->GetQueryData($type, $shard_vals, $columnsFormated, $sql);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "GetQueryData fail, sql={$sql} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "GetQueryData fail, sql={$sql} exception={$e->getMessage()}");
            return FALSE;
        }
        if ('*' == $columnsFormated) {
            $columnsFormated = $resp->selected_columns_;
        }
        $result = array();
        self::GetResultMultiRows($result, $resp->rows_, $columnsFormated, $retStr, $hashKey); 
        return $result;
    }

    public function GetQueryDataShardingWithChoice($type, $sql, $retStr, $hashKey) {
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->GetQueryDataSharding($type, $sql);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "GetQueryDataSharding fail, sql={$sql} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "GetQueryDataSharding fail, sql={$sql} exception={$e->getMessage()}");
            return FALSE;
        }
        /* 暂时不支持select *,如果支持，需要服务器解析，并转换成具体的列 */
        $columnsFormated = $this->FormatColumns($resp->selected_columns_);
        $result = array();
        self::GetResultMultiRows($result, $resp->rows_, $columnsFormated, $retStr, $hashKey); 
        return $result;
    }

    public function MultiRowGetMultiKeyWithChoice($type, $column_names, $key_name, $key_vals, $force_index, $filter, $with_sharding, $retStr, $hashKey) {
        $columnsFormated = $this->FormatColumns($column_names);
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->MultiRowGetMultiKey($type, $columnsFormated, $key_name, $key_vals, $force_index, $filter);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "MultiRowGetMultiKey fail, type={$type} keyname={$key_name} columnNames={$columnsFormated} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "MultiRowGetMultiKey fail, type={$type} keyname={$key_name}  exception={$e->getMessage()}");
            return FALSE;
        }
        if ('*' == $columnsFormated) {
            $columnsFormated = $resp->selected_columns_;
        }
        $result = array();
        self::GetResultMultiRowGetMulti($result, $resp->items_, $columnsFormated, $retStr, $hashKey); 
        return $result;
    }

    public function QueryReadWithChoice($sql, $retStr, $hashKey) {
        $sqlFormated = $this->FormatSql($sql);
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->QueryRead($sqlFormated);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "QueryRead fail, sql={$sql} ret={$resp->result_}");
                return FALSE;
            } elseif (1 == $resp->result_) {
                return array();
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "QueryRead fail, sql={$sql} exception={$e->getMessage()}");
            return FALSE;
        }
        $columnsFormated = $this->RemoveSpace($resp->selected_columns_);
        $result = array();
        self::GetResultMultiRows($result, $resp->rows_, $columnsFormated, $retStr, $hashKey); 
        return $result;
    }

    public function PreStmtWrite($sql, $bind_params) {
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->PreStmtWrite($sql, $bind_params);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "PreStmtWrite fail, sql={$sql} ret={$resp->result_}");
                return FALSE;
            }
        }
        catch(Exception $e) {
            MlscacheLog('ERROR', "PreStmtWrite fail, sql={$sql} exception={$e->getMessage()}");
            return FALSE;
        }
        return $resp;
    }

    public function Insert($type, $column_vals) {
        try {
            $client = $this->GetClient();
            if (NULL == $client) {
                return FALSE;
            }
            $resp = $client->Insert($type, $column_vals);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "Insert fail, type={$type} ret={$resp->result_}");
                return FALSE;
            }
        } catch(Exception $e) {
            MlscacheLog('ERROR', "Insert fail, type={$type} exception={$e->getMessage()}");
            return FALSE;
        }
        return $resp;
    }

    public function Update($type, $column_vals, $key_name, $key_vals) {
        try {
            $client = $this->GetClient();
            if (NULL ==$client) {
                return FALSE;
            }
            $resp = $client->Update($type,$column_vals,$key_name,$key_vals);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "Update fail, type={$type} ret={$resp->result_}");
                return FALSE;
            }
        } catch(Exception $e) {
            MlscacheLog('ERROR', "Update fail, type={$type} exception={$e->getMessage()}");
            return FALSE;
        }
        return $resp;
    }

    public function Delete($type, $key_name, $key_vals) {
        try {
            $client = $this->GetClient();
            if (NULL ==$client) {
                return FALSE;
            }
            $resp = $client->Delete($type,$key_name,$key_vals);
            if ($resp->result_ < 0) {
                MlscacheLog('ERROR', "Delete fail, type={$type} ret={$resp->result_}");
                return FALSE;
            }
        } catch(Exception $e) {
            MlscacheLog('ERROR', "Delete fail, type={$type} exception={$e->getMessage()}");
            return FALSE;
        }
        return $resp;
    }
}

?>
