<?php
namespace Snake\Libs\Base;

Use \Snake\Libs\Thrift;
Use \Snake\Libs\Thrift\Transport\TFramedTransport;
Use \Snake\Libs\Thrift\Protocol\TBinaryProtocol;
Use \Snake\Libs\Thrift\Protocol\TBinaryProtocolAccelerated;
Use \Snake\Libs\Thrift\Packages\Scribe\ScribeClient;
Use \Snake\Libs\Thrift\Transport\TSocket;
Use \Snake\Libs\Thrift\Packages\Scribe\LogEntry;
require_once($GLOBALS['THRIFT_ROOT'] . '/packages/scribe/scribe_types.php');

Use Exception;

require_once($GLOBALS['THRIFT_ROOT'] . '/Thrift.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TSocket.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php');
require_once(PLATFORM_SERVICE_PATH . '/Scribe.php');

/**
 * @author Chen Hailong
 * 收集日志到统一的服务器 
 */
class LogCollect {
    /* 服务进程集合 */
    private $nodes_;
    /* 服务进程数 */
    private $nodesCount_ = 0;
    /* LogCollect 对象 */
    static $instance_ = NULL;

    static public function instance() {
        if (is_null(self::$instance_)) {
            self::$instance_ = new self();
        }
        return self::$instance_;
    }
	
	/**
	 * 构造函数，私有，单体模式
	 */
    private function __construct() {
		if (empty($GLOBALS['LOG_SERVER_CONFIG'])) {
			return FALSE;
		}
        $this->nodes_ = $GLOBALS['LOG_SERVER_CONFIG'];
        $this->nodesCount_ = count($this->nodes_);
    }

	public function sendLog($filename, $logStr) {
		if (empty($this->nodes_)) {
			\Snake\Libs\Base\Utilities::MlscacheLog('ERROR', "LogCollect nodes is empty. Please configure first.");
			return FALSE;
		}
		try {
			$scribeClient = $this->getScribeClient();
			$this->transport->open();
			!empty($_SERVER['SERVER_ADDR']) ? $nIp = $_SERVER['SERVER_ADDR'] : $nIp = '127.0.0.1';
			$fromIp = gethostbyname($_SERVER['HOSTNAME']);
			$logStr .= "\t[$fromIp]\t[$nIp]";
			$msg1['category'] = $filename;
			$msg1['message'] = $logStr; 
			$entry1 = new LogEntry($msg1);
			$messages = array($entry1);
			$scribeClient->Log($messages);
			$this->transport->close();
		}
		catch (\Exception $e) {
			\Snake\Libs\Base\Utilities::MlscacheLog('ERROR', "LogCollect_scribe log timeout. Error message: " . $e->getMessage());
		}
	}
	
	/**
	 * @author Chen Hailong
	 * 获取Scribe client, 多台服务，随机函数负载均衡
	 */
    private function getScribeClient() {
        $node = $this->nodes_[rand(0, $this->nodesCount_ - 1)];
        $host = $node['host'];
        $port = $node['port'];
        $socket = new TSocket($host, $port, TRUE);
        $socket->setSendTimeout(1000);
        $socket->setRecvTimeout(1000);

        $this->transport = $transport = new TFramedTransport($socket);
        $protocol = new TBinaryProtocolAccelerated($transport);
        $client = new ScribeClient($protocol, $protocol);
        return $client;
    }

}

