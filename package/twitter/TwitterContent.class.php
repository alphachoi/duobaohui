<?php
namespace Snake\Package\Twitter;

class TwitterContent {

	private $filters = array();
	private $twitter = array();

	private $htmlContent = NULL;
	private $args = array();

	public function __construct($twitter = array()) {
		$this->twitter = $twitter;
		$this->htmlContent = trim($this->twitter['twitter_htmlcontent']);
		$this->addFilter(new TwitterMaskWords());
		$this->addFilter(new UrlFilter());
		$this->addFilter(new TwitterAt());
	}

	private function addFilter(\Snake\Libs\Interfaces\Ifilter $filter) {
		$this->filters[] = $filter;
	}

	public function operate() {
		foreach ($this->filters as $filter) {
			list($this->twitter, $this->args) = $filter->filter($this->twitter, $this->args);
		}
	}
}
