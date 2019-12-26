<?php

class frame_view {

    var $css;
    var $js;
    var $menu;

    function __construct($req = null) {
        global $_DB;
        global $config;
        $this->dbstatus = !is_string($_DB->conn)?true:false;
        $this->model = new frame_model();
        $this->sys_clients_model = new sys_clients_model();
        $this->login_model = new login_model();
        $this->utils = new utils();
        //Plugins base del frame
        //$this->css = ['adminlte', 'icons', 'validator'];
        //$this->js = ['jquery', 'jquery-cookie', 'adminlte', 'sweetalert', 'validator', '/js/frame.js'];

        $this->css = ['icons', 'adminTheme', 'sweetalert', 'bootstrap-select', 'validator'];
        $this->js = [];
        if ($config->socket->enabled) {
            $this->js[] = "{$config->socket->public}/socket.io/socket.io.js";
        }
        $this->js = array_merge($this->js, [$this->jsdbstatus(), 'adminTheme', 'jquery-cookie', 'sweetalert', 'validator', 'bootstrap-select', '/js/frame.js']);
        
    }

    function jsdbstatus() {
        global $config;
        ob_start(); ?>
        <script>
        var dbstatus = <?=$this->dbstatus?'true':'false'?>;
        var socket_enabled = <?=$config->socket->enabled?'true':'false'?>;
        var socket_address = '<?=$config->socket->public?>';
        </script>
        <?php return ob_get_clean();
    }

    /**
     * returns full frame html
     * 
     * @param string $body
     */
    function main($data = null) {
        $default = [
            'menu' => '',
            'css' => [],
            'js' => [],
            'concatPlugins' => false,
            'cboClient' => true,
            'body' => [
                'title' => '',
                'subtitle' => '',
                'html' => ''
            ]
        ];
        if (!$data) {
            $data = [];
        }
        $data = array_replace($default, $data);
        
        extract($data);
        
        $this->menu = $menu;
?>

    <!DOCTYPE html>
    <html class="loading" lang="en" data-textdirection="ltr" style="display: none;">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
            <meta name="description" content="datview.cl">
            <meta name="keywords" content="datview.cl">
            <meta name="author" content="datview.cl">
            <title>DatCapital</title>
            <link rel="apple-touch-icon" href="<?=public_url?>/app-assets/images/ico/apple-icon-120.png">
            <link rel="shortcut icon" type="image/x-icon" href="<?=public_url?>/app-assets/images/ico/favicon.ico">
            <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet">
            <?php echo $this->utils->pluginLoader(array_merge($this->css, $css), 'css', $concatPlugins); ?>
        </head>
        <body class="vertical-layout vertical-menu 2-columns   fixed-navbar" data-open="click" data-menu="vertical-menu" data-col="2-columns">
            <?= $this->head($data)?>
            <?=$this->menu()?>
            <?=$this->body($data)?>
            <div class="sidenav-overlay"></div>
            <div class="drag-target"></div>
            <?=$this->footer()?>
            <!--
            <script type="text/javascript" src="http://dev.suwod.com/core/js/forms/forms.js"></script>
            <link rel="stylesheet" href="http://dev.suwod.com/core/js/forms/css/forms.css" type="text/css">
            <script type="text/javascript" src="http://dev.suwod.com/core/js/jquery.base64.js"></script>
            -->
            <?php echo $this->utils->pluginLoader(array_merge($this->js, $js), 'js', $concatPlugins); ?>
            <script>
                $(document).ready(function() {
                    $(window).trigger("load");
                });
            </script>
        </body>
    </html>

<?php
    }

    function head($data) { 
        extract($data);
        if ($cboClient) {
            $clients = $this->sys_clients_model->get();
        }

        $userData = $this->login_model->getTokenData($_COOKIE['token']);
        if (is_object($userData)) {
            $userData->avatar = 'img/user.png';
        } else if ($userData == 'admin') {
            $userData = (object)[
                'nombre' => 'Hackerman',
                'avatar' => 'img/hackerman.jpg'
            ];
        }
        
?>
        <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-dark navbar-border navbar-shadow bg-cyan">
            <div class="navbar-wrapper">
                <div class="navbar-header">
                    <ul class="nav navbar-nav flex-row">
                        <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu font-large-1"></i></a></li>
                        <li class="nav-item">
                            <a class="navbar-brand" href="<?=public_url?>">
                                <img class="brand-logo" alt="logo" src="<?=public_url?>/app-assets/images/logo/logo.png">
                                <img class="brand-text" alt="logo" src="<?=public_url?>/app-assets/images/logo/logo_frame.png">
                            </a>
                        </li>
                        <li class="nav-item d-md-none"><a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i class="la la-ellipsis-v"></i></a></li>
                    </ul>
                </div>
                <div class="navbar-container content">
                    <div class="collapse navbar-collapse" id="navbar-mobile">
                        <ul class="nav navbar-nav mr-auto float-left">
                            <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu"></i></a></li>
                            <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand" href="#"><i class="ficon ft-maximize"></i></a></li>
                        </ul>
                        
                        <?php if($this->dbstatus && $cboClient): ?>
                        <div style="padding: 8px; width: 200px;">    
                            <select id="client-selector" class="form-control">
                                <option value="">Seleccione cliente...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?=$client['client_id']?>" data-subtext="<?=$client['db_name']?>"><?=$client['label']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <ul class="nav navbar-nav float-right">
                            <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon ft-bell"></i><span class="badge badge-pill badge-danger badge-up badge-glow">5</span></a>
                                <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                                    <li class="dropdown-menu-header">
                                        <h6 class="dropdown-header m-0"><span class="grey darken-2">Notifications</span></h6><span class="notification-tag badge badge-danger float-right m-0">5 New</span>
                                    </li>
                                    <li class="scrollable-container media-list w-100"><a href="javascript:void(0)">
                                            <div class="media">
                                                <div class="media-left align-self-center"><i class="ft-plus-square icon-bg-circle bg-cyan"></i></div>
                                                <div class="media-body">
                                                    <h6 class="media-heading">You have new order!</h6>
                                                    <p class="notification-text font-small-3 text-muted">Lorem ipsum dolor sit amet, consectetuer elit.</p><small>
                                                        <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">30 minutes ago</time></small>
                                                </div>
                                            </div>
                                        </a><a href="javascript:void(0)">
                                            <div class="media">
                                                <div class="media-left align-self-center"><i class="ft-download-cloud icon-bg-circle bg-red bg-darken-1"></i></div>
                                                <div class="media-body">
                                                    <h6 class="media-heading red darken-1">99% Server load</h6>
                                                    <p class="notification-text font-small-3 text-muted">Aliquam tincidunt mauris eu risus.</p><small>
                                                        <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Five hour ago</time></small>
                                                </div>
                                            </div>
                                        </a><a href="javascript:void(0)">
                                            <div class="media">
                                                <div class="media-left align-self-center"><i class="ft-alert-triangle icon-bg-circle bg-yellow bg-darken-3"></i></div>
                                                <div class="media-body">
                                                    <h6 class="media-heading yellow darken-3">Warning notifixation</h6>
                                                    <p class="notification-text font-small-3 text-muted">Vestibulum auctor dapibus neque.</p><small>
                                                        <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Today</time></small>
                                                </div>
                                            </div>
                                        </a><a href="javascript:void(0)">
                                            <div class="media">
                                                <div class="media-left align-self-center"><i class="ft-check-circle icon-bg-circle bg-cyan"></i></div>
                                                <div class="media-body">
                                                    <h6 class="media-heading">Complete the task</h6><small>
                                                        <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last week</time></small>
                                                </div>
                                            </div>
                                        </a><a href="javascript:void(0)">
                                            <div class="media">
                                                <div class="media-left align-self-center"><i class="ft-file icon-bg-circle bg-teal"></i></div>
                                                <div class="media-body">
                                                    <h6 class="media-heading">Generate monthly report</h6><small>
                                                        <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last month</time></small>
                                                </div>
                                            </div>
                                        </a></li>
                                    <li class="dropdown-menu-footer"><a class="dropdown-item text-muted text-center" href="javascript:void(0)">Read all notifications</a></li>
                                </ul>
                            </li>
                            
                            <li class="dropdown dropdown-user nav-item">
                                <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown"><span class="mr-1 user-name text-bold-700"><?=$userData->nombre?></span><span class="avatar avatar-online"><img src="<?=public_url?>/<?=$userData->avatar?>" alt="avatar"><i></i></span></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="<?=public_url?>/perfil"><i class="fa fa-user-edit"></i> Mi perfil</a>
                                    <div class="dropdown-divider"></div><a class="dropdown-item" href="<?=public_url?>/login/logout"><i class="ft-power"></i> Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
<?php
    }

    function footer() { ?>
        <!-- <footer class="footer footer-static footer-light navbar-border navbar-shadow">
            <p class="clearfix blue-grey lighten-2 text-sm-center mb-0 px-2">
                <span class="float-md-left d-block d-md-inline-block">Copyright © <?=date("Y")?> <a href="http://www.datview.cl/">Datview</a>.</span>
                <span class="float-md-right d-none d-lg-block">Todos los derechos reservados.</span>
                <span id="scroll-top"></span>
            </p>
        </footer> -->

<?php
    }

    function body($data = null) { 
        $default = [
            'body' => [
                'icon' => '',
                'title' => '',
                'subtitle' => '',
                'html' => ''
            ]
        ];
        if (!$data) {
            $data = [];
        }
        $data = array_replace_recursive($default, $data);
        extract($data);
        ?>
        <div class="app-content content">
            <div class="content-wrapper">
                <div class="content-header row mb-1">
                    <div class="content-header-left col-md-6 col-12">
                        <h3 class="content-header-title text-bold-700 font-large-1"><i class="<?=$body['icon']?>" aria-hidden="true"></i> <?=$body['title']?></h3>
                    </div>
                </div>
                <div class="content-body">
                    <div class="row">			
                        <div class="col-lg-12">				
                            <?=$body['html']?>				
                        </div>		
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    function menu($data = null) { 
        global $config;
        if (!$data) {
            $data = [
                'menu' => ''
            ];
        }
        extract($data);
        $sysres = array_map(function($row) {
            return (array)$row;
        }, $config->sysres);

        $userData = $this->login_model->getTokenData($_COOKIE['token']);
        if (is_object($userData)) {
            $frameResources = [];
            $userResources = $this->model->buildTree($this->model->getResources($userData));
        } else if ($userData == 'admin') {
            $frameResources = $this->model->buildTree($sysres);
            $userResources = $this->model->buildTree($this->model->getResources($userData));
        }

?>
    <div class="main-menu menu-fixed menu-dark menu-shadow menu-accordion" data-scroll-to-active="true">
        <div class="main-menu-content">
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

                <?php if ($frameResources): ?>
                    <li class="navigation-header">
                        <span data-i18n="nav.category.general">FRAME</span>
                        <i class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right" data-original-title="Opciones de Frame"></i>
                    </li>
                    <?=$this->menuItems($frameResources)?>
                <?php endif; ?>

                <?php if ($userResources): ?>
                    <li class="navigation-header">
                        <span data-i18n="nav.category.general">NAVEGACIÓN</span>
                        <i class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right" data-original-title="Opciones de Navegación"></i>
                    </li>
                    <?=$this->menuItems($userResources)?>
                    <pre style="font-size: 5pt; font-weight: bold; display: none;"><?=json_encode($userResources, JSON_PRETTY_PRINT)?></pre>
                <?php endif; ?>

            </ul>
        </div>
    </div>
<?php
    }

    function menuItems(array $elements, $level = 1) {
        ?>

        <?php foreach($elements as $element): $printFlag = $this->model->isThereAnyChildActive($element); ?>
            
            <?php if ($printFlag): ?>
            <li class="nav-item <?php if($this->model->isChildActive($element, $this->menu)): ?>active<?php endif; ?> <?php if (isset($element['children'])): ?><?php endif; ?>">
                
                <a class="menu-item" href="<?php if (isset($element['children'])): ?>#<?php else: echo base_url."/".$element['funcion']; endif; ?>">
                    <i class="<?=$element['icono']?>"></i>
                    <?php if (isset($element['children'])): ?>
                        <span class="menu-title" data-i18n="nav.dash.main"><?=$element['texto']?></span>
                    <?php else:?>
                        <?php if ($level == 1): ?>
                            <span class="menu-title" data-i18n="nav.dash.main"><?=$element['texto']?></span>
                        <?php else:?>
                            <span data-i18n="nav.dash.ecommerce"><?=$element['texto']?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </a>

                <?php if (isset($element['children'])): ?>
                    <ul class="menu-content">
                        <?=$this->menuItems($element['children'], $level + 1)?>
                    </ul>
                <?php endif; ?>

            </li>
            <?php endif; ?>
            

        <?php endforeach; ?>
        
        <?php
    }

    function errorpage($code, $text) {
        ?>
<html class="loaded" lang="en" data-textdirection="ltr"><!-- BEGIN: Head-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Modern admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities with bitcoin dashboard.">
    <meta name="keywords" content="admin template, modern admin template, dashboard template, flat admin template, responsive admin template, web app, crypto dashboard, bitcoin dashboard">
    <meta name="author" content="PIXINVENT">
    <title>Error <?=$code?></title>
    <link rel="apple-touch-icon" href="<?=public_url?>/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?=public_url?>/app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/vendors/css/vendors.min.css">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/colors.min.css">
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/components.min.css">
    <!-- END: Theme CSS-->

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/core/menu/menu-types/vertical-menu-modern.css">
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/core/colors/palette-gradient.min.css">
    <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/pages/error.min.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <!-- <link rel="stylesheet" type="text/css" href="<?=public_url?>/assets/css/style.css"> -->
    <!-- END: Custom CSS-->

  </head>
  <!-- END: Head-->

  <!-- BEGIN: Body-->
  <body class="vertical-layout vertical-menu-modern 1-column blank-page pace-done menu-expanded" data-open="click" data-menu="vertical-menu-modern" data-col="1-column"><div class="pace  pace-inactive"><div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
  <div class="pace-progress-inner"></div>
</div>
<div class="pace-activity"></div></div>
    <!-- BEGIN: Content-->
    <div class="app-content content">
      <div class="content-wrapper">
        <div class="content-header row mb-1">
        </div>
        <div class="content-body"><section class="flexbox-container">
    <div class="col-12 d-flex align-items-center justify-content-center">
        <div class="col-lg-4 col-md-8 col-10 p-0">
            <div class="card-header bg-transparent border-0">
                <h2 class="error-code text-center mb-2"><?=$code?></h2>
                <h3 class="text-uppercase text-center"><?=$text?></h3>
            </div>
            <div class="card-content">
                <!-- <fieldset class="row py-2">
                    <div class="input-group col-12">
                        <input type="text" class="form-control border-grey border-lighten-1 " placeholder="Search..." aria-describedby="button-addon2">
                        <span class="input-group-append" id="button-addon2">
                           <button class="btn btn-secondary border-grey border-lighten-1" type="button"><i class="ft-search"></i></button>
                       </span>
                   </div>
                </fieldset> -->
                <div class="row py-2">
                    <div class="col-12 col-sm-12 col-md-12 mb-1">
                        <a href="/" class="btn btn-primary btn-block"><i class="ft-home"></i> Inicio</a>
                    </div>
                    <!-- <div class="col-12 col-sm-6 col-md-6 mb-1">
                        <a href="search-website.html" class="btn btn-danger btn-block"><i class="ft-search"></i>  Search</a>
                    </div> -->
                </div>
            </div>
            <!-- <div class="card-footer bg-transparent">
                <div class="row">
                    <p class="text-muted text-center col-12 pb-1">© <span class="year">2019</span> <a href="#">Modern </a>Crafted with <i class="ft-heart pink"> </i> by <a href="http://themeforest.net/user/pixinvent/portfolio" target="_blank">PIXINVENT</a></p>
                    <div class="col-12 text-center">
                        <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-facebook"><span class="la la-facebook"></span></a>
                        <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-twitter"><span class="la la-twitter"></span></a>
                        <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-linkedin"><span class="la la-linkedin font-medium-4"></span></a>
                        <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-github"><span class="la la-github font-medium-4"></span></a>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</section>

        </div>
      </div>
    </div>
    <!-- END: Content-->


    <!-- BEGIN: Vendor JS-->
    <script src="<?=public_url?>/app-assets/vendors/js/vendors.min.js"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="<?=public_url?>/app-assets/vendors/js/forms/validation/jqBootstrapValidation.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="<?=public_url?>/app-assets/js/core/app-menu.min.js"></script>
    <script src="<?=public_url?>/app-assets/js/core/app.min.js"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src="<?=public_url?>/app-assets/js/scripts/forms/form-login-register.min.js"></script>
    <!-- END: Page JS-->

  
  <!-- END: Body-->
</body>
</html>
        <?php
    }

    
}