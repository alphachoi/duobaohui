<?php
namespace Snake\Libs\Base;

/**
 * task is a dolphin or snake script run on a remote machine like task01
 * author jianxu
 * version 1.0
 */

class Task {

	private $frame = NULL;
	private $class = NULL;
	private $parameters = array();
	private $client = NULL;

	public static function getTask($frame) {
		return new self($frame);
	}

	private function __construct($frame) {
		$this->frame = $frame;
		$this->client = \Snake\Libs\Base\ZooClient::getClient(0);
	}

	public function setFile($class) {
		$this->class = $class;
		return $this;
	}

	public function setParams($parameters) {
		$this->parameters = $parameters;
		return $this;
	}

	public function run() {
		if ($this->frame == 'dolphin') {
			$this->runDolphinTask();
		}
		elseif ($this->frame == 'snake') {
			$this->runSnakeTask();
		}
		else {
			throw new \Exception("wrong frame");
		}
	}

	private function runDolphinTask() {
		$params = array();
		$params['file'] = $this->class;
		$params['data'] = json_encode($this->parameters);
		$this->client->user_task($params);
	}

	private function runSnakeTask() {
		$params = array();
		$params['file'] = $this->class;
		$params['data'] = json_encode($this->parameters);
		$this->client->user_snake_task($params);
	}
}
