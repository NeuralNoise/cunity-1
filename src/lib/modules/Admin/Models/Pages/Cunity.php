<?php

namespace Admin\Models\Pages;
use Core\Models\Db\Table\Modules;

/**
 * Class Cunity
 * @package Admin\Models\Pages
 */
class Cunity extends PageAbstract {

    /**
     *
     */
    public function __construct() {
        $this->loadData();
        $this->render("cunity");
    }

    /**
     * @throws \Exception
     */
    private function loadData() {
        $modules = new Modules();
        $installedModules = $modules->getModules()->toArray();
        $config = \Core\Cunity::get("config");
        $this->assignments['smtp_check'] = $config->mail->smtp_check;
        $this->assignments['modules'] = $installedModules;
    }

}
