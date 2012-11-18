<?php
namespace Snake\Package\Twitter;
Use \Snake\Package\Base\DomainObjectAssembler;

class TwitterStat implements \Snake\Libs\Interfaces\Iobserver {
	private $twitter = array();
	private $twitterStat = array();

	public function __construct() {
	}

	private function insertStat() {
		$twitterStatObj = new TwitterStatObject($this->twitterStat);
		$domainObjectAssembler = new DomainObjectAssembler(TwitterStatPersistenceFactory::getFactory('\Snake\Package\Twitter\TwitterStatPersistenceFactory'));
		//插入t_dolphin_twitter_stat
		$domainObjectAssembler->insert($twitterStatObj);
	}

	public function onChanged($sender, $args) {
		$this->twitter = $args['twitter'];
		if (!in_array($this->twitter['twitter_show_type'], array(2, 7, 8))) {
			return TRUE;
		}
		$this->twitterStat['twitter_id'] = $this->twitter['twitter_id'];
		$this->twitterStat['twitter_author_uid'] = $this->twitter['twitter_author_uid'];
		$this->twitterStat['twitter_goods_id'] = $this->twitter['twitter_goods_id'];
		$this->twitterStat['twitter_ctime'] = $this->twitter['twitter_ctime'];

		$this->insertStat();
		return TRUE;
	}

}
