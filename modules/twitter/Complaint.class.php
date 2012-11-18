<?php
namespace Snake\Modules\Twitter;

use \Snake\Package\Spam\TwitterComplaint;

class Complaint extends \Snake\Libs\Controller {

	private $user;
	private $twitterId;
	private $type;

	public function run() {
		$this->user = $this->userSession;
		$this->twitterId = $this->request->POST['tid'];
		$this->type = $this->request->POST['type'];

		$TwitterComplaint = new TwitterComplaint($this->user, $this->twitterId, $this->type);
		$ret = $TwitterComplaint->twitterComlpaint();
		if (200 === $ret) {
			$this->view = "OK";
			return TRUE;
		}
		elseif (401 === $ret) {
			$this->setError(401, 40100, "Unauthorised");
			$this->view = "Unauthorised";
			return FALSE;
		}
		else {
			$this->setError(400, 40000, "Bad Request");
			$this->view = "Bad Request";
			return FALSE;
		}
	}
}
