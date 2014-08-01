<?php

namespace Comments;

use Core\ModuleController;
use Register\Models\Login;

/**
 * Class Controller
 * @package Comments
 */
class Controller implements ModuleController
{

    /**
     *
     */
    public function __construct()
    {
        Login::loginRequired();
        new Models\Process($_GET['action']);
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