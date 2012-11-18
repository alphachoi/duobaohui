<?php
namespace Snake\Modules\Twitter;

use \Snake\Package\Twitter\TwitterLike;

class Like extends \Snake\Libs\Controller {

	public function run() {
		//$tid = 74089415;
		$tid = $this->request->GET['tid'];
		$twitterLike = new TwitterLike($tid, array('user_id' => 1227713));
		$twitterLike->twitterLike();
	}
}
