<?php

namespace Snake\Modules\Seo;

use Snake\Package\Seo\SeoModel;

class Seowords extends \Snake\Libs\Controller {


    public function run() {
        $this->main();
    }

    private function main() {
        $this->head = 200;
        $limit = $this->request->path_args[0];
        $seowords = SeoModel::getInstance()->seoWords($limit);
        $this->view = $seowords;
        return;
    }

    private function errorMessage($code, $message) {
        $this->head = 400;
        $this->view = array('code' => $code, 'message' => $message);
        return TRUE;
    }

}

?>
