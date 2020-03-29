<?php

class login_view {
    /**
     * @param bool  $error  Mostrar mensaje de error 
     */


    function __construct(){
        $this->frame_view = new frame_view();
        // $this->register_model = new register_model();
        $this->utils = new utils();
        $this->css = [
            '/app-assets/css/colors.css',
            '/app-assets/css/components.css',
            '/app-assets/css/core/menu/menu-types/vertical-menu.css',
            '/app-assets/css/core/colors/palette-gradient.css',
            '/app-assets/css/plugins/animate/animate.css',
            '/css/bootstrap.min.css',
            '/css/style.css'
        ];
        $this->js =[
            '/app-assets/js/core/libraries/jquery.min.js',
            '/app-assets/vendors/js/vendors.min.js',
            '/app-assets/vendors/js/animation/jquery.appear.js',
            '/js/jquery-3.4.1.slim.min.js',
            '/js/popper.min.js',
            '/js/bootstrap.min.js',
            '/app-assets/sweetalerts/sweetalert2.all.min.js',
            '/js/register.js'
        ];

    } 


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
          <title>BuildingClerk | Login</title>
          <link rel="apple-touch-icon" href="<?=public_url?>/app-assets/images/ico/apple-icon-120.png">
          <link rel="shortcut icon" type="image/x-icon" href="<?=public_url?>/app-assets/images/ico/favicon.ico">
          <?=$this->utils->pluginLoader($this->css, 'css')?>
          <link href="https://fonts.googleapis.com/css?family=Raleway:400,500,700,800&display=swap" rel="stylesheet">

      </head>
      <!-- END: Head-->

      
        <!-- <body class="vertical-layout vertical-menu 1-column  bg-full-screen-image blank-page" data-open="click" data-menu="vertical-menu" data-col="1-column">

          BEGIN: Content
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
                                          <!--<p class="card-subtitle line-on-side text-muted text-center font-small-3 mx-2 my-1"><span>Agenda rapidamente tu clase</span></p>
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
          </div> -->
      <body>
        <div class="contenedor-general">
          <div class="container">            
            <div class="row no-gutters sombra segundo-bloque  animated fadeIn" id="login_view">
              <div class="col-5 color-fondo cambio-mobile">
                <div class="logo "> <img class="img-fluid" src="<?=public_url?>/img/login/logo.jpg" alt="" title=""> </div>
                  <div id="step1">
                    <?php $this->step1($error); ?>
                  </div>
                  <div id="step2"  style="display:none;">
                    <?php $this->step2($error); ?>
                  </div>
                </div>
                <div class="col-7 color-fondo ocultar"> <img class="img-fluid" src="<?=public_url?>/img/login/banner.jpg" alt="" title=""> </div>
              </div>                                          
              <?php $this->conoce(); ?>
            </div>
          </div>
        </div>
        <script>if (sessionStorage.getItem("client") !== undefined) sessionStorage.removeItem("client");</script>
      </body>
      <?=$this->utils->pluginLoader($this->js, 'js')?>
      <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>    
    </html>
    
    <?php
    }

    function step1($error = null){
      ?>
      <div class="container-formulario">
        <div class="titulo-principal pb-2">
          <h1>Ingresa a <span>tu perfil</span></h1>
        </div>
        <div class="bajada"> Administra tu proyecto, configura tu perfil.</div>
        <form role="form" action="<?=base_url?>/login/login" method="post"  id="cbo">
          <div class="form-group pb-2">
            <input type="text" id="username" name="username" class="form-control estilo-input <?php if($error): ?> error  <?php endif; ?> "  
            placeholder="Nombre de usuario">
          </div>
          <div class="form-group col-form-label">
            <input type="password" id="password" name="password" class="form-control  estilo-input <?php if($error): ?> error  <?php endif; ?>" 
                placeholder="Tu Contraseña">
          </div>
          <input type="hidden" name="dispositivo" value="1">
          <div class="form-group col-form-label">
            <?php if($error): ?>
              <?php if($error == 1): ?><span class="bajada pl-2 rojo negrita">Nombre de usuario o contraseña inválidos.</span><?php endif; ?>
              <?php if($error == 2): ?><span class="bajada pl-2 rojo negrita">Sesión caducada</strong> vuelva a ingresar.</span><?php endif; ?>
              <?php if($error == 3): ?><span class="bajada pl-2 rojo negrita">El usuario no tiene permisos asignados, contacte a su administrador.</span><?php endif; ?>
              <?php if($error == 4): ?><span class="bajada pl-2 rojo negrita">El usuario no tiene clientes asignados, contacte a su administrador.</span><?php endif; ?>
            <?php endif; ?>
          </div>
          <div class="checkbox">
            <div class="row pt-2">                       
              <div class="col">
                <label class="float-left ml-3"> <span class="txt-1 txt-right pr-1"> <a href="#"  onclick="backlogin(1);">Olvidaste tu clave</a> </span> </label>
              </div>
            </div>
          </div>
          <div class="row no-gutters pt-5">
            <div class="col-5 text-center">
              <button type="submit" class="btn btn-default btn-amarillo">Ingresar</button>
            </div>
            <div class="col-2 text-center">
              <div class="margen-interno"> ó </div>
            </div>
            <div class="col-5 text-center float-right ">
              <button type="button" id="conocelo" class="btn btn-default btn-calipso ">Conócelo</button>
            </div>
          </div>
        </form>
        <div class="contenedor-inferior">
          <div class="menu-inferior">
            <ul>
              <li><a href="#">Home</a></li>
              <li><a href="#">Ayuda</a></li>
              <li><a href="#">Contacto</a></li>
            </ul>
          </div>
          <div class="caja-txt"><a href="#">Building Clerk</a></div>
        </div>
      </div>
      <?php
    }

    function step2($error = null){
      ?>
      <div class="container-formulario">
        <div class="titulo-principal pb-2">
          <h1>Recupera <span>tu clave</span></h1>
        </div>
        <form role="form" action="<?=base_url?>/login/recovery" method="post" id="rec">
        <div class="bajada"> Ingrese su Usuario</div>
          <div class="form-group pb-2">
            <input type="text" id="user" name="user" class="form-control estilo-input"  
            placeholder="Nombre de usuario">
          </div>
          <div class="form-group col-form-label">
          </div>
          <div class="row no-gutters pt-5">
            <div class="col-5 text-center">
              <button type="button" class="btn btn-default btn-amarillo" id="cgg-recovery">Recuperar</button>
            </div>
            <div class="col-2 text-center">
              <div class="margen-interno"> ó </div>
            </div>
            <div class="col-5 text-center float-right">
              <button type="button" id="volver" class="btn btn-default btn-calipso" onclick="backlogin(2);">Volver</button>
            </div>
          </div>
        </form>
        <div class="contenedor-inferior">
          <div class="menu-inferior">
            <ul>
              <li><a href="#">Home</a></li>
              <li><a href="#">Ayuda</a></li>
              <li><a href="#">Contacto</a></li>
            </ul>
          </div>
          <div class="caja-txt"><a href="#">Building Clerk</a></div>
        </div>
      </div>
      <?php
    }


    function conoce(){
      $data = $this->register_model->listplanes();

      ?>
        <div id="conoce_view" class=" animated fadeInRight aparecer"> <img class="ico-cerrar" src="<?=public_url?>/img/login/ico-cerrar-2.png" alt="" title="">
                <div class="row no-gutters sombra relativo animated fadeInRight">
                  <div class="fondo-banner">
                    <div class="logo "> <img class="img-fluid" src="<?=public_url?>/img/login/logo-transparente.png" alt="" title=""> </div>
                    <div class="caja-info">
                      <div class="txt-info-1">Vive </div>
                      <div class="txt-info-2">informado </div>
                      <div class="txt-info-3">Con<span class="negrita">vive</span> con Building Clerk </div>
                    </div>
                    <div class="caja-video"> <img class="img-fluid" src="<?=public_url?>/img/login/momentaneo.png" alt="" title=""> </div>
                  </div>
                  <div class="row no-gutters fondo-1 margin-mobile-3 pb-0" >
                      <div class="col-12 col-sm-12 col-md-3 bloque margen-izq ">
                        <div class="titulo-6">¿Cómo <span class="negrita">funciona?</span></div>
                        <div class="txt-simple centrado-txt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae nibh porttitor, eleifend nisi et, commodo ante. Sed ac finibus metus, sit amet tempor est. Suspendisse potenti.</div>
                        <div class="centrado-btn">
                          <a href="http://www.clerk.cl" class="btn btn-amarillo ancho-165 ancho-variable">VER SITIO WEB</a>
                        </div>
                      </div>

                      <?php
                        $cont = 0;
                        
                        foreach($data as $p){
                            $title = ($cont == 0) ? 'titulo-7' : 'titulo-8';
                            $md = ($cont == 0 || $cont == (count($data)-1)) ? '' : 'bordes-laterales';
                            $b = ($cont == (count($data)-1)) ? 'margen-derecho' : '';
                            $nom = explode(" ",$p["nombre_plan"]);
                            $plan = '';
                            for($i = 0; $i < count($nom); $i++){
                                $plan .= ($p["palabra_destacada"] == $i) ? '<span >'.$nom[$i].'</span> ' : $nom[$i].' ';
                            }
                          ?>
                          <div class="col-12 col-sm-12 col-md-3 bloque separacion-mobile fondo-1 <?=$md." ".$b; ?> ">
                            <div class="<?=$title; ?>"><?= $plan; ?></div> 
                            <div class="txt-simple-2"><?=$p["descripcion"]; ?></div>  
                            <div class="caja-fondo">Precio</div>   
                            <div class="dos-cajas">
                              <?php if($p["oferta"] == 1) : ?>
                              <div class="caja-1  borde-separador"><span class="style-1">normal</span> <span class="style-2"><?=$p["precio"]; ?> uf</span></div>
                              <div class="caja-2"><span class="style-3">Promocional</span> <span class="style-4"><?=$p["precio_oferta"]; ?> uf</span></div>
                              <?php else : ?>
                                <div class="text-center"><span class="style-4"><?=$p["precio"]; ?> uf</span></div>
                              <?php endif; ?>
                            </div>                      
                          </div>
                          <?php
                            $cont+=1;
                        }
                      ?>
                  
                  <!--BOTON CONTRATAR MOBILE-->
                  <div class="col-12 col-sm-12 col-md-12 col-lg-7 col-xl-7 fondo-blanco-2 posicion-btn d-block d-lg-none d-xl-none ">
                    <button type="button" class="btn btn-calipso-especial" onclick="redirect(2);">CONTRATAR</button>
                  </div>
                  <div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-5  fondo-blanco-2 aparecer-mobile ">
                    <div class="contenedor-inferior">
                      <div class="menu-inferior margenes-menu">
                        <ul>
                          <li><a href="#">Home</a></li>
                          <li><a href="#">Ayuda</a></li>
                          <li><a href="#">Contacto</a></li>
                        </ul>
                      </div>
                      <div class="caja-txt posicion-2 posicion-mobile"><a href="#">Building Clerk</a></div>
                    </div>
                  </div>
                  
                  <!--BOTON CONTRATAR DESKTOP-->
                  <div class="col-12 col-sm-12 col-md-12 col-lg-7 col-xl-7 fondo-blanco-2 posicion-btn ocultar-mobile d-none d-lg-block">
                    <button type="button" class="btn btn-calipso-especial" onclick="redirect(2);">CONTRATAR</button>
                  </div>
                </div>
            </div>
          </div>
        </div> 
      <?php
    }
}