<?php
namespace Snake\Package\Group;

use \Snake\Package\Group\GroupFactory;
use \Snake\Package\Twitter\TwitterFactory;
use \Snake\Package\Picture\PictureFactory;
use \Snake\Package\Relation\UserRelationGroup;
use \Snake\Package\Twitter\Twitter;

class GroupSquare implements \Snake\Libs\Interfaces\Square {

	private $id = NULL;
	private $url = NULL;
	private $words = NULL;
	private $mClient = NULL;

	private $group = array();
	private $twitter = array();
	private $picture = array();
	private $userFollowGroup = array();
	private $squares = array();

	public function __construct($id, $url, $words, $requests) {
		$this->id = $id;
		$this->url = $url;
		$this->words = $words;
		$this->user_id = $user_id;
		$this->mClient = \Snake\Libs\Base\MultiClient::getClient(0);
		foreach ($requests as $request) {
			$group_ids[] = $request['id'];
		}

		$groupFactory = new GroupFactory($group_ids);
		$groupFactory->fillElements(0, 9);
		$this->groups = $groupFactory->getGroups();

		foreach ($group_ids as $group_id) {
			$grouprequest = array();
            $grouprequest = array(
                'multi_func' => 'pop_group_twitter',
                'method' => 'GET',
                'group_id' => $group_id,
                'self_id' => 0,
            );
            $g_request[] = $grouprequest;	
		}

		$group_picUrls = $this->mClient->router($g_request);
		$group_pic = array();
		if (!empty($group_picUrls)) {
			foreach ($group_picUrls AS $key => $value) {
				$group_pic[$group_ids[$key]] = $group_picUrls[$key]['pic'];
			}
		}
		$key = 0;
		foreach ($this->groups as &$group) {
			if (!empty($group_pic[$group->group_id])) { 
				$group->mixpic = $group_pic[$group->group_id];
			}
			elseif (!empty($group->twitter_ids)) {
				foreach ($group->twitter_ids as $tid => $tinfo) {
					$twitter_ids[] = $tid; 
				}
			}	
			$key ++;
		}
		
		if (!empty($twitter_ids)) {
			$twitterHelper = new Twitter();
			$twitterInfo = $twitterHelper->getPicturesByTids($twitter_ids, "c");
			
			$this->pictures = $twitterInfo;
		}
	}

	public function getSquareInfos() {

		foreach ($this->groups as $group) {
			if (!empty($group->mixpic)) {
				$this->squares[] = $group;
			}
			else {
				$urls = array();
				if (!empty($group->twitter_ids)) {
					foreach ($group->twitter_ids as $twitter_id => $info) {
						$picurl = $this->pictures[$twitter_id]['n_pic_file'];
						if (!empty($picurl)) {
							$urls[] = $picurl;
						}
					}
					$urls = array_slice($urls, 0, 9);
				}
				$group->setPicUrl($urls);
				$group->is_follower = 0;
				
				$this->squares[] = $group;
			}
		}
		return $this->squares;
	}
}
