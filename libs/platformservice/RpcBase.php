<?php

namespace Snake\Libs\PlatformService;

use \Snake\Libs\Thrift;
use \Snake\Libs\Thrift\Transport\TBufferedTransport;
use \Snake\Libs\Thrift\Protocol\TBinaryProtocol;

require_once($GLOBALS['THRIFT_ROOT'] . '/Thrift.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TBufferedTransport.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php');
require_once(PLATFORM_SERVICE_PATH . '/RpcConfig.php');

class RpcBase {
	private $socket;
	private $transport;
	private $client;
	private $configs;
	
	const RECONNECT_NUM = 6;
	
	public function __construct($configs) {
		$this->configs = $configs;
		$this->init($configs);
	}
	
	public function init($configs) {
		$idx = rand(1,100) % count($configs);
		$serviceConfig = $configs[$idx];
		$this->socket = new TSocket($serviceConfig->host, $serviceConfig->port);
		$this->socket->setRecvTimeout(40000);
        $this->transport = new $serviceConfig->buffer($this->socket, 1024, 1024);
        $protocol = new TBinaryProtocol($this->transport);
        $clientClassName = $serviceConfig->serviceName.'Client';
        require_once($serviceConfig->includePath.$serviceConfig->serviceName.'.php');
        $this->client= new $clientClassName($protocol, $protocol);
	}
	
	public function request($method, $params) {
		//$begin = microtime(true);
		if (method_exists($this->client, $method) === false) {
            //log
            self::rpcLogger($method, $params,
                "\nThis interface does not exist in the RPC service : \"".$method."\"\n");
            throw new Exception('This interface does not exist in the RPC service');
        }

        try {
            $this->openTransport();
            $result = call_user_func(array($this->client, $method), $params);
            $this->closeTransport();
        } catch (Exception $e) {
             $this->closeTransport();
            self::rpcLogger($method, $params, $e->getMessage());
            throw $e;
        }
        
        //log
        //$end = microtime(true);
        //self::rpcLogger($method, $params, $end - $begin);
        return $result;
	}
	
	private function openTransport()
    {
        $connected = false;
        $numConnect = 0;
        do {
            $numConnect++;
            try {
                if($this->transport !== null) {
                    $this->transport->open();
                    $connected = true;
                }
            } catch (Exception $e) {
            	//echo 'fail:',$numConnect;
                if ($connected == false && $numConnect < self::RECONNECT_NUM ) {
                    $this->retrieveClient();
                } else {
                    throw $e;
                }
            }
        } while ($connected == false && $numConnect < self::RECONNECT_NUM );
    }
    
 	private function closeTransport()
    {
        if($this->transport !== null) {
            $this->transport->close();
        }
    }

    private function retrieveClient()
    {
        $this->init($this->configs);
    }
    
	public static function rpcLogger($method, $params = null, $result = null)
	{
		$f = @fopen(LOG_FILE_BASE_PATH.'rpc.log', 'a+');
		$str = "\n---------[".date('Y-m-d H:i:s')."]---------\n";
        $str .= "<<method>> : ".$method."\n<<paramters>> :\n";
        if ($params) {
            foreach ($params as $param) {
                //$str .= var_export($param, true)."\n";
            }
        }
        $str .= "<<result>> : ".var_export($result, true)."\n";
        fwrite($f, $str);
        fclose($f);
    }
}
