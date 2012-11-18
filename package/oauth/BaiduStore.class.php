<?php
namespace Snake\Package\Oauth;
/***************************************************************************
 *
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Baidu.php
 * 
 * 
 * @package	Baidu
 * @author	zhujt(zhujianting@baidu.com)
 * @version	$Revision: 1.0 Mon Jun 27 10:52:18 CST 2011
 **/
 
abstract class BaiduStore
{
	protected $apiKey;
	
	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}
	
	/**
	 * Get the variable value specified by the variable key name for
	 * current session user from the storage system.
	 * 
	 * @param string $key Variable key name
	 * @param mix $default Default value if the key couldn't be found
	 * @return mix Returns the value for the specified key if it exists, 
	 * otherwise return $default value
	 */
	abstract public function get($key, $default = false);
	
	/**
	 * Save the variable item specified by the variable key name into
	 * the storage system for current session user.
	 * 
	 * @param string $key	Variable key name
	 * @param mix $value	Variable value
	 * @return bool Returns true if the saving operation is success,
	 * otherwise returns false
	 */
	abstract public function set($key, $value);
	
	/**
	 * Remove the stored variable item specified by the variable key name
	 * from the storage system for current session user.
	 * 
	 * @param string $key	Variable key name
	 * @return bool Returns true if remove success, otherwise returns false
	 */
	abstract public function remove($key);
	
	/**
	 * Remove all the stored variable items for current session user from
	 * the storage system.
	 * 
	 * @return bool Returns true if remove success, otherwise returns false
	 */
	abstract public function removeAll();
	
	/**
	 * Prints to the error log if you aren't in command line mode.
	 *
	 * @param String log message
	 */
	public static function errorLog($msg)
	{
		// disable error log if we are running in a CLI environment
		if (php_sapi_name() != 'cli') {
			error_log($msg);
		}
		// uncomment this if you want to see the errors on the page
		// print 'error_log: '.$msg."\n";
	}
}
