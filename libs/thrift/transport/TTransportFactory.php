<?php

namespace Snake\Libs\Thrift\Transport;

class TTransportFactory {
  /**
   * @static
   * @param TTransport $transport
   * @return TTransport
   */
  public static function getTransport(TTransport $transport) {
    return $transport;
  }
}
