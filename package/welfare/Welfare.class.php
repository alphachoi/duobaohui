<?php
namespace Snake\Package\Welfare;

/**
 * @author guoshuaitan@meilishuo.com
 * @since 2012-06-28
 * @version 1.0
 */

Use Snake\Libs\DB\MakeSql;
Use Snake\Package\Welfare\Helper\DBWelfareHelper;
Use Snake\Package\User\User;
/**
 * Welfare class 
 *
 * 品牌用户的信息
 * @author guoshuaitan@meilishuo.com
 * @since 2012-06-28
 * @version 1.0
 */

class Welfare {	
	/**
	 * @var string table_name
	 * $access private
	 */
	private $table = 't_dolphin_activity_info';
	private $atable = 't_dolphin_activity_apply';
	private $wtable = 't_dolphin_activity_twitter';
	private static $instances;
	public function __construct() {}

	static function getInstance() {
		$class = get_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();	
		}
		return self::$instances[$class];
	}
	/**
	 * 获取福利社的相关信息
	 * @return array
	 * @access public 
	 */
	public function getData($param, $master = FALSE, $hashKey = '' ) {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType('select', $param);
		$result = DBWelfareHelper::getConn()->read($sql, array(), $master, $hashKey);
		return $result;
	}
	/**
	 * 获得福利社头部banner
	 * @return array
	 * @access public
	 */
	 public function getWelfareBanner($master = false, $hashkey = "") {
			 
	     $param = array('table' => 't_dolphin_cms_index_welcome',
					  'colum' => 'imgurl,linkurl,sortno /*welfare_header-gstan*/',
					  'where' => array('page_type' => 31),	
					  'order' => 'sortno'
	     );
		 $result = $this->getData($param, $master, $hashkey);
		 return $result;
	 }
	 /**
	  * 获得福利社总参加人数
	  * @return array
	  * @access public 
	  */
	  public  function getWelfareAllNum($master = false, $hashkey = "") {
		  
		  $param = array('table' => $this->atable,
					   'colum' => 'count(*) as num /*welfare_header-gstan*/',
					   'where' => array('status' => 1)			   
		  );
		  $result = $this->getData($param, $master, $hashkey);
		  return $result;
	  }

	 /**
	  * 获得最新的十个福利社
	  * @retuan array
	  * @access
	  */
	  public function getTopWelfare($num, $master = false, $hashkey = "") {
			  
		$param = array('table' => $this->table,
					   'colum' => 'activity_id,title,index_banner,trynumber,begin_time,end_time,valid/*welfare_header-gstan*/',
					   'where' => array('valid' => 1, ),
					   'where_not' => array('top_banner' => "''"),
					   'esp' => ' and activity_banner is NOT NULL',
					   'order' => 'begin_time desc',
					   'limit' => $num	
        );
		$result = $this->getData($param, $master, $hashkey);
		return $result;
	  }
	  /**
	   * 获得各活动的参加人数
	   * @return array
	   * @access public
	   */
	  public function getNumsByAids($aids = array(),$master = false, $hashkey = "activity_id") {
		  	   
	      if (empty($aids)) {
			  return FALSE;
		  }	
		  $aids = implode(',', $aids);
		  $param = array('table' => $this->atable,
						 'colum' => 'count(*) as num ,activity_id/*welfare_list-gstan*/',
						 'where_in' => array('activity_id' => $aids),
						 'esp' => 'group by activity_id'
		  );
		  $result = $this->getData($param, $master, $hashkey);
		  return $result;
	  }
	  /**
	   * 获得试用的福利社
	   * @return  array
	   * @access public
	   */
		public function getTryOnWelfare($offset = 0, $limit = 10, $type = 0, $master = false, $hashkey = "") {
			$time = time();
			$str = " and activity_id != 85  and activity_banner IS NOT NULL AND index_banner IS NOT NULL and begin_time is not null";
			if ($type != 0) {
				$str = " and begin_time > $time " . $str;
			}
			else {
				$str = " and begin_time < $time " . $str;	
			}
	       $param = array('table' => $this->table,
						  'colum' => "activity_id,activity_type,title,products_price,index_banner,trynumber,end_time,valid,begin_time/*welfare_list-gstan*/",
						  'where' => array('valid' => 1),
					      'esp' => $str,
						  'order' => 'end_time desc,activity_id desc',
						  'limit' => "$offset, $limit"
		   );
		   $result = $this->getData($param, $master, $hashkey );
		   return $result;
	   }
	   /**
	    * 获得各种状态最新参加福利社的人
		* @return array
		  @param $num 最新的人数
		* @access public
		*/
		public function getNewTakeInWelfare($num = 5, $status = 0, $master = false, $hashkey = "") {
				
			$param = array('table' => $this->atable,
						   'colum' => 'user_id,activity_id,ctime/*welfare_list-gstan*/',
						   'where' => array('status' => $status),
						   'order' => 'id  desc',
						   'limit' => '0, ' . $num
			);
			$result = $this->getData($param, $master, $hashkey);
			return $result;
		}
		/**
		 * 获得多个福利社的信息
		 * @return array
		 * @param  colum 获取的字段
		 * @param  aids array 福利社id
		 */
		 public function getWelfareInfoByIds($colum, $aids,$master = FALSE, $key = '') {
				 
	         if (empty($aids)) {
			    return FALSE;
		     }	
		     $aids = implode(',', $aids);
			 $param = array('table' => $this->table,
							'colum' => $colum,
							'where_in' => array('activity_id' => $aids)
			 );
			 $result = $this->getData($param, $master, $key);
			 return $result;
					
		}
		/**
		 * 获得福利社相关信息
		 * @return array
		 * @param $colum 获取的字段,
		 * @param id 福利社id
		 * @access public
		 */
		public function getWelfareInfoById($colum, $id, $master = FALSE, $key = '') {
			 
			$param = array('table' => $this->table,
							'colum' => $colum,
							'where' => array('activity_id' => $id)
			);
			$result = $this->getData($param, $master, $key);
			return $result;
		}
		/**
         * 获取福利社申请人相关信息
		 * @return array
		 * @param $colum 获取的相关字段
		 * @param id 福利社的id
		 */
		public function getApplyInfoById($colum, $id, $master = FALSE, $key = '') {
				 
			$param = array('table' => $this->atable,
					       'colum' => $colum,
						   'where' => array('activity_id' => $id)
			);
			$result = $this->getData($param, $master, $key);
			return $result;
		 }

		/**
         * 获取福利社推相关信息
		 * @return array
		 * @param $colum 获取的相关字段
		 * @param id 福利社的id
		 */
		public function getTwitterById($colum, $id, $master = FALSE, $key = '') {
				 
			$param = array('table' => $this->wtable,
					       'colum' => $colum,
						   'where' => array('activity_id' => $id)
			);
			$result = $this->getData($param, $master, $key);
			return $result;
		}
		/**
		  * 获取用户的一个福利社
		  *
		  *
		  */
		public function getOneWelfare($colum, $aid, $uid,  $master = FALSE, $key = "") {
			$param = array('table' => $this->atable,
						   'colum' => $colum,
						   'where' => array('activity_id' => $aid, 'user_id' => $uid),
						   'order' => 'status asc',
						   'limit' => 1
			  );
			  $result = $this->getData($param, $master, $key);
			  return $result;
		}
		/**
		 *获得参加福利设的用户头像
		 *
		 */
		public function getAvatarInWelfare($aid, $colum, $limit, $master = FALSE, $key = "") {
			if (empty($aid)) {
				return false;	
			}
            $param = array('table' => $this->atable,
						   'colum' => $colum,
						   'where' => array('activity_id' => $aid),
						   'order' => 'id desc',
						   'limit' => $limit+10

			);
			$result = $this->getData($param, $master, $key);
			$user_ids = array();
			if (!empty($result)) {
				 $user_ids = \Snake\Libs\Base\Utilities::DataToArray($result, 'user_id');		
			}
			if (empty($user_ids)) {
				return false;
			}
			$userHelper = new User();
			$param = array('user_id', 'avatar_c');
			$info = $userHelper->getUserInfos($user_ids, $param, "");	
			$gif = array('/css/images/0.gif', 'ap/c/f3/94/6857d75b2dac8a469d05ee11cc18_100_100.jpg','ap/c/fc/06/ad8fac52975c449395d1de74d075_100_100.jpg', 'ap/c/09/a8/7d60f32f1e842c83ac1f25386dcd_120_120.png', 'ap/c/b9/9a/10c1753b1430455bff57031fa1c1_180_180.gif', '/ap/c/fc/06/ad8fac52975c449395d1de74d075_100_100.jpg', '/ap/c/09/a8/7d60f32f1e842c83ac1f25386dcd_120_120.png', '/ap/c/b9/9a/10c1753b1430455bff57031fa1c1_180_180.gif', '/ap/c/64/9d/4912bf9c0458f48756bca1bd5b13_80_80.gif', '/ap/c/ca/55/52722bd27a5bd0ae78e5de2a63b0_80_80.png', '/ap/c/f5/1f/b71ab7042046661450087b3e8bb6_200_200.jpg');
			foreach ($info as $key=>$avatar) {
				if (!empty($avatar)) {
					foreach ($gif as $newgif) {
						if (strpos($avatar['avatar_c'], $newgif)) {
							unset($info[$key]);	
						}
					}
				}
			}
			$len = count($info);
			if ($len > $limit) {
				$info = array_slice($info, 0, $limit);	
			}
			$k = 0;
			$backInfo = array();
			foreach ($info as $key => $uInfo) {
				$backInfo[$k]['avatar_c'] = $uInfo['avatar_c'];
				$backInfo[$k]['user_id'] = $uInfo['user_id'];
				$k++;
			}
			return $backInfo;
		}
		/**
		 *活得所有福利社信息
		 *
		 */	
		public function getAllWelfare($colum, $master = false, $key = "") {
			$time = time();
			$param = array('table' => $this->table,
						   'colum' => $colum,
						   'where' => array('valid' => 1),
						   'esp' => " and activity_banner IS NOT NULL AND index_banner IS NOT NULL  and begin_time < $time and begin_time is not null "
			);
			
			$result = $this->getData($param, $master, $key);
			return $result;
			
		}
		/**
		 * 获得福利社预告
		 *
		 */
		 public function getTrailerWelfare($colum, $limit, $master = false, $key = "") {
			 $time = time();
			 $param = array('table' => $this->table,
						    'colum' => $colum,
							'where' => array('valid' => 1),
							'esp'   => " and products_preview_img is NOT NULL and (begin_time > $time or begin_time is NULL or begin_time = 0) and sortno > 0 ",
							'order' => " sortno asc ",
							'limit' => $limit
			 );
			 $result = $this->getData($param, $master, $key);
			 return $result;
			 
			 
		}
	

			
}

