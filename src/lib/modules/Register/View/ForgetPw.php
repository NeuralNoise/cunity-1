<?php
namespace Register\View;

use Core\View\View;

/**
 * Class ForgetPw
 * @package Register\View
 */
class ForgetPw extends View
{

    /**
     * @var string
     */
    protected $_templateDir = "register";
    /**
     * @var string
     */
    protected $_templateFile = "forgetpw.tpl";
    /**
     * @var string
     */
    protected $_languageFolder = "Register/languages";
    /**
     * @var array
     */
    protected $_metadata = ["title" => "Reset Password"];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function render()
    {
        $this->show();
    }
}


