<?php
namespace Snake\Libs\Interfaces;

interface IObserver {

	public function onChanged($sender, $args);

}
