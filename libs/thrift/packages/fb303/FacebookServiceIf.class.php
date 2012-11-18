<?php
namespace Snake\Libs\Thrift\Packages\Fb303;
/**
 * Autogenerated by Thrift Compiler (0.8.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
include_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';

include_once $GLOBALS['THRIFT_ROOT'].'/packages/fb303/fb303_types.php';

interface FacebookServiceIf {
  public function getName();
  public function getVersion();
  public function getStatus();
  public function getStatusDetails();
  public function getCounters();
  public function getCounter($key);
  public function setOption($key, $value);
  public function getOption($key);
  public function getOptions();
  public function getCpuProfile($profileDurationInSec);
  public function aliveSince();
  public function reinitialize();
  public function shutdown();
}
