<?php

namespace Snake\Libs\PlatformService;

class tableColumnInfo {
    public static $tableInfo = array(
        't_twitter' =>'twitter_id,twitter_author_uid,twitter_images_id,twitter_content,twitter_htmlcontent,twitter_source_code,twitter_source_uid,twitter_create_ip,twitter_create_time,twitter_show_type,twitter_goods_id,twitter_source_tid,twitter_options_num,twitter_options_show,twitter_pic_type,twitter_reply_show',
        't_twitter' =>'twitter_id,twitter_author_uid,twitter_images_id,twitter_content,twitter_htmlcontent,twitter_source_code,twitter_source_uid,twitter_create_ip,twitter_create_time,twitter_show_type,twitter_goods_id,twitter_source_tid,twitter_options_num,twitter_options_show,twitter_pic_type,twitter_reply_show',
        't_picture' =>'picid,ctime,n_pic_file,authorid,nwidth,nheight',
		/*taoeaten@test
        't_dolphin_fut_goods_info' =>'goods_id,goods_author_uid,goods_title,goods_url,goods_pic_url,goods_pic_id,goods_author_note,goods_author_ctime,goods_source_type,goods_picture_id,goods_price,good_weight,cataid'
		*/
        't_dolphin_fut_goods_info' =>'goods_id,goods_author_uid,goods_title,goods_url,goods_pic_url,goods_pic_id,goods_author_ctime,goods_source_type,goods_picture_id,goods_price,good_weight,cataid'
    );
}

?>
