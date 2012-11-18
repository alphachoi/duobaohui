<?php
namespace Snake\Package\Permission;

class TokenAuthed {
	private $authorizedApi = array(
		'woxihuan' => array(
				'Snake\Modules\Woxihuan\Data_list',
				'Snake\Modules\Woxihuan\Item',
		),
		'qqphoto' => array(
				'Snake\Modules\Qq\Data_index',
				'Snake\Modules\Qq\Board',
		),
		'captcha_register' => array(
				'Snake\Modules\Register\Captcha',	
		),
		'u' => array(
				'Snake\Modules\Target\Targeturl',
		),
	);
	private $user = NULL;
	private $class = NULL;

	public function __construct($user, $class) {
		$this->user = $user;
		$this->class = $class;
	}

	public function getAuthorized() {
		if (!empty($this->authorizedApi[$this->user]) 
			&& in_array($this->class, $this->authorizedApi[$this->user])) {
			return TRUE;
		}
		return FALSE;
	}
}
