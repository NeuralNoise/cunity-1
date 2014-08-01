<?php

namespace Contact;

use Core\ModuleController;

/**
 * Class Controller
 * @package Contact
 */
class Controller implements ModuleController
{

    /**
     *
     */
    public function __construct()
    {
        $this->handleRequest();
    }

    /**
     *
     */
    private function handleRequest()
    {
        if (!isset($_GET['action']) || empty($_GET['action']))
            new View\ContactForm();
        else if (isset($_GET['action']) && $_GET['action'] == "sendContact")
            new Models\ContactForm();
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


