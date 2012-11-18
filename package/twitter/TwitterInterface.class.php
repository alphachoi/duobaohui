<?php
namespace Snake\Package\Twitter;

Use \Snake\Libs\PlatformService\MlsStorageService;
Use \Snake\Libs\Thrift\Packages\Anyval;

class TwitterInterface {

	private $fields = array();

	private $limits = array();

	public function __construct($fields = array(), $limits = array()) {
		$this->fields = $this->transferStorageData($fields);
		$this->limits = $this->transferStorageData($limits);
	}
	
	public function select() {
	}

	public function add() {
		$keys = array_keys($this->fields);
		$this->fields = array_values($this->fields);
		$cols = array();
		$params = array();
		foreach ($keys as $key) {
			$cols[] = "`" . $key . "`";
			$params[] = '?';
		}
		$field = implode(",", $cols);
		$param = implode(",", $params);
		$sql = "INSERT INTO t_twitter (" . $field . ") VALUES (" . $param . ")";
		
		$retObject = MlsStorageService::PreStmtWrite($sql, $this->fields);
		$ret = \Snake\Libs\Base\Utilities::objectToArray($retObject);

		return $ret;
	}

	public function update() {
	}

	private function transferStorageData($data) {
		$col_vars = array();
		foreach ($data as $field => $value) {
			$func = "__tsfm_" . $field;
			$col_var = self::$func($value);
			$col_vars[$field] = $col_var;
		}
		return $col_vars;
	}

	private static function __tsfm_twitter_id($sql_twitter_id) {
		$twitter_id = new AnyVal();
		$twitter_id->SetI32($sql_twitter_id);
		return $twitter_id;
	}

	private static function __tsfm_twitter_author_uid($sql_twitter_author_uid) {
		$twitter_author_uid = new AnyVal();
		$twitter_author_uid->SetI32($sql_twitter_author_uid);
		return $twitter_author_uid;
	}

	private static function __tsfm_twitter_images_id($sql_twitter_images_id) {
		$twitter_images_id = new AnyVal();
		$twitter_images_id->SetI64($sql_twitter_images_id);
		return $twitter_images_id;
	}

	private static function __tsfm_twitter_content($sql_twitter_content) {
		$twitter_content = new AnyVal();
		$twitter_content->SetString($sql_twitter_content);
		return $twitter_content;
	}

	private static function __tsfm_twitter_htmlcontent($sql_twitter_htmlcontent) {
		$twitter_content = new AnyVal();
		$twitter_content->SetString($sql_twitter_htmlcontent);
		return $twitter_content;
	}

	private static function __tsfm_twitter_source_code($sql_twitter_source_code) {
		$twitter_source_code = new AnyVal();
		$twitter_source_code->SetString($sql_twitter_source_code);
		return $twitter_source_code;
	}
	
	private static function __tsfm_twitter_source_uid($sql_twitter_source_uid) {
		$twitter_source_uid = new AnyVal();
		$twitter_source_uid->SetI64($sql_twitter_source_uid);
		return $twitter_source_uid;
	}

	private static function __tsfm_twitter_create_ip($sql_twitter_create_ip) {
		$twitter_create_ip = new AnyVal();
		$twitter_create_ip->SetString($sql_twitter_create_ip);
		return $twitter_create_ip;
	}

	private static function __tsfm_twitter_create_time($sql_twitter_create_time) {
		$twitter_create_time = new AnyVal();
		$twitter_create_time->SetI64($sql_twitter_create_time);
		return $twitter_create_time;
	}

	private static function __tsfm_twitter_show_type($sql_twitter_show_type) {
		$twitter_show_type = new AnyVal();
		$twitter_show_type->SetI16($sql_twitter_show_type);
		return $twitter_show_type;
	}

	private static function __tsfm_twitter_goods_id($sql_twitter_goods_id) {
		$twitter_goods_id = new AnyVal();
		$twitter_goods_id->SetI32($sql_twitter_goods_id);
		return $twitter_goods_id;
	}

	private static function __tsfm_twitter_source_tid($sql_twitter_source_tid) {
		$twitter_source_tid = new AnyVal();
		$twitter_source_tid->SetI32($sql_twitter_source_tid);
		return $twitter_source_tid;
	}

	private static function __tsfm_twitter_options_num($sql_twitter_options_num) {
		$twitter_options_num = new AnyVal();
		$twitter_options_num->SetI32($sql_twitter_options_num);
		return $twitter_options_num;
	}

	private static function __tsfm_twitter_options_show($sql_twitter_options_show) {
		$twitter_options_show = new AnyVal();
		$twitter_options_show->SetI16($sql_twitter_options_show);
		return $twitter_options_show;
	}

	private static function __tsfm_twitter_pic_type($sql_twitter_pic_type) {
		$twitter_pic_type = new AnyVal();
		$twitter_pic_type->SetI16($sql_twitter_pic_type);
		return $twitter_pic_type;
	}

	private static function __tsfm_twitter_reply_show($sql_twitter_reply_show) {
		$twitter_reply_show = new AnyVal();
		$twitter_reply_show->SetI16($sql_twitter_reply_show);
		return $twitter_reply_show;
	}

}
