<?php
namespace Snake\Package\Twitter;

Use \Snake\Package\Msg\Alert;

class TwitterAt implements \Snake\Libs\Interfaces\Ifilter {

	public function filter($twitter, $args) {
		$alert = new Alert();
		list($atContent, $atData) = $alert->hackAt($twitter['twitter_htmlcontent']);
		if (!empty($atData)) {
			$args['at'] = $atData;
		}
		$twitter['twitter_htmlcontent'] = $atContent;
		return array($twitter, $args);
	}

}
