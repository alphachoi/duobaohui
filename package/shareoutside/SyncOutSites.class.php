<?php
namespace Snake\Package\Shareoutside;

/**
 * 杂志社分享到外站相关接口
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Picture\Picture;
Use \Snake\Package\Shareoutside\ShareHelper;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Twitter\Twitter;

/**
 * TODO 目前只支持分享到qzone,sina微博；腾讯微博,qplus暂不支持,
 * @since 2012-07-30
 * @version 1.0
 */
class SyncOutSites implements \Snake\Libs\Interfaces\Iobserver {

	private $twitter = array();
	private $syncSets = array('2' => '_syncPic', '7' => '_syncGoods', '8' => '_syncForwards');
	public function __construct() {
	}

    public function onChanged($sender, $args) {
        $this->twitter = $args['twitter'];
        if (!in_array($this->twitter['twitter_show_type'], array(2, 7, 8))) {
            return FALSE;
        }
        if (isset($this->twitter) && $this->twitter['sync_to_weibo'] == 1) {
			$type = 'weibo';
			$func = $syncSets[$this->twitter['twitter_show_type']];
			$this->$func($type);
			/*if ($this->twitter['twitter_show_type'] == 2) {
            	$this->_syncPic('weibo');
			}
			elseif ($this->twitter['twitter_show_type'] == 7) {
            	$this->_syncGoods('weibo');
			}
			elseif ($this->twitter['twitter_show_type'] == 8) {
            	$this->_syncForwards('weibo');
			}*/
        }
        if (isset($this->twitter) && $this->twitter['sync_to_qzone'] == 1) {
			$type = 'qzone';
			$func = $syncSets[$this->twitter['twitter_show_type']];
			$this->$func($type);
			/*if ($this->twitter['twitter_show_type'] == 2) {
            	$this->_syncPic('qzone');
			}
			elseif ($this->twitter['twitter_show_type'] == 7) {
            	$this->_syncGoods('qzone');
			}
			elseif ($this->twitter['twitter_show_type'] == 8) {
            	$this->_syncForwards('qzone');
			}*/
        }
    }

	private function _syncPic($type = 'weibo') {
        $extras = array();
        $role = 3;
        $extras = array();
        $extras['groupUrl'] = "http://wap.meilishuo.com/group/" . $this->twitter['group_id'] . "?frm=huiliu_connectweibopic&time=" . $this->twitter['twitter_create_time'];
        if (empty($this->twitter['twitter_content'])) {
            $content = "我在@美丽说 分享了一张图片——欢迎围观我的杂志#" . $this->twitter['group_name'] . "# >>" . $extras['groupUrl'];
        }   
        else {
            $content = $this->twitter['twitter_content'] . "——欢迎围观我的杂志#" . $this->twitter['group_name'] . "# >>" . $extras['groupUrl'];
        }   
        if ($type == 'qzone') {
            $extras['groupUrl'] = "http://wap.meilishuo.com/group/" . $this->twitter['group_id'] . "?frm=huiliu_connectqzonepic&time=" . $this->twitter['twitter_create_time'];
            $role = 4;
            $content = "点击进入>>我的杂志《" . $this->twitter['group_name'] . "》" ;
            $extras['comment'] = "千辛万苦的收集啊，满满都是我心水的~~不点进来看看，怎么好意思说是我的朋友呢？！";
        }   
		$extras['url'] = $extras['groupUrl'] . $this->twitter['twitter_create_time'];
		
        $col = array('picid','n_pic_file','nwidth','nheight');
        $pictureAssembler = new Picture($col);
        $picInfo = $pictureAssembler->getPictureByPids(array($this->twitter['twitter_images_id']));

        $extras['image'] = $picInfo[0]['n_pic_file'];
        ShareHelper::sync($this->twitter['twitter_author_uid'], 'group', $this->twitter['twitter_id'], $role, 0, $content, NULL, $extras);
	}

	private function _syncGoods($type = 'weibo') {
		$fields = array('goods_title', 'goods_pic_url');
		$goodsHelper = new Goods($fields);
		$goodsInfo = $goodsHelper->getGoodsByGids(array($this->twitter['twitter_goods_id']));
        $tag = 3;
        if (empty($this->twitter['twitter_content'])) {
            $content = "我在@美丽说 分享了[" . $goodsInfo[0]['goods_title'] . "]——欢迎围观我的杂志#" . $this->twitter['group_name'] . "#" ;
        }   
        else {
            $content = $this->twitter['twitter_content'] . "——欢迎围观我在@美丽说 的杂志#" . $this->twitter['group_name'] . "#" ;
        }   
        $extras = array();
        if ($type == 'qzone') {
            $tag = 4;
            $content = "点击进入>>我的杂志《" . $this->twitter['group_name'] . "》" ;
            $extras['comment'] = "千辛万苦的收集啊，满满都是我心水的~~不点进来看看，怎么好意思说是我的朋友呢？！";
        }   
        $extras['image'] = $goodsInfo[0]['goods_pic_url'];
        $extras['tid'] = $this->twitter['twitter_id'];

        $extras['group_id'] = $this->twitter['group_id'];

        $extras['source'] = 1;

        //同步新鲜事
        ShareHelper::sync($this->twitter['twitter_author_uid'], 'goods', '', $tag, 0, $content, '', $extras);
	}

	private function _syncForwards($type = 'weibo') {
        $t_s_id = $this->twitter['twitter_source_tid'];
        $extras = array();
        $tag = 3;
        if (!empty($t_s_id)) {
			//$fields = array('twitter_images_id');
			//$twitterHelper = new Twitter($fields);
            //$twitter_source_info = $twitterHelper->getTwitterByTids(array($this->twitter['twitter_source_tid']));

        	$col = array('picid','n_pic_file','nwidth','nheight');
        	$pictureAssembler = new Picture($col);
        	$picInfo = $pictureAssembler->getPictureByPids(array($this->twitter['twitter_images_id']));
            $extras['image'] = $picInfo[0]['n_pic_file'];
        }   
        if ($type == 'weibo') {
            $extras['groupUrl'] = "http://wap.meilishuo.com/group/" . $this->twitter['group_id'] . "?frm=huiliu_connectweiborepin&time=" . $this->twitter['twitter_create_time'];
        }   
        $extras['url'] = $extras['groupUrl'] . $this->twitter['twitter_create_time']; 
        if (empty($this->twitter['twitter_goods_id'])) {
            if (empty($this->twitter['twitter_content'])) {
                $content = "我在@美丽说 分享了一张图片——欢迎围观我的杂志#" . $this->twitter['group_name'] . "# >>" . $extras['groupUrl'];
            }   
            else {
                $content = $this->twitter['twitter_content'] . "——欢迎围观我在@美丽说 的杂志#" . $this->twitter['group_name'] . "# >>" . $extras['groupUrl'];
            }   
        }   
        else {
			$fields = array('goods_title', 'goods_pic_url');
			$goodsHelper = new Goods($fields);
			$goodsInfo = $goodsHelper->getGoodsByGids(array($this->twitter['twitter_goods_id']));
            if (empty($this->twitter['twitter_content'])) {
                $content = "我在@美丽说 分享了[" . $goodsInfo[0]['goods_title'] . "]——欢迎围观我的杂志#" . $this->twitter['group_name'] . "# >>" . $extras['groupUrl'] ;
            }   
            else {
                $content = $this->twitter['twitter_content'] . "——欢迎围观我在@美丽说 的杂志#" . $this->twitter['group_name'] . "# >>" . $extras['groupUrl'];
            }   
    
        }   
        if ($type == 'qzone') {
            $extras['groupUrl'] = "http://wap.meilishuo.com/group/" . $this->twitter['group_id'] . "?frm=huiliu_connectqzonerepin&time=" . $this->twitter['twitter_create_time'];
            $extras['url'] = $extras['groupUrl'] . $this->twitter['twitter_create_time'];
            $tag = 4;
            $extras['comment'] = "千辛万苦的收集啊，满满都是我心水的~~不点进来看看，怎么好意思说是我的朋友呢？！";
            $content = "点击进入>>我的杂志《" . $this->twitter['group_name'] . "》";
        }
        ShareHelper::sync($this->twitter['twitter_author_uid'], 'group', $this->twitter['twitter_id'], $tag, 0, $content, array(), $extras);
	}
}
