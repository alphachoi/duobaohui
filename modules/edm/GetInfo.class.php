<?php
namespace Snake\Modules\Edm;
use Snake\Package\Edm\Edm;
class GetInfo extends \Snake\Libs\Controller {
	
	public function run() {
		$emails = $this->request->REQUEST['email'];
		$emails = explode('-', $emails);
		$day = strtotime($this->request->REQUEST['date']);
		$types = $this->request->REQUEST['type'];
		$types = explode('-', $types);
		$num = $this->request->REQUEST['num'] ? $this->request->REQUEST['num'] : 10000;
		$project = $this->request->REQUEST['project'] ? $this->request->REQUEST['project'] : 1;
		foreach ($types as $typekey) {
			$type[$typekey] = $typekey;	
		}
		
		$edmHelper = new Edm();
		$sendEdm = new SendEdm();
		foreach($emails as  $email) {
			$email = '%@' .$email. '.com';
			$info = $edmHelper->getActiveUser($email, $day,$num);
			$detailInfo = $edmHelper->getDetailInfo($info,$type);
			foreach($detailInfo as $info) {
				$sendEdm->post($info, $type, $project);
			}
		}
	}
}
