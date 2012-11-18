<?php

namespace Snake\Libs\PlatformService;

class ServiceConfig
{
    public $serviceName;
    public $host;
    public $port;
    public $include;
    public $buffer;

    public function __construct($serviceName, $host, $port, $buffer = 'TBufferedTransport', $includePath = '')
    {
        $this->serviceName = $serviceName;
        $this->host      = $host;
        $this->port      = $port;
        $this->buffer    = $buffer;
        $this->includePath   = $includePath;
    }
}
