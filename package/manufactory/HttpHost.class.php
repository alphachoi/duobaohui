<?php
namespace Snake\Package\Manufactory;
/*
 * 获取http_host
 * wangwei
 */
 class HttpHost{

		private  static  $_Obj = NULL;
		/*
		 * 访问这个类的唯一方法
		 */
		public static function getInstance(){
			if(is_null(self::$_Obj)){
				 self::$_Obj = new HttpHost;
				 return self::$_Obj;
			}else{
				return self::$_Obj;
			}
		}
		public function getHost() {
			return "http://" . $_SERVER['HTTP_HOST'] . "/"; 	
		}
		/*
		 * 不允许直接声明本类
		 */
	    private function __construct(  ){

		}
		/*
		 * 禁止clone
		 */
		private function __clone(){
		}
}
