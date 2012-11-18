<?php
namespace Snake\Package\Twitter;
Use \Snake\Package\Base\DomainObjectAssembler;

class TwitterActivity implements \Snake\Libs\Interfaces\Iobserver {
	private $twitter = array();
	private $twitterActivity = array();

	public function __construct() {
	}


	private function insertTwitterActivity() {
		if (empty($this->twitter['activity_id'])) {
			return FALSE;	
		}
		$twitterActivityObj = new TwitterActivityObject($this->twitter);
		$domainObjectAssembler = new DomainObjectAssembler(TwitterActivityPersistenceFactory::getFactory('\Snake\Package\Twitter\TwitterActivityPersistenceFactory'));
		//插入t_dolphin_activity_twitter
		$domainObjectAssembler->insert($twitterActivityObj);
		return TRUE;	
	}

	public function onChanged($sender, $args) {
		if (isset($args['twitter']['activity_id']) && !empty($args['twitter']['activity_id'])) {
			$this->twitter = $args['twitter'];
		}
		else {
			return TRUE;
		}
		$this->twitterActivity['activity_id'] = $this->twitter['activity_id'];
		$this->twitterActivity['twitter_id'] = $this->twitter['twitter_id'];
		$this->twitterActivity['twitter_author_uid'] = $this->twitter['twitter_author_uid'];

		$this->insertTwitterActivity();
		return TRUE;
	}

}
