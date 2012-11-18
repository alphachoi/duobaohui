<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package thrift.transport
 */

namespace Snake\Libs\PlatformService;

use \Snake\Libs\Thrift\Transport\TTransport;
use \Snake\Libs\Thrift\Transport\TTransportException;

require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TTransport.php');

function MyGetTime(){
    list($usec,$sec) = explode(' ',microtime());
    $milliSec = (int)((float)$usec * 1000);
    $intSec = intval($sec);
}

function MlscacheLog($level,$str){
   list($usec,$sec) = explode(' ',microtime());
   $milliSec = (int)((float)$usec * 1000000);
   $intSec = intval($sec);
   $ret = file_put_contents(LOG_FILE_BASE_PATH . '/mlscache.' . date('YmdH',$intSec) . '.log',
       sprintf("%s %s:%d:%d %s\n",$level,date('Y-m-d H:i:s', $intSec),$milliSec,0,$str),FILE_APPEND);
}



/**
 * Sockets implementation of the TTransport interface.
 *
 * @package thrift.transport
 */
class TMlsSocket extends TTransport {

    /**
     * Handle to PHP socket
     *
     * @var resource
     */
    private $handle_ = null;

    /**
     * php socket using create_socket
     *
     * @var resource
     */
    private $socket_ = null;

    /**
     * Remote hostname
     *
     * @var string
     */
    protected $host_ = 'localhost';

    /**
     * Remote port
     *
     * @var int
     */
    protected $port_ = '9090';

    /**
     * Send timeout in seconds.
     *
     * Combined with sendTimeoutUsec this is used for send timeouts.
     *
     * @var int
     */
    private $sendTimeoutSec_ = 0;

    /**
     * Send timeout in microseconds.
     *
     * Combined with sendTimeoutSec this is used for send timeouts.
     *
     * @var int
     */
    private $sendTimeoutUsec_ = 100000;

    /**
     * Recv timeout in seconds
     *
     * Combined with recvTimeoutUsec this is used for recv timeouts.
     *
     * @var int
     */
    private $recvTimeoutSec_ = 0;

    /**
     * Recv timeout in microseconds
     *
     * Combined with recvTimeoutSec this is used for recv timeouts.
     *
     * @var int
     */
    private $recvTimeoutUsec_ = 750000;

    /**
     * Persistent socket or plain?
     *
     * @var bool
     */
    protected $persist_ = FALSE;

    /**
     * Debugging on?
     *
     * @var bool
     */
    protected $debug_ = FALSE;

    /**
     * Debug handler
     *
     * @var mixed
     */
    protected $debugHandler_ = null;
    protected $id_ = "";

    /**
     * Socket constructor
     *
     * @param string $host         Remote hostname
     * @param int    $port         Remote port
     * @param bool   $persist      Whether to use a persistent socket
     * @param string $debugHandler Function to call for error logging
     */
    public function __construct($host='localhost',
                                $port=9090,
                                $persist=FALSE,
                                $debugHandler=null) {
        $this->host_ = $host;
        $this->port_ = $port;
        $this->persist_ = $persist;
        $this->debugHandler_ = $debugHandler ? $debugHandler : 'error_log';
        $this->id_ = "";
    }

    public function __destruct() {
        if ($this->isOpen()) {
            $this->close();
        }
    }

    /**
     * @param resource $handle
     * @return void
     */
    public function setHandle($handle) {
        $this->handle_ = $handle;
    }

    /**
     * @param resource $socket
     * @return void
     */
    public function setSocket($socket) {
        $this->socket_ = $socket;
    }

    /**
     * Sets the send timeout.
     *
     * @param int $timeout  Timeout in milliseconds.
     */
    public function setSendTimeout($timeout) {
        $this->sendTimeoutSec_ = floor($timeout / 1000);
        $this->sendTimeoutUsec_ =
            ($timeout - ($this->sendTimeoutSec_ * 1000)) * 1000;
    }

    /**
     * Sets the receive timeout.
     *
     * @param int $timeout  Timeout in milliseconds.
     */
    public function setRecvTimeout($timeout) {
        $this->recvTimeoutSec_ = floor($timeout / 1000);
        $this->recvTimeoutUsec_ =
            ($timeout - ($this->recvTimeoutSec_ * 1000)) * 1000;
    }

    /**
     * Sets debugging output on or off
     *
     * @param bool $debug
     */
    public function setDebug($debug) {
        $this->debug_ = $debug;
    }

    /**
     * Get the host that this socket is connected to
     *
     * @return string host
     */
    public function getHost() {
        return $this->host_;
    }

    /**
     * Get the remote port that this socket is connected to
     *
     * @return int port
     */
    public function getPort() {
        return $this->port_;
    }

    /**
     * Tests whether this is open
     *
     * @return bool true if the socket is open
     */
    public function isOpenBK() {
        return is_resource($this->handle_);
    }

    public function isOpen() {
        return is_resource($this->socket_);
    }

    /**
     * Connects the socket with create_socket.
     */
    public function open() {
        if ($this->isOpen()) {
            throw new TTransportException('Socket already connected', TTransportException::ALREADY_OPEN);
        }

        if (empty($this->host_)) {
            throw new TTransportException('Cannot open null host', TTransportException::NOT_OPEN);
        }

        if ($this->port_ <= 0) {
            throw new TTransportException('Cannot open without port', TTransportException::NOT_OPEN);
        }

        $t1 = microtime(TRUE);
        if ($this->persist_) {
            throw new Exception('Not Implement persist socket');
        }
        else {
            $this->socket_ = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            @socket_set_block($this->socket_);
            @socket_set_option($this->socket_, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $this->sendTimeoutSec_, 'usec' => $this->sendTimeoutUsec_)); 
            @socket_set_option($this->socket_, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->recvTimeoutSec_, 'usec' => $this->recvTimeoutUsec_));
            $connection = @socket_connect($this->socket_, $this->host_, $this->port_); 
        }

        $t2 = microtime(TRUE);
        // Connect failed?
        if ($this->socket_ === FALSE) {
            $errno = @socket_last_error();
            $errstr = @socket_strerror($errorcode);
            $error = 't1='.$t1.' t2='.$t2 . 'TSocket: Could not connect to '.$this->host_.':'.$this->port_.' ('.$errstr.' ['.$errno.'])';
            if ($this->debug_) {
                call_user_func($this->debugHandler_, $error);
            }
            throw new TException($error);
        }   
        $this->id_ = $this->host_ . ':' . $this->port_;
    }

    /**
     * Connects the socket.
     */
    public function openBK() {
        if ($this->isOpen()) {
            throw new TTransportException('Socket already connected', TTransportException::ALREADY_OPEN);
        }

        if (empty($this->host_)) {
            throw new TTransportException('Cannot open null host', TTransportException::NOT_OPEN);
        }

        if ($this->port_ <= 0) {
            throw new TTransportException('Cannot open without port', TTransportException::NOT_OPEN);
        }

        $t1 = microtime(TRUE);
        if ($this->persist_) {
            $this->handle_ = @pfsockopen($this->host_,
                                         $this->port_,
                                         $errno,
                                         $errstr,
                                         $this->sendTimeoutSec_ + ($this->sendTimeoutUsec_ / 1000000));
        }
        else {
            $this->handle_ = @fsockopen($this->host_,
                                        $this->port_,
                                        $errno,
                                        $errstr,
                                        $this->sendTimeoutSec_ + ($this->sendTimeoutUsec_ / 1000000));
        }

        $t2 = microtime(TRUE);
        // Connect failed?
        if ($this->handle_ === FALSE) {
            $error = 't1='.$t1.' t2='.$t2 . 'TSocket: Could not connect to '.$this->host_.':'.$this->port_.' ('.$errstr.' ['.$errno.'])';
            if ($this->debug_) {
                call_user_func($this->debugHandler_, $error);
            }
            throw new TException($error);
        }
        $this->id_ = $this->host_ . ':' . $this->port_ . ','
            .  stream_socket_get_name($this->handle_,FALSE);
    }

    /**
     * Closes the socket.
     */
    public function closeBK() {
        if (!$this->persist_) {
            @fclose($this->handle_);
            $this->handle_ = null;
        }
    }

    public function close() {
        if (!$this->persist_) {
            @socket_close($this->socket_);
            $this->socket_ = null;
        }
    }

    /**
     * Read from the socket at most $len bytes.
     *
     * This method will not wait for all the requested data, it will return as
     * soon as any data is received.
     *
     * @param int $len Maximum number of bytes to read.
     * @return string Binary data
     */
    public function readBK($len) {
        $null = null;
        $read = array($this->handle_);
        //MlscacheLog('DEBUG','act=read step=startSelect len='.$len. ' id=' . $this->id_);
        $readable = @stream_select($read, $null, $null, $this->recvTimeoutSec_, $this->recvTimeoutUsec_);

        if ($readable > 0) {
            $data = @stream_socket_recvfrom($this->handle_, $len);
            if ($data === false) {
                throw new TTransportException('failRead act=read len='.$len.' id='.
                                              $this->id_);
            }
            elseif ($data == '' && feof($this->handle_)) {
                throw new TTransportException('read_0_bytes act=read id=' . $this->id_);
            }

            return $data;
        }
        else if ($readable === 0) {
            throw new TTransportException('readTimeout act=read len='.$len.' id='.
                                          $this->id_);
        }
        else {
            throw new TTransportException('selectError act=read ret='.$readable .' len='.$len.' id='.
                                          $this->id_);
        }
    }   
  
    public function read($len) {
        $offset = 0;
        $socketData = '';
                 
        while ($offset < $len) {
            if (($data = @socket_read($this->socket_, $len - $offset)) === false) {
                $errorcode = @socket_last_error();
                if (11 === $errorcode) { // EAGAIN
                    //continue;
                } else if (4 === $errorcode) { // Interrupted system call
                    continue;
                }
                $errormsg = @socket_strerror($errorcode);
                throw new TTransportException('readFail act=read len=' . $len . 
                                              ' ret=' . $errormsg . '[' . $errorcode . '] id=' . $this->id_);
            }
            $dataLen = strlen($data);
            if ($dataLen == 0) {
                break;
            }
            $offset += $dataLen;
            $socketData .= $data;         
        }
        return $socketData;
    }

    /**
     * Write to the socket.
     *
     * @param string $buf The data to write
     */
    public function writeBK($buf) {
        $null = null;
        $write = array($this->handle_);

        // keep writing until all the data has been written
        while (strlen($buf) > 0) {
            // wait for stream to become available for writing
            $writable = @stream_select($null, $write, $null, $this->sendTimeoutSec_, $this->sendTimeoutUsec_);
            if ($writable > 0) {
                // write buffer to stream
                $written = @stream_socket_sendto($this->handle_, $buf);
                if ($written === -1 || $written === false) {
                    throw new TTransportException('sendtoFail act=write len='.strlen($buf).' ret='.$written
                                                  . ' id='.$this->id_);
                }
                // determine how much of the buffer is left to write
                $buf = substr($buf, $written);
            }
            else if ($writable === 0) {
                throw new TTransportException('selectTimeout act=write len='.strlen($buf).' id='.
                                              $this->id_);
            }
            else {
                throw new TTransportException('selectError act=write len='.strlen($buf).' id='.
                                              $this->id_);
            }
        }
    }   

    function write($buf) {
        $length = strlen($buf);
        while (true) {
            $sent = @socket_write($this->socket_, $buf, $length);
            if ($sent === false) {
                $errorcode = @socket_last_error();
                if (11 === $errorcode) {
                    //continue;
                } else if (4 === $errorcode) { // Interrupted system call
                    continue;
                }
                $errormsg = @socket_strerror($errorcode);
                throw new TTransportException('writeFail act=write len=' . strlen($buf) . 
                                              ' ret='.$errormsg . '[' . $errorcode . '] id=' . $this->id_);
            }
            if ($sent < $length) {
                $buf = substr($buf, $sent);
                $length -= $sent;
            }
            else {
                break;
            }
        }
    }


    /**
     * Flush output to the socket.
     *
     * Since read(), readAll() and write() operate on the sockets directly,
     * this is a no-op
     *
     * If you wish to have flushable buffering behaviour, wrap this TSocket
     * in a TBufferedTransport.
     */
    public function flush() {
        // no-op
    }
    public function getID() {
        return $this->id_;  
    }
}
