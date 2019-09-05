<?php

class frame_view {

    var $css;
    var $js;

    function __construct($req = null) {
        $this->utils = new utils();
        //Plugins base del frame
        $this->css = ['adminlte', 'icons'];
        $this->js = ['jquery', 'adminlte'];
    }

    /**
     * returns full frame html
     * 
     * @param string $body
     */
    function main($data = null) {
        if (!$data) {
            $data = [
                'css' => [],
                'js' => [],
                'body' => [
                    'title' => '',
                    'subtitle' => '',
                    'html' => ''
                ]
            ];
        }
        extract($data);
        
?>
        <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>DatCapital | Lightframe</title>
            <!-- Tell the browser to be responsive to screen width -->
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

            <?php echo $this->utils->pluginLoader(array_merge($this->css, $css), 'css'); ?>

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

            <?=$this->head()?>
            <?=$this->menu()?>
            <?=$this->body($data)?>
            <?=$this->footer()?>

            </div>
            <!-- ./wrapper -->
            <?php echo $this->utils->pluginLoader(array_merge($this->js, $js), 'js'); ?>
        </body>
    </html>

<?php
    }

    function head() { ?>
        <header class="main-header">
            <!-- Logo -->
            <a href="index2.html" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>D</b>C</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>DAT</b>Capital</span>
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
                    <span class="hidden-xs">Alexander Pierce</span>
                    </a>
                    <ul class="dropdown-menu">
                    <!-- User image -->
                    <li class="user-header">
                        <img src="<?=public_url?>/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">

                        <p>
                        Alexander Pierce - Web Developer
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
        if (!$data) {
            $data = [
                'body' => [
                    'title' => '',
                    'subtitle' => '',
                    'html' => ''
                ]
            ];
        }
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
                'resources' => []
            ];
        }
        extract($data);
        $sysres = array_map(function($row) {
            return (array)$row;
        }, $config->sysres);
        $resources = $this->buildTree(array_merge($sysres, $resources));
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
          <p>Alexander Pierce</p>
          <a href="#"><i class="fa fa-circle text-success"></i> En línea</a>
        </div>
      </div>

      <ul class="sidebar-menu" data-widget="tree" data-animation-speed="250">
        <li class="header">NAVEGACIÓN</li>
        <pre style="display: none;"><?=json_encode($resources, JSON_PRETTY_PRINT)?></pre>
        <?=$this->menuItems($resources)?>

        <!-- <li class="treeview">
          <a href="#">
            <i class="fa fa-share"></i> <span>Multilevel</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
            <li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i> Level One
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> Level Two</a></li>
                <li class="treeview">
                  <a href="#"><i class="fa fa-circle-o"></i> Level Two
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
          </ul>
        </li> -->

      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
<?php
    }

    function menuItems(array $elements, $level = 1) {
        ?>

        <?php foreach($elements as $element): ?>
        <li <?php if (isset($element['children'])): ?>class="treeview"<?php endif; ?>>
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
        <?php endforeach; ?>
        
        <?php
    }

    function buildTree(array $elements, $parentId = null) {
		$branch = array();

		foreach ($elements as $element) {
			if ($element['parent_id'] == $parentId) {
				$children = $this->buildTree($elements, $element['id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}

		return $branch;
	}

    function liSideMenuTreeArray(array $elements, $level = 1) {
		ob_start();
		if ($level != 1) {
			?><ul class="nav nav-<?php echo $level<4?$this->levels[$level]:$this->levels[3]; ?>-level collapse"><?php
		} 
		foreach ($elements as $element) {
			?>
			<li>
				<a href="<?php if (isset($element['children'])): ?>#<?php else: echo pathSite.$element['funcion']; endif; ?>">
					<span class="<?php echo ($element['icono']); ?>"></span> <?php echo ($element['texto']); if (isset($element['children'])):?> <span class="fa arrow"></span><?php endif; ?>
				</a>
				<?php if (isset($element['children'])) echo $this->liSideMenuTreeArray($element['children'], $level + 1); ?>
			</li>
			<?php
		}
		if ($level != 1) {
			?></ul><?php
		}
		return ob_get_clean();
	}
}