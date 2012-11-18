<?php
namespace Snake\Modules\User;
Use Snake\Package\User\User                      AS User;
Use Snake\Package\User\Testdb                      AS Testdb;

USE \Snake\Libs\Phpmailer\SendMail  AS SendMail;

class Testemail extends \Snake\Libs\Controller {

	public function run() {
		$email = 'whh7y7@126.com';
		$title = 'test duobaohui';
		$body = '<html><body>test duobaohui Register<body/><html/>';
		$userName = 'wawowo';
		
		$rs = SendMail::run($email, $title, $body, $userName);
	}
}
