<?php
namespace Snake\Libs\Base;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SnakeLog {

	private $logfile;
	private $DEBUG_LOGMOD = 0;
	private $SYS_LOGMOD = 0;
	private $LOG_MOD;
	private $LOG_PATH = '/home/work/webdata/logs/';
	private $REAL_PATH ;
	private $REAL_FILENAME;
	private $collectLogsBlack = array('syslog', 'snakelog', 'db_syslog', 'timepush', 'access_qzone', 'error_qzone', 'qplusShare', 'error_qplus', 'access_weibo', 'error_weibo', 'error_txweiboShare');
	private $collectLogs = array('syslog', 'db_errorlog', 'snakelog');
	
	public function __construct($filename, $logMod = 'DEBUG' ){
		try {
			$this->logfile = $filename;
			$this->LOG_MOD = $logMod;
			
			$path_parts = pathinfo($filename);
			if ($logMod == 'DEBUG') {
				$this->REAL_PATH = $this->LOG_PATH . 'DEBUG/' . $path_parts["dirname"];
			}
			else {
				$this->REAL_PATH = $this->LOG_PATH . $path_parts["dirname"];
			}
			if ( !is_dir( $this->REAL_PATH ) ) {
				system("mkdir -p " . $this->REAL_PATH . ";chmod -R 777 " . $this->REAL_PATH);
			}
			$this->REAL_FILENAME = $path_parts["basename"].".".date("YmdH");
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	public function w_log($str) {
		if ($str == null || $str == '') {
			return null;
		}
		if ($this->DEBUG_LOGMOD == 0 && $this->LOG_MOD == 'DEBUG') {
			return null;
		}
		
		$currentTime = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);

        $str = "[$currentTime]\t" . $str;
        if (!in_array($this->logfile, $this->collectLogsBlack) && defined('LOG_COLLECT') && LOG_COLLECT) {
			$filename = $this->logfile;
            $logCollect = \Snake\Libs\Base\LogCollect::instance();
            $logCollect->sendLog($filename, $str); 
			if ($this->logfile != 'db_errorlog') {
				return; 
			}
        }

		$file = $this->REAL_PATH . "/" . $this->REAL_FILENAME;
		$str = $str . "\n";
		// 20% 的snakelog到性能监控
		if (defined('AMQP_SWICTHER') && AMQP_SWICTHER == 1 && in_array($this->logfile, $this->collectLogs) && rand(1, 5) === 5) {
			$this->insertQeue($str);
			//not return， write all log to fe local server 
			//return;
		}
		@file_put_contents($file, $str, FILE_APPEND);
	}

	public function insertQeue($str) {		
		if ($this->logfile == 'syslog') {
			$addKey = "snakelog";
		}
		$addKey = $this->logfile;
		$key = date('Y-m-d H:i:s');
		$snakeLogKey = $addKey . $key;
		\Snake\Package\Group\Helper\RedisSnakeLog::rPush($snakeLogKey, $str);
		return FALSE;

	}
}
