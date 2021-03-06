<!DOCTYPE>
<html>
<head>
    <meta charset="utf-8">
    <title>{-$meta.title}&nbsp;|&nbsp;{-"core.sitename"|setting}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link type="text/css" href="{-"core.siteurl"|setting}style/CunityRefreshed/css/style.css" rel="stylesheet"
          media="screen and (min-width:1024px)">
    <link rel="stylesheet" type="text/css" href="{-"core.siteurl"|setting}lib/modules/Start/styles/css/style.css"
          media="screen and (min-width: 1024px)">
    <link rel="stylesheet" type="text/css" href="{-"core.siteurl"|setting}lib/modules/Start/styles/css/style-mobile.css"
          media="screen and (max-width: 1023px)">
    <link href="{-"core.siteurl"|setting}lib/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{-"core.siteurl"|setting}lib/plugins/fontawesome/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
          href="{-"core.siteurl"|setting}lib/modules/Core/styles/css/cunity.min.css.php?files={-$css_head}">
    <link href="{-"core.siteurl"|setting}lib/plugins/select2/select2.css" rel="stylesheet">
    <link href="{-"core.siteurl"|setting}lib/plugins/select2/select2-bootstrap.css" rel="stylesheet">
    <script src="{-"core.siteurl"|setting}lib/plugins/js/jquery.min.js" type="text/javascript"></script>
    <script src="{-"core.siteurl"|setting}lib/modules/Core/styles/javascript/cunity-core.js"
            type="text/javascript"></script>
    <script src="{-"core.siteurl"|setting}lib/modules/Register/styles/javascript/registration.js"
            type="text/javascript"></script>
    <script type="text/javascript">var modrewrite = {-$modrewrite}, siteurl = "{-"core.siteurl"|setting}", design = "CunityRefreshed", login = {-if empty($user)}false{-else}true{-/if};</script>

    <script src="{-"core.siteurl"|setting}lib/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="{-"core.siteurl"|setting}lib/plugins/js/html5shiv.min.js"></script>
    <script src="{-"core.siteurl"|setting}lib/plugins/js/respond.min.js"></script>
    <script src="{-"core.siteurl"|setting}lib/plugins/select2/select2.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('select').select2();
        });
    </script>
    {-$script_head}
</head>
<body>
<header class="head">
    <div class="content">
        <div class="headline pull-left"><a
                    href="{-"core.siteurl"|setting}">{-"core.headline"|setting|html_entity_decode}</a></div>
        <form class="login-form form-inline pull-right" action="{-"index.php?m=register&action=login"|URL}"
              method="post">
            <div class="form-group">
                <input type="text" name="email" placeholder="E-Mail" class="form-control" tabindex="1" id="loginemail">
                <label class="checkbox"><input type="checkbox" name="save-login"> {-"Remember Me"|translate}</label>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="{-"Password"|translate}" class="form-control"
                       tabindex="2">
                <a href="{-"index.php?m=register&action=forgetPw"|URL}">{-"I forgot my password"|translate}</a>
            </div>
            <div class="form-group" style="width:65px">
                <input type="submit" class="btn btn-primary" value="{-"Log in"|translate}">
            </div>
        </form>
    </div>
</header>
<div class="main-start">
    <div class="main-start-container">
        <div class="start-page-content">
            <img src="{-"core.siteurl"|setting}style/CunityRefreshed/img/startpage.jpg"/>

            <div class="start-page-content-caption top">
                {-"core.startpageheader"|setting|html_entity_decode}
            </div>
        </div>
        <div class="registration-start">
            {-include file="Register/styles/registration.tpl"}
        </div>
    </div>
</div>
<div class="login-buttons clearfix">
    <a href="{-"index.php?m=register"|URL}" class="btn btn-primary pull-left btn-large">{-"Register now!"|translate}</a>
    {-*<button class="btn btn-default pull-right btn-large info-button" data-toggle="dropdown" data-href="infomenu"><i*}
    {-*class="fa fa-info"></i></button>*}
    <button class="btn btn-default pull-right btn-large" data-toggle="modal"
            data-target="#loginModal">{-"Login"|translate}</button>
    <ul class="dropdown-menu" role="menu" aria-labelledby="infomenu" id="infomenu">
        <li><a href="http://www.cunity.net">&reg; 2014 - Cunity</a></li>
        <li class="divider"></li>
        <li><a href="{-"index.php?m=pages&action=legalnotice"|URL}">{-"Legal-Notice"|translate}</a></li>
        <li><a href="{-"index.php?m=pages&action=privacy"|URL}">{-"Privacy"|translate}</a></li>
        <li><a href="{-"index.php?m=pages&action=terms"|URL}">{-"Terms and Conditions"|translate}</a></li>
        <li><a href="{-"index.php?m=contact"|URL}">{-"Contact"|translate}</a></li>
    </ul>
</div>
<footer class="footer">
    <ul class="footer-menu-start list-inline list-unstyled">
        <li><a href="{-"index.php?m=pages&action=legalnotice"|URL}">{-"Legal-Notice"|translate}</a></li>
        <li>|</li>
        <li><a href="{-"index.php?m=pages&action=privacy"|URL}">{-"Privacy"|translate}</a></li>
        <li>|</li>
        <li><a href="{-"index.php?m=pages&action=terms"|URL}">{-"Terms and Conditions"|translate}</a></li>
        <li>|</li>
        <li><a href="{-"index.php?m=contact"|URL}">{-"Contact"|translate}</a></li>
        <li class="pull-right copyright-start">Powered by <a href="http://cunity.net/" target="_blank">Cunity</a>
        </li>
    </ul>
</footer>
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Login</h4>
            </div>
            <div class="modal-body">
                <form class="login-form form-horizontal" action="{-"index.php?m=register&action=login"|URL}"
                      method="post" style="margin:10px;">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="E-Mail" class="form-control" tabindex="1">
                    </div>
                    <div class="form-group clearfix">
                        <input type="password" name="password" placeholder="{-"Password"|translate}"
                               class="form-control" tabindex="2">
                        <label class="checkbox pull-left"><input type="checkbox"
                                                                 name="save-login"> {-"Remember Me"|translate}</label>
                        <a href="{-"index.php?m=register&action=forgetPw"|URL}" class="pull-right"
                           style="padding-top:7px">{-"I forgot my password"|translate}</a>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary btn-block" value="{-"Log in"|translate}">
                    </div>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
</body>
</html>