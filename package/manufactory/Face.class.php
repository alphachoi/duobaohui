<?php
namespace Snake\Package\Manufactory;
/*
 * 表情singleton
 * wangwei
 */
 class Face{

		const  F33 = '[小红心]';
		
		private  static  $_Obj = NULL;

		private  static $_IndexofFaceTable = array('[笑]'  ,'[晕死]'  ,'[泪汪汪]','[害羞]','[问号]','[流泪]','[得意]'  ,'[抓狂]'  ,'[酷]'  ,
									 	           '[怒]'  ,'[坏笑]'  ,'[心碎]'  ,'[猪头]','[猥琐]','[囧]'  ,'[转眼珠]','[刚巴德]','[长草]',
	 									           '[财迷]','[星星眼]','[白菜]','[鄙视]','[飞吻]','[色色]','[调皮]','[拒绝]','[骷髅]','[泪]','[汗]','[么么]','[如花]','[思考]','[小红心]');

	    private  static $_HashFaceTable = array(
	    										 '[笑]'     =>'<img title="笑" src="http://img.meilishuo.net/css/images/face_small/1.gif" class="facetableSetxy"/>',
	    									 	 '[晕死]'   =>'<img title="晕死" src="http://img.meilishuo.net/css/images/face_small/2.gif" class="facetableSetxy"/>',
	    									 	 '[泪汪汪]' =>'<img title="泪汪汪" src="http://img.meilishuo.net/css/images/face_small/3.gif" class="facetableSetxy"/>',
	    									 	 '[害羞]'   =>'<img title="害羞" src="http://img.meilishuo.net/css/images/face_small/4.gif" class="facetableSetxy"/>',
	    									 	 '[问号]'   =>'<img title="问号" src="http://img.meilishuo.net/css/images/face_small/5.gif" class="facetableSetxy"/>',
	    									 	 '[流泪]'   =>'<img title="流泪" src="http://img.meilishuo.net/css/images/face_small/6.gif" class="facetableSetxy"/>',
	    									 	 '[得意]'   =>'<img title="得意" src="http://img.meilishuo.net/css/images/face_small/7.gif" class="facetableSetxy"/>',
											 	 '[抓狂]'   =>'<img title="抓狂" src="http://img.meilishuo.net/css/images/face_small/8.gif" class="facetableSetxy"/>',
											 	 '[酷]'     =>'<img title="酷" src="http://img.meilishuo.net/css/images/face_small/9.gif" class="facetableSetxy"/>',
											 	 '[怒]'     =>'<img title="怒" src="http://img.meilishuo.net/css/images/face_small/10.gif" class="facetableSetxy"/>',
											 	 '[坏笑]'	=>'<img title="坏笑" src="http://img.meilishuo.net/css/images/face_small/11.gif" class="facetableSetxy"/>',
											 	 '[心碎]'	=>'<img title="心碎" src="http://img.meilishuo.net/css/images/face_small/12.gif" class="facetableSetxy"/>',
											 	 '[猪头]'   =>'<img title="猪头" src="http://img.meilishuo.net/css/images/face_small/13.gif" class="facetableSetxy"/>',
											 	 '[猥琐]'   =>'<img title="猥琐" src="http://img.meilishuo.net/css/images/face_small/14.gif" class="facetableSetxy"/>',
											 	 '[囧]'  	=>'<img title="囧" src="http://img.meilishuo.net/css/images/face_small/15.gif" class="facetableSetxy"/>',
											 	 '[转眼珠]' =>'<img title="转眼珠" src="http://img.meilishuo.net/css/images/face_small/16.gif" class="facetableSetxy"/>',
											 	 '[刚巴德]' =>'<img title="刚巴德" src="http://img.meilishuo.net/css/images/face_small/17.gif" class="facetableSetxy"/>',
											 	 '[长草]'   =>'<img title="长草" src="http://img.meilishuo.net/css/images/face_small/18.gif" class="facetableSetxy"/>',
											 	 '[财迷]'   =>'<img title="财迷" src="http://img.meilishuo.net/css/images/face_small/19.gif" class="facetableSetxy"/>',
											 	 '[星星眼]' =>'<img title="星星眼" src="http://img.meilishuo.net/css/images/face_small/20.gif" class="facetableSetxy"/>',
											 	 '[白菜]'   =>'<img title="白菜" src="http://img.meilishuo.net/css/images/face_small/21.gif" class="facetableSetxy"/>',
											 	 '[鄙视]'   =>'<img title="鄙视" src="http://img.meilishuo.net/css/images/face_small/22.gif" class="facetableSetxy"/>',
											 	 '[飞吻]'   =>'<img title="飞吻" src="http://img.meilishuo.net/css/images/face_small/23.gif" class="facetableSetxy"/>',
											 	 '[色色]'   =>'<img title="色色" src="http://img.meilishuo.net/css/images/face_small/24.gif" class="facetableSetxy"/>',
											 	 '[调皮]'   =>'<img title="调皮" src="http://img.meilishuo.net/css/images/face_small/25.gif" class="facetableSetxy"/>',
											 	 '[拒绝]'   =>'<img title="拒绝" src="http://img.meilishuo.net/css/images/face_small/26.gif" class="facetableSetxy"/>',
											 	 '[骷髅]'   =>'<img title="骷髅" src="http://img.meilishuo.net/css/images/face_small/27.gif" class="facetableSetxy"/>',
											 	 '[泪]'	    =>'<img title="泪" src="http://img.meilishuo.net/css/images/face_small/28.gif" class="facetableSetxy"/>',
	    									 	 '[汗]'		=>'<img title="汗" src="http://img.meilishuo.net/css/images/face_small/29.gif" class="facetableSetxy"/>',
	    									 	 '[么么]'   =>'<img title="么么" src="http://img.meilishuo.net/css/images/face_small/30.gif" class="facetableSetxy"/>',
	    									 	 '[如花]'   =>'<img title="如花" src="http://img.meilishuo.net/css/images/face_small/31.gif" class="facetableSetxy"/>',
	    									 	 '[思考]'   =>'<img title="思考" src="http://img.meilishuo.net/css/images/face_small/32.gif" class="facetableSetxy"/>',
	    									 	 Face::F33 =>'<img title="小红心" src="http://img.meilishuo.net/css/images/person/mylove.gif" class="facetableSetxy"/>'
										   );
		/*
		 * 访问这个类的唯一方法
		 */
		public static function getInstance(){
			if(is_null(self::$_Obj)){
				 self::$_Obj = new Face;
				 return self::$_Obj;
			}else{
				return self::$_Obj;
			}
		}
		/*
		 * 表情转化主函数
		 */
		public  function _getFaceCode( $contentStr ){
			$contentStr = strtr($contentStr , self::$_HashFaceTable);
			return $contentStr;

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
