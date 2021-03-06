<?php
namespace Snake\Package\Goods;

class CommentsTids {
	private $comments = array(
		596981469 => array( 
			"穿上超显瘦的，肚子上的肉肉都看不出来了，哈哈，满意！",
			"想把钟小姐的街拍款式穿上身就不要错过这款短夹克，菱格加短款，保暖又时髦。评价保暖厚实，但是肩宽的姑娘可要慎重考虑了。",
		), 
		602366257 => array( 
			"最爱大翻领，很帅气的说，灰色的更百搭！",
			"我发现今年飞行员的款式特别流行，相较于之前欧美流行的军绿魁梧设计感，日韩单做了调整之后感觉更适合亚洲人，刚刚拿到手就直接穿上了，配了 一件羊绒衫就很暖和了。",
		), 
		599603923 => array( 
			"菱形格的大包，不会有假名牌的感觉~ 容量够大！",
			"菱形包有什么特别的，经典款想要就有。但是多了个毛毛设计就是特别，搭配厚实的外套，整个冬天都弥漫着一股软绵绵的温暖感。大小很足够，放些上班用的烂七八糟刚刚好。",
		), 
		602552923 => array( 
			"品质很好，含毛量高，有兔毛成分！实物颜色鲜亮很好看！",
			"秋冬的课题对我意味着藏肉，宽松的毛衣我今年买了一件又一件，悲催啊！但是又觉得方便，下面竹炭袜或者打底裤就可以出街。穿了几天暂时没发现起球的现象，同事打算一起再入。",
		), 
		597700309 => array( 
			"很好，值这个价，很厚实！款式也很好看！如果里面要多穿，建议选大一号！",
			"天蝎月，最适合穿着的是灰色，黑色吧。流畅的简约设计，衣橱里的must have，约会通勤万能款，搭配小黑裙变成优雅lady，搭配巴洛克连衣裙，立马化身街拍达人。反正你就是需要来一款。",
		), 
		608851619 => array( 
			"去很舒服而且感觉特别温暖。单穿或者里边儿加打底衫都没问题！",
			"打昕薇炒红了这款单品之后，每年冬天保暖的毛毛系列成了爱美妞扮嗲不可多得的必备单品。刚入手这款韩货，性价比算不错了，现在经常在里面搭配衬衫和大衣穿。推荐酒红款。",
		), 
		604452031 => array( 
			"可以作名片夹又可以当钱包来用，一举两得。拿在手里相当有质感！简单大方！",
			"大妞必备，  皮质因批次原因，纹路也会不一样！两层牛皮，不到100的价格很抵了，想买钱包的姑娘可以看看。",
		), 
		604327131 => array( 
			"超复古系带短靴！穿着很舒服不磨脚！超赞！",
			"好看的绑带短靴，竟然还做出简约好看的层次，复古的圆头设计，环绕帮面系带，舒适稳重的粗跟，并且鞋底是耐磨的橡胶底，防滑安全。",
		), 
		602227807 => array( 
			"ASOS同款手表哦！戴起来超显时尚还有今天流行小胡子图案！超嗲的！",
			"趣怪的大胡子又来了！ASOS同款的手表，特别是一圈金边的设计，霸气外露。元方，这么便宜的价格你怎么看？",
		), 
		600608653 => array( 
			"超级有型的一款仿雪地靴休闲鞋！外形不会像雪地靴的基本款那么的笨重 很休闲不乏时尚感。",
			"我问朋友，今年流行的款式是什么？答：雪地靴。噗.....到了冬天离不开秋裤雪地靴的我，又摸爬滚打的找到一双，送亲送友送长辈。收礼只收雪地靴。"
		),
	);
	private $tidsIndex = array( 
		596981469, 602366257, 599603923, 602552923, 597700309,
		608851619, 604452031, 604327131, 602227807, 600608653,
	);



    public function getTids($frame) {
        if ($frame > 1 || $frame < 0) {
            return array();
        }
        return array_slice($this->tidsIndex, $frame * 5, 5);
    }

    public function getComment($tid, $ab = -1) {
        if (empty($tid) || $ab == -1 ) {
            return '';
        }
        $ab = $ab - 1;
        return $this->comments[$tid][$ab];
    }





}
