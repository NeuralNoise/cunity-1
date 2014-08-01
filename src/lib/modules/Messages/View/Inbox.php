<?php

namespace Messages\View;

use Core\View\View;

/**
 * Class Inbox
 * @package Messages\View
 */
class Inbox extends View
{

    /**
     * @var string
     */
    protected $_templateDir = "messages";
    /**
     * @var string
     */
    protected $_templateFile = "inbox.tpl";
    /**
     * @var array
     */
    protected $_metadata = ["title" => "My Conversations"];

    /**
     * @throws \Core\Exception
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerScript("messages", "inbox");
        $this->registerCss("messages", "inbox");
        $this->render();
    }

    /**
     *
     */
    public function render()
    {
        $this->show();
    }

}
