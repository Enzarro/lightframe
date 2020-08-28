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

        $this->css = ['icons', 'adminlte', 'sweetalert', 'bootstrap-select', 'validator','frame','select-picker', 'dropify'];
        $this->js = [];
        if ($config->socket->enabled) {
            $this->js[] = "{$config->socket->public}/socket.io/socket.io.js";
        }
        $this->js = array_merge($this->js, [$this->jsdbstatus(), 'jquery', 'jquery-cookie', 'adminlte', 'sweetalert', 'validator', 'bootstrap-select', 'select-picker', 'dropify', '/js/frame.js']);
        
    }

    function jsdbstatus() {
        global $config;
        ob_start(); ?>
        <script>
        var dbstatus = <?=$this->dbstatus?'true':'false'?>;
        var socket_enabled = <?=$config->socket->enabled?'true':'false'?>;
        var socket_address = '<?=$config->socket->public?>';
        var socket_system = '<?=$config->socket->system?>';
        var socket_timeout = '<?= isset($config->socket->timeout)?$config->socket->timeout:5?>';
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
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>LightFrame</title>
            <!-- Tell the browser to be responsive to screen width -->
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

            <?php echo $this->utils->pluginLoader(array_merge($this->css, $css), 'css', $concatPlugins); ?>

            <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->

            <!-- Google Font -->
            <link rel="stylesheet"
                    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        </head>
        <body class="hold-transition skin-blue sidebar-mini">
            <div class="wrapper">

            <?=$this->head($data)?>
            <?=$this->menu()?>
            <?=$this->body($data)?>
            <?=$this->footer()?>

            </div>
            <!-- ./wrapper -->
            <?php echo $this->utils->pluginLoader(array_merge($this->js, $js), 'js', $concatPlugins); ?>
            <style>
            .swal2-popup {
                font-size: 1.4rem !important;
            }
            </style>
        </body>
    </html>

<?php
    }

    function head($data) { 
        extract($data);
        if ($cboClient) {
            $clients = $this->sys_clients_model->get();
        }

        ?>
        <header class="main-header">
        <!-- Logo -->
        <a href="index2.html" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>L</b>F</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>Light</b>Frame</span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
            <!-- Messages: style can be found in dropdown.less-->
            <li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-envelope-o"></i>
                <span class="label label-success">4</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="header">You have 4 messages</li>
                    <li>
                        <!-- inner menu: contains the actual data -->
                        <ul class="menu">
                        <li><!-- start message -->
                            <a href="#">
                            <div class="pull-left">
                                <img src="<?=public_url?>/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                            </div>
                            <h4>
                                Support Team
                                <small><i class="fa fa-clock-o"></i> 5 mins</small>
                            </h4>
                            <p>Why not buy a new awesome theme?</p>
                            </a>
                        </li>
                        <!-- end message -->
                        <li>
                            <a href="#">
                            <div class="pull-left">
                                <img src="<?=public_url?>/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                            </div>
                            <h4>
                                AdminLTE Design Team
                                <small><i class="fa fa-clock-o"></i> 2 hours</small>
                            </h4>
                            <p>Why not buy a new awesome theme?</p>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                            <div class="pull-left">
                                <img src="<?=public_url?>/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                            </div>
                            <h4>
                                Developers
                                <small><i class="fa fa-clock-o"></i> Today</small>
                            </h4>
                            <p>Why not buy a new awesome theme?</p>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                            <div class="pull-left">
                                <img src="<?=public_url?>/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                            </div>
                            <h4>
                                Sales Department
                                <small><i class="fa fa-clock-o"></i> Yesterday</small>
                            </h4>
                            <p>Why not buy a new awesome theme?</p>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                            <div class="pull-left">
                                <img src="<?=public_url?>/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                            </div>
                            <h4>
                                Reviewers
                                <small><i class="fa fa-clock-o"></i> 2 days</small>
                            </h4>
                            <p>Why not buy a new awesome theme?</p>
                            </a>
                        </li>
                        </ul>
                    </li>
                    <li class="footer"><a href="#">See All Messages</a></li>
                </ul>
            </li>
            <!-- Notifications: style can be found in dropdown.less -->
            <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-bell-o"></i>
                <span class="label label-warning">10</span>
                </a>
                <ul class="dropdown-menu">
                <li class="header">You have 10 notifications</li>
                <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                    <li>
                        <a href="#">
                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                        </a>
                    </li>
                    <li>
                        <a href="#">
                        <i class="fa fa-warning text-yellow"></i> Very long description here that may not fit into the
                        page and may cause design problems
                        </a>
                    </li>
                    <li>
                        <a href="#">
                        <i class="fa fa-users text-red"></i> 5 new members joined
                        </a>
                    </li>
                    <li>
                        <a href="#">
                        <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                        </a>
                    </li>
                    <li>
                        <a href="#">
                        <i class="fa fa-user text-red"></i> You changed your username
                        </a>
                    </li>
                    </ul>
                </li>
                <li class="footer"><a href="#">View all</a></li>
                </ul>
            </li>
            <!-- Tasks: style can be found in dropdown.less -->
            <li class="dropdown tasks-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-flag-o"></i>
                <span class="label label-danger">9</span>
                </a>
                <ul class="dropdown-menu">
                <li class="header">You have 9 tasks</li>
                <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                    <li><!-- Task item -->
                        <a href="#">
                        <h3>
                            Design some buttons
                            <small class="pull-right">20%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar"
                                aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">20% Complete</span>
                            </div>
                        </div>
                        </a>
                    </li>
                    <!-- end task item -->
                    <li><!-- Task item -->
                        <a href="#">
                        <h3>
                            Create a nice theme
                            <small class="pull-right">40%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar"
                                aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">40% Complete</span>
                            </div>
                        </div>
                        </a>
                    </li>
                    <!-- end task item -->
                    <li><!-- Task item -->
                        <a href="#">
                        <h3>
                            Some task I need to do
                            <small class="pull-right">60%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar"
                                aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">60% Complete</span>
                            </div>
                        </div>
                        </a>
                    </li>
                    <!-- end task item -->
                    <li><!-- Task item -->
                        <a href="#">
                        <h3>
                            Make beautiful transitions
                            <small class="pull-right">80%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar"
                                aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">80% Complete</span>
                            </div>
                        </div>
                        </a>
                    </li>
                    <!-- end task item -->
                    </ul>
                </li>
                <li class="footer">
                    <a href="#">View all tasks</a>
                </li>
                </ul>
            </li>
            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="<?=public_url?>/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
                <span class="hidden-xs">Hackerman</span>
                </a>
                <ul class="dropdown-menu">
                <!-- User image -->
                <li class="user-header">
                    <img src="<?=public_url?>/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">

                    <p>
                    Hackerman - Web Developer
                    <small>Member since Nov. 2012</small>
                    </p>
                </li>
                <!-- Menu Body -->
                <li class="user-body">
                    <div class="row">
                    <div class="col-xs-4 text-center">
                        <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                        <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                        <a href="#">Friends</a>
                    </div>
                    </div>
                    <!-- /.row -->
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <div class="pull-left">
                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                    <a href="<?=base_url?>/login/logout" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                </li>
                </ul>
            </li>
            <!-- Control Sidebar Toggle Button -->
            <!-- <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
            </li> -->
            </ul>
        </div>

        </nav>
    </header>
<?php
    }

    function footer() { ?>
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
            <b>Version</b> 1.0
            </div>
            <strong>Copyright &copy; <?=date("Y")?> <a href="http://www.datview.cl/">Datview</a>.</strong> Todos los derechos reservados.
        </footer>

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
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?=$body['title']?>
                    <small><?=$body['subtitle']?></small>
                </h1>
                <!-- <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="#">Tables</a></li>
                    <li class="active">Data tables</li>
                </ol> -->
            </section>

            <!-- Main content -->
            <section class="content">
                <?=$body['html']?>
            </section>
            <!-- /.content -->
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
        <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?=public_url?>/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Hackerman</p>
          <a href="#"><i class="fa fa-circle text-success"></i> En línea</a>
        </div>
      </div>

      <ul class="sidebar-menu" data-widget="tree" data-animation-speed="250">
        <?php if ($frameResources): ?>
        <li class="header">FRAME</li>
        <?=$this->menuItems($frameResources)?>
        <?php endif; ?>
        <?php if ($userResources): ?>
        <li class="header">NAVEGACIÓN</li>
        <?=$this->menuItems($userResources)?>
        <pre style="font-size: 5pt; font-weight: bold; display: none;"><?=json_encode($userResources, JSON_PRETTY_PRINT)?></pre>
        <?php endif; ?>

      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
<?php
    }

    function menuItems(array $elements, $level = 1) {
        ?>

        <?php foreach($elements as $element): 
            
            $printFlag = $this->model->isThereAnyChildActive($element); 
            
            ?>
            
            <?php if ($printFlag): ?>
            <li class="<?php if($this->model->isChildActive($element, $this->menu)): ?>active<?php endif; ?> <?php if (isset($element['children'])): ?>treeview<?php endif; ?>">
                <a href="<?php if (isset($element['children'])): ?>#<?php else: echo base_url."/".$element['funcion']; endif; ?>">
                    <i class="<?=$element['icono']?>"></i>
                    <span><?=$element['texto']?></span>
                    <?php if (isset($element['children'])): ?><span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span><?php endif; ?>
                </a>
                <?php if (isset($element['children'])): ?><ul class="treeview-menu">
                    <?=$this->menuItems($element['children'], $level + 1)?>
                </ul><?php endif; ?>
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