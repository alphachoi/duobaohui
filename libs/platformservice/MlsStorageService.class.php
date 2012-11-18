<?php

namespace Snake\Libs\PlatformService;

require_once(PLATFORM_SERVICE_PATH . '/MlsStorageServiceClient.class.php');

class MlsStorageService {

    private static $_mlsStorageService;

    private static $_logHandle = NULL;

    /*
     * @return _mlsStorageService
     */
    static function GetMlsStorage() {
        if (is_null(self::$_mlsStorageService)) {
            self::$_mlsStorageService = MlsStorage::GetInstance($GLOBALS['MLSSTORAGE']);
        }
        return self::$_mlsStorageService;
    }

    static function StorageLog($interfaceType, $result/*array,FALSE*/, $timeCost) {
        if (NULL == $_logHandle) {
            $_logHandle = new \Snake\Libs\Base\SnakeLog('storage_perform', 'normal');
        }
        $choice = rand(0, 50);
        if (5 == $choice) {
            if (false === $result) {
                $logContent = "[$interfaceType]\t[0]\t[$timeCost]";
            } else {
                $logContent = "[$interfaceType]\t[1]\t[$timeCost]";
            }
            $_logHandle->w_log($logContent);
        }
    }

    /* 
     * description: UniqRowGetUniq接口
     * @param string type: 表名
     * @param string keyName: 查找的列名
     * @param AnyVal keyVal: 查找的列值
     * @param string columns: 返回列, 列名之间以逗号分隔
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: 返回数组中的元素即返回列的列值 
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...},} 
     */
    public static function UniqRowGetUniq($type, $keyName, $keyVal, $columns, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->UniqRowGetUniqWithChoice($type, $keyName, $keyVal, $columns, true, $hashKey);
        self::StorageLog('UniqRowGetUniq', $ret, microtime(true)-$start);
        return $ret;
    }

    /* 
     * description: UniqRowGetMulti接口
     * @param string type: 表名
     * @param string keyName: 查找的列名
     * @param AnyVal keyVals: 查找的列值,多个
     * @param array filter: 过滤条件
     * @param string columns: 返回列, 列名之间以逗号分隔
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     */
    public static function UniqRowGetMulti($type, $keyName, $keyVals, $filter, $columns, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->UniqRowGetMultiWithChoice($type, $keyName, $keyVals, $filter, $columns, true, $hashKey);
        self::StorageLog('UniqRowGetMulti', $ret, microtime(true)-$start);
        return $ret;
    }
    /*
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     */
    public static function UniqRowGetMultiSharding($type, $keyName, $keyVals, $filter, $columns, $order_by, $order_dir, $start, $limit, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->UniqRowGetMultiShardingWithChoice($type, $keyName, $keyVals, $filter, $columns, $order_by, $order_dir, $start, $limit, true, $hashKey);
        self::StorageLog('UniqRowGetMultiSharding', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     */
    public static function UniqRowGetMultiEx($type, $keyName, $keyVals, $filter, $columns, $order_by, $order_dir, $start, $limit, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->UniqRowGetMultiExWithChoice($type, $keyName, $keyVals, $filter, $columns, $order_by, $order_dir, $start, $limit, true, $hashKey);
        self::StorageLog('UniqRowGetMultiEx', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     */
    public static function MultiRowGetUniq($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnNames, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->MultiRowGetUniqWithChoice($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnNames, false, true, $hashKey);
        self::StorageLog('MultiRowGetUniq', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     */
    public static function MultiRowGetUniqSharding($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnNames, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->MultiRowGetUniqWithChoice($type, $keyName, $keyVal, $filter, $force_index, $start, $limit, $orderBy, $orderDir, $columnNames, true, true, $hashKey);
        self::StorageLog('MultiRowGetUniqSharding', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     */
    public static function GetQueryData($type, $shard_vals, $column_names, $sql, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->GetQueryDataWithChoice($type, $shard_vals, $column_names, $sql, true, $hashKey);
        self::StorageLog('GetQueryData', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     */
    public static function GetQueryDataSharding($type, $sql, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->GetQueryDataShardingWithChoice($type, $sql, true, $hashKey);
        self::StorageLog('GetQueryDataSharding', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * description: UniqRowGetMultiKeyEx接口
     * @param string type:表名,例如:t_twitter
     * @param string columns: 选择的列, 列名之间以逗号分隔
     * @param string keyName:查找的列名,必须具有唯一性约束,例如:twitter_id
     * @param array<AnyVal> keyVals: 查找的列值,多个AnyVal Objects组成的数组
     *  note:keyName和keyVals对应sql语句中的: keyName IN (v1,...vn), 其中keyName必须具有唯一性约束
     * @param string filter: 其它过滤条件,与前面的条件是AND的关系,
     *  note:目前支持的比较运算符有: =,IN,>,>=,<,<=,!=,<>
     *       例如:twitter_show_type=4 AND twitter_author_uid IN (1023,...,234)
     * @param QueryConditions queryConditions:order by 和 limit限制条件
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     * example: 
     *   $keyVals = array();
     *   for ($i=0; $i < 10; $i++) {
     *       $val = new AnyVal()
     *       $val->SetI32($i);
     *       $keyVals[] = $val;
     *   }
     *   $queryConditions = new QueryConditions();
     *   $queryConditions->order_by_ = ''; // 不需要排序
     *   $queryConditions->order_dir_ = 0;
     *   $queryConditions->start_ = 0;
     *   $queryConditions->limit_ = -1;
     *   $result = MlsStorageService::UniqRowGetMultiKeyEx('t_twitter', 'twitter_author_uid,twitter_images_id,twitter_goods_id',
     *       'twitter_id', $keyVals, 'twitter_show_type>0 AND twitter_show_type<9', $queryConditions, 'twitter_id');
     */
    public static function UniqRowGetMultiKeyEx($type, $column_names, $key_name, $key_vals, $filter, $queryConditions, $hashKey  = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->UniqRowGetMultiKeyExWithChoice($type, $column_names, $key_name, $key_vals, $filter, $queryConditions, true, $hashKey);
        self::StorageLog('UniqRowGetMultiKeyEx', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * description: UniqRowGetMultiKey接口
     * @param string type:表名,例如:t_twitter
     * @param string columns: 选择的列, 列名之间以逗号分隔
     * @param string keyName:查找的列名,必须具有唯一性约束,例如:twitter_id
     * @param array<AnyVal> keyVals: 查找的列值,多个AnyVal Objects组成的数组
     *  note:keyName和keyVals对应sql语句中的: keyName IN (v1,...vn), 其中keyName必须具有唯一性约束
     * @param string filter: 其它过滤条件,与前面的条件是AND的关系,
     *  note:目前支持的比较运算符有: =,IN,>,>=,<,<=,!=,<>
     *       例如:twitter_show_type=4 AND picture_id>0
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     * example: 
     *   $keyVals = array();
     *   for ($i=0; $i < 10; $i++) {
     *       $val = new AnyVal()
     *       $val->SetI32($i);
     *       $keyVals[] = $val;
     *   }
     *   $result = MlsStorageService::UniqRowGetMultiKey('t_twitter', 'twitter_author_uid,twitter_images_id,twitter_goods_id',
     *       'twitter_id', $keyVals, 'twitter_show_type>0 AND twitter_show_type<9', 'twitter_id');
     */
    public static function UniqRowGetMultiKey($type, $column_names, $key_name, $key_vals, $filter, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->UniqRowGetMultiKeyWithChoice($type, $column_names, $key_name, $key_vals, $filter, true, $hashKey);
        self::StorageLog('UniqRowGetMultiKey', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * description: MultiRowGetUniqKey接口
     * @param string type:表名,例如:t_twitter
     * @param string columns: 选择的列, 列名之间以逗号分隔
     * @param string keyName:查找的列名,适合无唯一性约束的列,例如:twitter_author_uid
     * @param AnyVal keyVals: 查找的列值,AnyVal Object
     *  note:keyName和keyVal对应sql语句中的: keyName IN (v), 其中keyName最好没有唯一性约束
     * @param string filter: 其它过滤条件,与前面的条件是AND的关系,
     *  note:目前支持的比较运算符有: =,IN,>,>=,<,<=,!=,<>
     *       例如:twitter_show_type=4 AND picture_id>0
     * @param QueryConditions queryConditions:order by 和 limit限制条件
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     * example: 
     *   $keyVal = new AnyVal();
     *   $keyVal->SetI32(100);
     *   $queryConditions = new QueryConditions();
     *   $queryConditions->order_by_ = 'twitter_id';
     *   $queryConditions->order_dir_ = 0;
     *   $queryConditions->start_ = 0;
     *   $queryConditions->limit_ = -1;
     *   $result = MlsStorageService::MultiRowGetUniqKey('t_twitter', 'twitter_id,twitter_images_id,twitter_goods_id',
     *       'twitter_author_uid', $keyVal, '', 'twitter_show_type>0 AND twitter_show_type<9', $queryConditions, 'twitter_id');
     */
    public static function MultiRowGetUniqKey($type, $column_names, $key_name, $key_val, $force_index, $filter, $queryConditions, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->MultiRowGetUniqKeyWithChoice($type, $column_names, $key_name, $key_val, $force_index, $filter, $queryConditions, false, true, $hashKey);
        self::StorageLog('MultiRowGetUniqKey', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * description: MultiRowGetMultiKey接口
     * @param string type:表名,例如:t_twitter
     * @param string columns: 选择的列, 列名之间以逗号分隔
     * @param string keyName:查找的列名,最好不具有唯一性约束,例如:twitter_author_uid
     * @param array<AnyVal> keyVals: 查找的列值,多个AnyVal Objects组成的数组
     *  note:keyName和keyVals对应sql语句中的: keyName IN (v1,...vn), 其中keyName最好不具有唯一性约束
     * @param string filter: 其它过滤条件,与前面的条件是AND的关系,
     *  note:目前支持的比较运算符有: =,IN,>,>=,<,<=,!=,<>
     *       例如:twitter_show_type=4 AND picture_id>0
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     * example: 
     *   $keyVals = array();
     *   for ($i=0; $i < 10; $i++) {
     *       $val = new AnyVal()
     *       $val->SetI32($i);
     *       $keyVals[] = $val;
     *   }
     *   $result = MlsStorageService::MultiRowGetMultiKey('t_twitter', 'twitter_author_uid,twitter_images_id,twitter_goods_id',
     *       'twitter_source_tid', $keyVals, 'twitter_source_tid', 'twitter_show_type>0 AND twitter_show_type<9', 'twitter_id');
     */
    public static function MultiRowGetMultiKey($type, $column_names, $key_name, $key_vals, $force_index, $filter, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->MultiRowGetMultiKeyWithChoice($type, $column_names, $key_name, $key_vals, $force_index, $filter, false, true, $hashKey);
        self::StorageLog('MultiRowGetMultiKey', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * description: 通用sql查询接口
     * @param string sql: sql查询语句，不支持变量绑定
     * @param string hashKey: 返回结果以kv对形式表示，其中key是列名为hashKey的值，
     *                        注意，这个hashKey的指定必须是键值
     * @return array: get结果,形式如:
     * array{key_val1=>array{column_name1=>column_val1,column_name2=>column_val2,...}, 
     *       key_val2=>array{column_name1=>column_val1,column_name2=>column_val2,...},
     *       ...}
     * example: 
     *   $result = MlsStorageService::QueryRead('select twitter_author_uid,twitter_images_id,twitter_goods_id from t_twitter' . 
     *       ' where twitter_id<1000 AND twitter_show_type>0 AND twitter_show_type<9', 'twitter_id');
     */
    public static function QueryRead($sql, $hashKey = "") {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->QueryReadWithChoice($sql, true, $hashKey);
        self::StorageLog('QueryRead', $ret, microtime(true)-$start);
        return $ret;
    }
    
    /*
     * description: 此接口支持insert,update和delete语句
     * @param string sql: sql语句, 支持变量绑定,形式为:
     * insert into t_twitter (c1,...cn) values (?,...?)
     * update t_twitter set twitter_show_type=?,twitter_source_tid=0 where twitter_id in (?,?,?)
     * delete from t_twitter where twitter_id in (?,?,?)
     * @param array<AnyVal> bind_params: 变量绑定的值,这些值的顺序必须和列名在sql语句中出现的顺序一致
     * @return bool: 成功:QueryWriteResp对象, 失败:FALSE
     * example: 
     *   $bind_params = array();
     *   $param_twitter_images_id = new AnyVal()
     *   $param_twitter_images_id->SetI32(1000);
     *   $bind_params[] = $param_twitter_images_id;
     *   for ($i=1; $i < 4; $i++) {
     *       $val = new AnyVal()
     *       $val->SetI32($i);
     *       $bind_params[] = $val;
     *   }
     *   $result = MlsStorageService::PreStmtWrite('update t_twitter set twitter_show_type=1,twitter_images_id=? where twitter_id in (?,?,?)',
     *      $bind_params);
     */
    public static function PreStmtWrite($sql, $bind_params) {
        $mlsStorage = self::GetMlsStorage();
        $start = microtime(true);
        $ret = $mlsStorage->PreStmtWrite($sql, $bind_params);
        self::StorageLog('PreStmtWrite', $ret, microtime(true)-$start);
        return $ret;
    }

    /*
     * description: insert key-value 接口
     * @param string type: 表名
     * @param array<string=>AnyVal> column_vals: 列名=>列值对,除了自增id,必须包含所有无默认值的列
     * @return bool: 成功:QueryWriteResp, 失败:FALSE
     * example: 
     *   $column_vals = array();
     *   $column_vals['twitter_author_uid'] = new AnyVal();
     *   $column_vals['twitter_author_uid']->SetI32(10000);
     *   $column_vals['twitter_content'] = new AnyVal();
     *   $column_vals['twitter_content']->SetString('hello');
     *   ...
     *   $result = MlsStorageService::Insert('t_twitter', $column_vals);
     */
    public static function Insert($type, $column_vals) {
        $mlsStorage = self::GetMlsStorage();
        return $mlsStorage->Insert($type, $column_vals);
    }

    public static function Update($type, $column_vals, $key_name, $key_vals) {
        $mlsStorage = self::GetMlsStorage();
        return $mlsStorage->Update($type, $column_vals, $key_name, $key_vals);
    }

    public static function Delete($type, $key_name, $key_vals) {
        $mlsStorage = self::GetMlsStorage();
        return $mlsStorage->Delete($type, $key_name, $key_vals);
    }
}

?>
