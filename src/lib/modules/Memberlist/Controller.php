<?php

namespace Memberlist;

use Core\ModuleController;
use Register\Models\Login;

/**
 * Class Controller
 * @package Memberlist
 */
class Controller implements ModuleController
{

    /**
     *
     */
    public function __construct()
    {
        Login::loginRequired();
        $this->handleRequest();
    }

    /**
     *
     */
    private function handleRequest()
    {
        if (isset($_GET['action']) && $_GET['action'] == "load") {
            $p = new Models\Process();
            $p->getAll();
        } else
            new View\Memberlist();
    }

    /**
     * @param $user
     * @return mixed|void
     */
    public static function onRegister($user)
    {

    }

    /**
     * @param $user
     * @return mixed|void
     */
    public static function onUnregister($user)
    {

    }

}
