<?php
namespace Snake\Modules\Edm;

use Snake\Package\Group\Groups;

class Group  extends \Snake\Libs\Controller {

    private $group_id;

    public function run() {
        $this->group_id = $this->request->path_args[0];
        //获得九宫格的信息
        $gHelper = new Groups;
        $info = $gHelper->getGroupSquareInfo(array($this->group_id), 0); 
        $info = array_values($info);
        $info = $info[0];
        if(!isset($info['mixpic'])) {
            $this->view = array();
            return false;
        }   
        $this->view =  $info;
    }   
}
