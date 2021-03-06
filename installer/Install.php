<?php

/**
 * ########################################################################################
 * ## CUNITY(R) V2.0 - An open source social network / "your private social network"     ##
 * ########################################################################################
 * ##  Copyright (C) 2011 - 2015 Smart In Media GmbH & Co. KG                            ##
 * ## CUNITY(R) is a registered trademark of Dr. Martin R. Weihrauch                     ##
 * ##  http://www.cunity.net                                                             ##
 * ##                                                                                    ##
 * ########################################################################################.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or any later version.
 *
 * 1. YOU MUST NOT CHANGE THE LICENSE FOR THE SOFTWARE OR ANY PARTS HEREOF! IT MUST REMAIN AGPL.
 * 2. YOU MUST NOT REMOVE THIS COPYRIGHT NOTES FROM ANY PARTS OF THIS SOFTWARE!
 * 3. NOTE THAT THIS SOFTWARE CONTAINS THIRD-PARTY-SOLUTIONS THAT MAY EVENTUALLY NOT FALL UNDER (A)GPL!
 * 4. PLEASE READ THE LICENSE OF THE CUNITY SOFTWARE CAREFULLY!
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program (under the folder LICENSE).
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * If your software can interact with users remotely through a computer network,
 * you have to make sure that it provides a way for users to get its source.
 * For example, if your program is a web application, its interface could display
 * a "Source" link that leads users to an archive of the code. There are many ways
 * you could offer source, and different solutions will be better for different programs;
 * see section 13 of the GNU Affero General Public License for the specific requirements.
 *
 * #####################################################################################
 */
use Cunity\Admin\Models\Process;
use Cunity\Core\Exceptions\AlreadyInstalled;
use Cunity\Core\Exceptions\MissingConfig;
use Cunity\Core\Request\Get;
use Cunity\Core\Request\Request;
use Cunity\Core\Request\Session;
use Cunity\Core\Request\Server;

require_once __DIR__.'/../vendor/autoload.php';

ob_start('ob_gzhandler');
date_default_timezone_set('UTC');
chdir('..');
session_start();

/**
 * Class Install.
 */
class Install
{
    /**
     * @var String
     */
    private static $lang = 'en';

    /**
     * @var array
     */
    private static $langTexts = [];

    /**
     *
     */
    public function __construct()
    {
        if (Request::hasAction() &&
            Request::get('type') === 'ajax' &&
            Request::get('action') !== 'prepareDatabase'
        ) {
            \Cunity\Core\Cunity::init();
        } elseif (!Request::hasAction() &&
            Request::get('type') !== 'ajax'
        ) {
            $this->init();
        }

        $this->initTranslator();
        $this->handleRequest();
    }

    /**
     * @throws AlreadyInstalled
     * @throws MissingConfig
     */
    private function init()
    {
        if (file_exists(__DIR__.'/../data/config.xml')) {
            throw new AlreadyInstalled();
        }
        if (!file_exists(__DIR__.'/../data/config-example.xml')) {
            throw new MissingConfig();
        }
    }

    /**
     *
     */
    private function initTranslator()
    {
        if (Get::get('lang') !== null && (file_exists('installer/lang/'.Get::get('lang').'.php') || Get::get('lang') == 'en')) {
            self::$lang = Get::get('lang');
            Session::set('lang', self::$lang);
        } elseif (Session::get('lang') !== null && (file_exists('installer/lang/'.Session::get('lang').'.php') || Session::get('lang') === 'en')) {
            self::$lang = Session::get('lang');
        } else {
            self::$lang = 'en';
        }
        if (self::$lang !== 'en') {
            self::$langTexts = include 'installer/lang/'.self::$lang.'.php';
        }
    }

    /**
     * @throws Exception
     */
    private function handleRequest()
    {
        if (Request::hasAction() &&
            method_exists($this, Request::get('action'))
        ) {
            call_user_func([$this, Request::get('action')]);
        }
    }

    /**
     *
     */
    private function prepareDatabase()
    {
        if (Request::get('db-name') === '') {
            $this->outputAjaxResponse('databasename cannot be empty', false);
        }

        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            $this->outputAjaxResponse('gdlib', false);
        }

        $connection = mysqli_connect(Request::get('db-host'), Request::get('db-user'), Request::get('db-password'), Request::get('db-name'));

        if ($connection === false) {
            $this->outputAjaxResponse('could not connect to database', false);
        } else {
            $this->executeSql($connection);
        }

        $this->writeDatabaseConfig();
        $this->outputAjaxResponse();
    }

    /**
     * @param $response
     * @param bool $isSuccess
     */
    private function outputAjaxResponse($response = '', $isSuccess = true)
    {
        $responseObject = new stdClass();
        $responseObject->success = $isSuccess;
        $responseObject->response = $response;
        echo json_encode($responseObject);
        exit;
    }

    /**
     * @param String $input
     *
     * @internal param array $replacements
     *
     * @return String
     */
    public static function translate($input)
    {
        if (self::$lang == 'en' || !(isset(self::$langTexts[$input]))) {
            $str = $input;
        } else {
            $str = self::$langTexts[$input];
        }

        return $str;
    }

    /**
     * @param $connection
     */
    private function executeSql($connection)
    {
        $dbPrefix = Request::get('db-prefix');

        if ($dbPrefix !== '') {
            $dbPrefix .= '_';
        }

        $sqlData = file_get_contents(__DIR__.'/../resources/database/newcunity.sql');
        $sqlData = str_replace('TABLEPREFIX', $dbPrefix, $sqlData);
        mysqli_multi_query($connection, $sqlData);
    }

    /**
     *
     */
    private function prepareConfig()
    {
        foreach (Request::get('general', []) as $setting => $value) {
            if ($setting == 'core.siteurl' &&
                substr($value, -1) != '/'
            ) {
                $value .= '/';
            }
            $this->writeConfigToDatabase($setting, $value);
        }

        $this->writeConfigToFile(Request::get('config', []));
    }

    /**
     * @throws Zend_Config_Exception
     */
    private function writeDatabaseConfig()
    {
        if (!is_writable(__DIR__.'/../data/')) {
            $this->outputAjaxResponse('config', false);
        }

        if (!is_writable(__DIR__.'/../data/temp/')) {
            $this->outputAjaxResponse('temp', false);
        }

        $databaseConfig = [];
        $databaseConfig['db'] = [];
        $databaseConfig['db']['params'] = [];
        $databaseConfig['db']['params']['host'] = Request::get('db-host');
        $databaseConfig['db']['params']['username'] = Request::get('db-user');
        $databaseConfig['db']['params']['password'] = Request::get('db-password');
        $databaseConfig['db']['params']['dbname'] = Request::get('db-name');
        $databaseConfig['db']['params']['table_prefix'] = Request::get('db-prefix');

        $this->writeConfigToFile($databaseConfig, false);
    }

    /**
     * @param $newConfiguration
     * @param bool $update
     *
     * @throws Zend_Config_Exception
     */
    private function writeConfigToFile($newConfiguration, $update = true)
    {
        $configFile = __DIR__.'/../data/config-example.xml';

        if ($update) {
            $configFile = __DIR__.'/../data/config.xml';
        }

        $config = new Zend_Config_Xml($configFile);
        $configWriter = new Zend_Config_Writer_Xml(['config' => new Zend_Config(Process::arrayMergeRecursiveDistinct($config->toArray(), $newConfiguration)), 'filename' => __DIR__.'/../data/config.xml']);
        $configWriter->write();
    }

    /**
     * @param $field
     * @param $value
     */
    private function writeConfigToDatabase($field, $value)
    {
        $settings = new \Cunity\Core\Models\Db\Table\Settings();
        $settings->setSetting($field, $value);
    }

    /**
     *
     */
    private function prepareAdmin()
    {
        $user = new \Cunity\Core\Models\Db\Table\Users();
        $user->registerNewUser(Request::get(null, []), 3, false);

        $this->outputAjaxResponse();
    }
}

$installer = new Install();

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo Install::translate('Install Cunity'); ?></title>
        <link href="../lib/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../lib/plugins/fontawesome/css/font-awesome.css" rel="stylesheet">
        <script src="../lib/plugins/js/html5shiv.min.js"></script>
        <script src="../lib/plugins/js/respond.min.js"></script>
        <style>
            .breadcrumb > li + li:before {
                font-family: FontAwesome;
                font-style: normal;
                font-weight: normal;
                line-height: 1;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                padding: 0 10px;
                color: #ccc;
                content: "\f0da" !important;
            }

            .breadcrumb li {
                color: #b80718;
            }

            .progress {
                margin: 7px 0
            }

            .item {
                margin-right: 0;
                margin-left: 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                -webkit-box-shadow: none;
                box-shadow: none;
                position: relative;
                height: 643px;
                margin-bottom: 20px;
                padding: 50px 20px 20px 20px;
                background-color: #fff;
                overflow-y: auto;
            }

            .item > .title {
                position: absolute;
                top: 15px;
                left: 15px;
                font-size: 12px;
                font-weight: 700;
                color: #959595;
                text-transform: uppercase;
                letter-spacing: 1px;
                cursor: default;
            }

            .item .page-header {
                margin-top: 10px;
            }

            footer {
                text-align: center;
                margin: 30px auto;
                font-style: italic;
                font-size: 0.9em;
                color: #999;
            }

            #steps > li.active > a {
                color: #777 !important;
                text-decoration: none;
                cursor: default;
            }

            .item > form {
                margin: 0 15px;
            }

            .terms div.form-control {
                height: 500px !important;
                overflow-y: scroll;
                overflow-x: hidden;
            }

            #splashscreen {
                width: 350px;
                text-align: center;
                margin-top: 100px;
            }

            #splashscreen img.logo {
                width: 320px;
                padding: 15px;
            }
        </style>
    </head>
    <body>
    <?php if (Get::get('lang') === null) {
    ?>
        <div class="container" id="splashscreen">
            <img src="img/cunity-logo.gif" class="logo">

            <div class="login-container">
                <form>
                    <div class="form-group">
                        <label><?php echo Install::translate('Please select your language for the installation-process');
    ?></label>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                            <select class="form-control" name="lang">
                                <option value="en">English</option>
                                <option value="de">Deutsch</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block"
                                type="submit"><?php echo Install::translate('Start Installation');
    ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php

} else {
    ?>
        <div class="container" id="installation-container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <div class="page-header">
                        <h1><?php echo Install::translate('Install Cunity');
    ?></h1>
                    </div>
                    <div id="installCarousel" class="carousel slide">
                        <ol class="breadcrumb" id="steps" role="tablist">
                            <li><a href="Install.php"
                                   title="<?php echo Install::translate('Back to language selection');
    ?>"><i
                                        class="fa fa-globe"></i></a></li>
                            <li class="active"><?php echo Install::translate('Terms');
    ?></li>
                            <li><?php echo Install::translate('Database');
    ?></li>
                            <li><?php echo Install::translate('Settings');
    ?></li>
                            <li><?php echo Install::translate('Account');
    ?></li>
                            <li><?php echo Install::translate('Finish');
    ?></li>
                        </ol>

                        <div class="carousel-inner">
                            <div class="item active" id="terms">
                                <span class="title"><?php echo Install::translate('Terms and Conditions');
    ?></span>

                                <div class="terms">
                                    <form>
                                        <div class="form-group">
                                            <label><?php echo Install::translate('Please agree to our Terms & Conditions first');
    ?></label>

                                            <div class="form-control"><?php echo Install::translate('This program is distributed in the hope that it will be useful,<br />
    but WITHOUT ANY WARRANTY; without even the implied warranty of<br />
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br />
    GNU Affero General Public License for more details.<br />
<br />
	THIS FREE SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND<br />
    ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT<br />
    LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND<br />
    FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO<br />
    EVENT SHALL THE AUTHOR OR ANY CONTRIBUTOR BE LIABLE FOR<br />
    ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR<br />
    CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,<br />
    EFFECTS OF UNAUTHORIZED OR MALICIOUS NETWORK ACCESS;<br />
    PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,<br />
    DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED<br />
    AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT<br />
    LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)<br />
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN<br />
    IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.<br />
<br />
    * CUNITY(R) is open source, you may do the above mentioned actions under<br />
	  GNU Affero General Public License EXCEPT removing "Powered by CUNITY(R)",<br />
	  the logo or "(C) Smart In Media GmbH & Co. KG" without permission<br />
<br />
    * We highly encourage websites that promote our product<br />
<br />
    * You cannot sell your products on behalf of CUNITY, so you may sell them,<br />
	  but not as an official of CUNITY<br />
<br />
    * We are not responsible for any material or content that is found on<br />
	  "Powered by CUNITY(R)" websites<br />
<br />
    * All official products are listed on our Main website, there is no reseller or affiliate<br />
<br />
    * Without Purchase of Branding Removal, you cannot remove "Powered by CUNITY(R)", the<br />
	  logo or  "(C) Smart In Media GmbH & Co. KG" without permission  from your website<br />
<br />
    * You may use CUNITY(R) for multiple websites/servers.<br />
<br />
	* If you change the code / design, you must provide a link on your web-page for<br />
	  the users to an archive of the changed code.<br />
<br />
	* If you did not change the original code / design, you have to leave the link to<br />
	  http://www.cunity.net<br />
<br />
    * Distribution of this CUNITY(R) software under another license than GNU Affero<br />
	  General Public License is not allowed.<br />
<br />
    * Our Technical Support is not FREE, you can use our Forums (user-to-user) support<br />
<br />
	* You must not use the name CUNITY or its logo in any other context than on the software.<br />
<br />
Refund Policy<br />
<br />
    * Our products are intangible and virtual, and all products have online demos<br />
	  available so you can test each and every part of the products prior to your<br />
	  download/purchase. Once the services are rendered we can\'t provide you the refund.');
    ?>
                                            </div>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" value="1" name="accept-terms" id="accept-terms"
                                                       required="required">
                                                <?php echo Install::translate('I accept the Terms and Conditions');
    ?>
                                            </label>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="item" id="database">
                                <span class="title"><?php echo Install::translate('Setup Database');
    ?></span>
                                <h4 class="page-header">Database configuration</h4>

                                <form id="databaseForm" class="form-horizontal">
                                    <input type="hidden" name="action" value="prepareDatabase"/>
                                    <input type="hidden" name="type" value="ajax"/>

                                    <div class="form-group has-feedback">
                                        <label class="col-lg-3 control-label"
                                               for="db-host"><?php echo Install::translate('Database Host');
    ?></label>

                                        <div class="col-lg-7 ">
                                            <input type="text" id="db-host" class="form-control" value="localhost"
                                                   autocomplete="off"
                                                   name="db-host">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Hostname where your MySQL database is located');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class="col-lg-3 control-label"
                                               for="db-user"><?php echo Install::translate('Database User');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" id="db-user" class="form-control" autocomplete="off"
                                                   name="db-user">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Database username to connect');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class="col-lg-3 control-label"
                                               for="db-password"><?php echo Install::translate('Database Password');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="password" id="db-password" class="form-control"
                                                   autocomplete="off"
                                                   name="db-password">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Password for your database user');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class="col-lg-3 control-label"
                                               for="db-name"><?php echo Install::translate('Database Name');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" id="db-name" class="form-control" autocomplete="off"
                                                   name="db-name">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Name of your database to install Cunity');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class="col-lg-3 control-label"
                                               for="db-prefix"><?php echo Install::translate('Database Prefix');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" id="db-prefix" class="form-control" value="cunity"
                                                   autocomplete="off"
                                                   name="db-prefix">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Prefix for your tables, leave default value if you have no idea');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group has-feedback hidden error-message-config error-message">
                                        <label
                                            class="col-lg-10 control-label"><?php echo Install::translate('please check your user rights so data/config.xml is writeable');
    ?></label>
                                    </div>
                                    <div class="form-group has-feedback hidden error-message-temp error-message">
                                        <label
                                            class="col-lg-10 control-label"><?php echo Install::translate('please check your user rights so data/temp/ is writeable');
    ?></label>
                                    </div>
                                    <div class="form-group has-feedback hidden error-message-gdlib error-message">
                                        <label
                                            class="col-lg-10 control-label"><?php echo Install::translate('PHP GD libray is required in order to run Cunity');
    ?></label>
                                    </div>
                                    <div class="form-group has-feedback hidden has-success success-message">
                                        <label
                                            class="col-lg-10 control-label"><?php echo Install::translate('your configuration passed all tests, please proceed to the next step');
    ?></label>
                                    </div>
                                    <div class="form-group has-feedback col-lg-7">
                                        <button class="btn btn-primary btn-block" id="checkDatabase"><i
                                                class="fa-check fa"></i>&nbsp;<?php echo Install::translate('Check Connection & copy data to database');
    ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="item" id="settings">
                                <span class="title"><?php echo Install::translate('Enter Cunity-Settings');
    ?></span>
                                <h4 class="page-header"><?php echo Install::translate('General Settings');
    ?></h4>

                                <form class="form-horizontal" id="configForm">
                                    <input type="hidden" name="action" value="prepareConfig"/>
                                    <input type="hidden" name="type" value="ajax"/>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"
                                               for="sitename"><?php echo Install::translate('Name of your Cunity');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" name="general[core.sitename]" id="sitename"
                                                   class="form-control">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Your slogan');
    ?>" data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"
                                               for="siteurl"><?php echo Install::translate('URL of your Cunity');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" name="general[core.siteurl]" id="siteurl"
                                                   class="form-control" value="http://">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Where to find your installation');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"
                                               for="description"><?php echo Install::translate('Description');
    ?></label>

                                        <div class="col-lg-7">
                                            <textarea class="form-control" id="description"
                                                      name="general[core.description]"></textarea>
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Give a brief description');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"
                                               for="contactmail"><?php echo Install::translate('Contact Mail');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" name="general[core.contact_mail]" id="contactmail"
                                                   class="form-control">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Mail adress to contact you');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <h4 class="page-header"><?php echo Install::translate('Mail Settings');
    ?></h4>

                                    <div class="form-group">
                                        <label for="use-smtp"
                                               class="col-lg-3 control-label"><?php echo Install::translate('Mailserver');
    ?></label>

                                        <div class="col-lg-7">
                                            <div class="radio-inline">
                                                <label>
                                                    <input type="radio" id="connection-type-smtp" required
                                                           name="config[mail][smtp]"
                                                           class="change-connection-type"
                                                           checked="checked">&nbsp;<?php echo Install::translate('Use SMTP');
    ?>
                                                </label>
                                            </div>
                                            <div class="radio-inline">
                                                <label>
                                                    <input type="radio" id="connection-type-sendmail" required
                                                           name="config[mail][smtp]"
                                                           class="change-connection-type">&nbsp;<?php echo Install::translate('Use PHP Sendmail');
    ?>
                                                </label>
                                            </div>
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Use SMTP if your server does not support PHP Sendmail');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div id="smtp-settings">
                                        <div class="form-group">
                                            <label for="smtp-host"
                                                   class="col-lg-3 control-label"><?php echo Install::translate('SMTP-Host');
    ?></label>

                                            <div class="col-lg-7">
                                                <input type="text" class="form-control"
                                                       id="smtp-host" name="config[mail][params][host]" required>
                                            </div>
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                              title="<?php echo Install::translate('Hostname of SMTP Server');
    ?>"
                              data-placement="right"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="smtp-port"
                                                   class="col-lg-3 control-label"><?php echo Install::translate('SMTP-Port');
    ?></label>

                                            <div class="col-lg-7">
                                                <input type="number" class="form-control"
                                                       id="smtp-port" name="config[mail][params][port]" required
                                                       data-bv-greaterthan
                                                       data-bv-greaterthan-value="25">
                                            </div>
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                              title="<?php echo Install::translate('Port of SMTP Server, usually 25');
    ?>"
                              data-placement="right"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="smtp-auth"
                                                   class="col-lg-3 control-label"><?php echo Install::translate('SMTP-Authentication');
    ?></label>

                                            <div class="col-lg-7">
                                                <select class="form-control" id="smtp-auth"
                                                        name="config[mail][params][auth]" required>
                                                    <option
                                                        value="login"><?php echo Install::translate('Yes');
    ?></option>
                                                    <option
                                                        value="plain"><?php echo Install::translate('No');
    ?></option>
                                                </select>
                                            </div>
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                              title="<?php echo Install::translate('Is authentification required');
    ?>"
                              data-placement="right"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="smtp-username"
                                                   class="col-lg-3 control-label"><?php echo Install::translate('SMTP-Username');
    ?></label>

                                            <div class="col-lg-7">
                                                <input type="text" required class="form-control"
                                                       id="smtp-username"
                                                       name="config[mail][params][username]">
                                            </div>
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                              title="<?php echo Install::translate('SMTP username');
    ?>" data-placement="right"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="smtp-password"
                                                   class="col-lg-3 control-label"><?php echo Install::translate('SMTP-Password');
    ?></label>

                                            <div class="col-lg-7">
                                                <input type="password" required class="form-control"
                                                       id="smtp-password"
                                                       name="config[mail][params][password]">
                                            </div>
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                              title="<?php echo Install::translate('SMTP password');
    ?>" data-placement="right"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="smtp-ssl"
                                                   class="col-lg-3 control-label"><?php echo Install::translate('SMTP-Security');
    ?></label>

                                            <div class="col-lg-7">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="config[mail][params][ssl]"
                                                               value="ssl">&nbsp;<?php echo Install::translate('Use SSL');
    ?>
                                                    </label>
                                                </div>
                                            </div>
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                              title="<?php echo Install::translate('Use secure connection');
    ?>"
                              data-placement="right"></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="item" id="account">
                                <span class="title"><?php echo Install::translate('Create Admin-Account');
    ?></span>

                                <form class="form-horizontal" id="adminForm">
                                    <input type="hidden" name="type" value="ajax"/>
                                    <input type="hidden" name="action" value="prepareAdmin"/>

                                    <div class="form-group">
                                        <label class="control-label col-lg-3"
                                               for="input-username"><?php echo Install::translate('Username');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" autocomplete="off" required class="form-control"
                                                   id="input-username"
                                                   placeholder="<?php echo Install::translate('Username');
    ?>"
                                                   name="username">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Your username in Cunity');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3"
                                               for="input-email"><?php echo Install::translate('E-Mail');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="email" required class="form-control" id="input-email"
                                                   placeholder="<?php echo Install::translate('E-Mail');
    ?>"
                                                   name="email">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Your Mail adress');
    ?>" data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3"
                                               for="input-firstname"><?php echo Install::translate('Firstname');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" autocomplete="off" required class="form-control"
                                                   id="input-firstname"
                                                   placeholder="<?php echo Install::translate('Firstname');
    ?>"
                                                   name="firstname">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Firstname if you wish to provide');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3"
                                               for="input-lastname"><?php echo Install::translate('Lastname');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="text" autocomplete="off" required class="form-control"
                                                   id="input-lastname"
                                                   placeholder="<?php echo Install::translate('Lastname');
    ?>"
                                                   name="lastname">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Lastname if you wish to provide');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3"
                                               for="input-password"><?php echo Install::translate('Password');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="password" autocomplete="off" required class="form-control"
                                                   id="input-password"
                                                   placeholder="<?php echo Install::translate('Password');
    ?>"
                                                   name="password">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Your password');
    ?>" data-placement="right"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3"
                                               for="input-password-repeat"><?php echo Install::translate('Repeat password');
    ?></label>

                                        <div class="col-lg-7">
                                            <input type="password" autocomplete="off" required class="form-control"
                                                   id="input-password-repeat"
                                                   placeholder="<?php echo Install::translate('Repeat password');
    ?>"
                                                   name="password_repeat">
                                        </div>
                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip"
                          title="<?php echo Install::translate('Repeat your password');
    ?>"
                          data-placement="right"></span>
                                    </div>
                                </form>
                            </div>
                            <div class="item" id="finish">
                                <span class="title"><?php echo Install::translate('Finish Installation');
    ?></span>

                                <div class="terms">
                                    <form>
                                        <div class="form-group">
                                            <div
                                                class="form-control"><?php echo Install::translate('Congratuliations, you successfully installed your own Version of Cunity. If you want to change your settings, please login with your newly created user account and follow the link to your administration area.');
    ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-2 clearfix"><a role="button" href="#installCarousel"
                                                              id="installPrevButton"
                                                              data-slide="prev"
                                                              class="btn btn-default pull-left hidden"><i
                                        class="fa fa-chevron-left"></i>&nbsp;<?php echo Install::translate('Prev');
    ?>
                                </a></div>
                            <div class="col-lg-8">
                                <div class="progress">
                                    <div class="progress-bar" id="installation-progress" role="progressbar"
                                         aria-valuenow="0"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                        <span class="progress-status">0</span>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 clearfix"><a role="button" href="#installCarousel"
                                                              id="installNextButton"
                                                              data-slide="next" disabled
                                                              class="btn btn-primary pull-right"><?php echo Install::translate('Next');
    ?>
                                    &nbsp;<i class="fa fa-chevron-right"></i></a></div>
                            <div class="col-lg-2 clearfix"><a role="button" href=".." id="installFinishButton"
                                                              class="btn btn-success pull-right hidden"><i
                                        class="fa fa-check"></i>&nbsp;<?php echo Install::translate('Finish');
    ?>
                                </a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php

} ?>
    <footer>
        <small class="copyright">Powered by <a href="http://cunity.net/" target="_blank">Cunity</a></small>
    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="../lib/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script>
        var origShow = jQuery.fn.show, origHide = jQuery.fn.hide;
        jQuery.fn.show = function () {
            $(this).removeClass("hidden");
            return origShow.apply(this, arguments);
        };
        jQuery.fn.hide = function () {
            $(this).addClass("hidden");
            return origHide.apply(this, arguments);
        };
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            $('#installCarousel').carousel({
                interval: false,
                keyboard: false
            });
            $('#accept-terms').change(function () {
                if ($('#accept-terms').is(':checked')) {
                    $('#installNextButton').removeAttr('disabled');
                } else {
                    $('#installNextButton').attr('disabled', 'disabled');
                }
            });
            index = 1;

            $('#checkDatabase').click(function () {
                $.ajax({
                    type: "GET",
                    url: '<?php echo Server::get('PHP_SELF') ?>',
                    data: $('#databaseForm').serialize()
                }).done(function (data) {
                    data = $.parseJSON(data);
                    if (data.success) {
                        $('#databaseForm .has-feedback').removeClass('has-error').addClass('has-success');
                        $('#installNextButton').removeAttr('disabled');
                        $('.error-message').hide();
                        $('.success-message').show();
                    } else {
                        $('#databaseForm .has-feedback').removeClass('has-success').addClass('has-error');
                        $('#installNextButton').attr('disabled', 'disabled');

                        if (data.response == 'config') {
                            $('.error-message-config').show();
                        } else if (data.response == 'temp') {
                            $('.error-message-temp').show();
                        } else if (data.response == 'gdlib') {
                            $('.error-message-gdlib').show();
                        } else {
                            $('.error-message').hide();
                        }
                    }
                });

                return false;
            });

            $('#connection-type-sendmail').click(function () {
                $('#smtp-settings').hide();
            });

            $('#connection-type-smtp').click(function () {
                $('#smtp-settings').show();
            });

            $('#installNextButton').click(function () {
                var formId = '';

                if (index == 3) {
                    formId = 'configForm';
                }
                else if (index == 4) {
                    formId = 'adminForm';
                }

                $.ajax({
                    type: "GET",
                    url: '<?php echo Server::get('PHP_SELF') ?>',
                    data: $('#' + formId).serialize()
                });
            });

            $('#installCarousel').off('keydown.bs.carousel');
            $('#installCarousel').on('slide.bs.carousel', function (e) {
                var c = $(this).data('bs.carousel');
                var oldIndex = index;
                index = $("#installCarousel .item").index($(e.relatedTarget)) + 1;

                $("#steps > li.active").removeClass("active");
                $("#steps > li:eq(" + index + ")").addClass("active");
                var percentage = (100 / ($("#steps > li").length - 1)) * index;
                if (percentage == 100) {
                    $("#installation-progress").addClass("progress-bar-success");
                }
                else {
                    $("#installation-progress").removeClass("progress-bar-success");
                }
                $("#installation-progress").width(percentage + "%").attr("aria-valuenow", percentage).children(".progress-status").text(Math.round(percentage));
                $("#installNextButton, #installPrevButton, #installFinishButton").hide();

                if (index > 1) {
                    $("#installPrevButton").show();
                }
                if (index < $("#steps > li").length - 1) {
                    $("#installNextButton").show();
                }
                if (index == $("#steps > li").length - 1) {
                    $("#installFinishButton").show();
                }

                if (index > oldIndex && index < 3) {
                    $('#installNextButton').attr('disabled', 'disabled');
                } else {
                    $('#installNextButton').removeAttr('disabled');
                }
            });
        });
    </script>
    </body>
    </html>

<?php

ob_end_flush();
