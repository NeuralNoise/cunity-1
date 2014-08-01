<?php

namespace Register\View;

use Core\View\Mail\MailView;

/**
 * Class VerifyMail
 * @package Register\View
 */
class VerifyMail extends MailView
{

    /**
     * @var string
     */
    protected $_templateDir = "register";
    /**
     * @var string
     */
    protected $_templateFile = "register-mail.tpl";

    /**
     * @var string
     */
    protected $_subject = "Your Cunity-Registration";

    /**
     * @param $receiver
     * @param $registerSalt
     */
    public function __construct($receiver, $registerSalt)
    {
        parent::__construct();
        $this->_receiver = $receiver;
        $this->assign("name", $receiver["name"]);
        $this->assign('registerSalt', $registerSalt);
        $this->show();
    }

}
