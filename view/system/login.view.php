<?php

class login_view {
    /**
     * @param bool  $error  Mostrar mensaje de error 
     */
    function html($data = null) {
        if (!$data) {
            $data = [
                'error' => 0
            ];
        }
        extract($data);
        ?>

        

    <!DOCTYPE html>
    <html class="loading" lang="en" data-textdirection="ltr">
    <!-- BEGIN: Head-->

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="description" content="datview.cl">
        <meta name="keywords" content="datview.cl">
        <meta name="author" content="datview.cl">
        <title>TITULO</title>
        <link rel="apple-touch-icon" href="<?=public_url?>/app-assets/images/ico/apple-icon-120.png">
        <link rel="shortcut icon" type="image/x-icon" href="<?=public_url?>/app-assets/images/ico/favicon.ico">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet">

        <!-- BEGIN: Vendor CSS-->
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/vendors/css/vendors.min.css">
        <!-- END: Vendor CSS-->

        <!-- BEGIN: Theme CSS-->
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/bootstrap.css">
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/bootstrap-extended.css">
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/colors.css">
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/components.css">
        <!-- END: Theme CSS-->

        <!-- BEGIN: Page CSS-->
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/core/menu/menu-types/vertical-menu.css">
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/core/colors/palette-gradient.css">
          <link rel="stylesheet" type="text/css" href="<?=public_url?>/app-assets/css/plugins/animate/animate.css">
        <!-- END: Page CSS-->

        <!-- jQuery / FRAME -->
          <script src="<?=public_url?>/app-assets/js/core/libraries/jquery.min.js"></script>

        <!-- BEGIN: Vendor JS-->
          <script src="<?=public_url?>/app-assets/vendors/js/vendors.min.js"></script>
        <!-- BEGIN Vendor JS-->

        <!-- BEGIN: Page Vendor JS-->
          <script src="<?=public_url?>/app-assets/vendors/js/animation/jquery.appear.js"></script>
        <!-- END: Page Vendor JS-->

    </head>
    <!-- END: Head-->

      <body class="vertical-layout vertical-menu 1-column  bg-full-screen-image blank-page" data-open="click" data-menu="vertical-menu" data-col="1-column">

        <!-- BEGIN: Content-->
        <div class="app-content content">
            <div class="content-wrapper">
                <div class="content-header row mb-1">
                </div>
                <div class="content-body">
                    <section class="flexbox-container">
                        <div class="col-12 d-flex align-items-center justify-content-center">
                            <div class="col-lg-4 col-md-8 col-10 box-shadow-2 p-0">
                                <div class="card border-grey border-lighten-3 px-1 py-1 m-0">
                                    <div class="card-header border-0 mb-0 pb-0">
                                        <div class="card-title text-center">
                                          <img src="<?=public_url?>/app-assets/images/logo/logo_login.png" alt="branding logo" >
                                        </div>
                                        <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2"><span>Ingrese sus datos para iniciar sesión</span></h6>
                                    </div>
                                    <div class="card-content">
                                        <!--<p class="card-subtitle line-on-side text-muted text-center font-small-3 mx-2 my-1"><span>Agenda rapidamente tu clase</span></p>-->
                                        <div class="card-body"  style="margin-top:0px;">
                                            <form action="<?=base_url?>/login/login" method="post" class="form-horizontal" id="cbo">
                                              <fieldset class="form-group position-relative has-icon-left">
                                                  <input type="text" class="form-control round" id="username" name="username" autofocus alert="email" placeholder="Email" required alert>
                                                  <div class="form-control-position">
                                                      <i class="ft-user"></i>
                                                  </div>
                                              </fieldset>
                                              <fieldset class="form-group position-relative has-icon-left">
                                                  <input type="password" class="form-control round" id="password" name="password" alert="contraseña" placeholder="Contraseña" required>
                                                  <div class="form-control-position">
                                                      <i class="la la-key "></i>
                                                  </div>
                                              </fieldset>
                                              
                                              <button type="submit" class="btn btn-info btn-block round buttonAnimation " data-animation="pulse"  id="save"><i class="ft-unlock"></i> INGRESAR</button>
                                        
                                              <?php if($error): ?>
                                                <div class="alert round bg-warning alert-icon-left alert-dismissible mb-2 mt-2" role="alert">
                                                    <span class="alert-icon"><i class="la la-warning"></i></span>
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                    <?php if($error == 1): ?>Nombre de usuario o contraseña inválidos.<?php endif; ?>
                                                    <?php if($error == 2): ?><strong>Sesión caducada</strong> vuelva a ingresar.<?php endif; ?>
                                                    <?php if($error == 3): ?>El usuario no tiene permisos asignados, contacte a su administrador.<?php endif; ?>
                                                    <?php if($error == 4): ?>El usuario no tiene clientes asignados, contacte a su administrador.<?php endif; ?>
                                                </div>
                                              <?php endif; ?>


                                              <hr>
                                              <div class="form-group row">
                                                <div class="col-sm-8 col-12 float-sm-left text-center text-sm-left">
                                                  <button type="button" class="btn btn-link" id="rstpassBtn">Olvide mi contraseña</button>
                                                </div>
                                                
                                                <div class="col-sm-4 col-12 text-center text-sm-left pr-0">
                                                  <fieldset  style="display:none">
                                                    <input type="checkbox" id="remember-me" class="chk-remember">
                                                    <label for="remember-me"> Remember Me</label>
                                                  </fieldset>
                                                </div>
                                              </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>
        <script>if (sessionStorage.getItem("client") !== undefined) sessionStorage.removeItem("client");</script>
    </body>
    </html>
    
        <?php
    }
}