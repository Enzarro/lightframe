<?php

class lightframe {

    function __construct() {
        $this->login_view = new login_view();
        $this->login_model = new login_model();
        $this->model = new frame_model();
        $this->view = new frame_view();
        $this->sys_clients_model = new sys_clients_model();
        $this->main();
    }

    function main() {
        $uris = $this->getURIArray();
        global $config;
        
        $saltarvalidacion = false;

        //Login
        if ($uris[0] == "login" && ($uris[1] == "login" || $uris[1] == "logout" || $uris[1] == "error")) {
            $saltarvalidacion = true;
        }

        //Register
        if ($uris[0] == "register" && (!isset($uris[1]) || $uris[1] == "formaddress" || $uris[1] == "set" || $uris[1] == "error" || $uris[1] == "planes" 
                || $uris[1] == "mail" )) {
            $saltarvalidacion = true;
        }

        //Activar
        if ($uris[0] == "activar" && (!isset($uris[1]) || $uris[1] == "set" || $uris[1] == "mail")) {
            $saltarvalidacion = true;
        }

         //Recovery
         if ($uris[0] == "recovery" && (!isset($uris[1]) || $uris[1] == "set" || $uris[1] == "mail")) {
            $saltarvalidacion = true;
        }

        //Register Administrador
        if($uris[0] == "adminregister" && (!isset($uris[1]) || $uris[1] == "dte" || $uris[1] == "set" || $uris[1] == "terms" 
                || $uris[1] == "validAdm" || $uris[1] == "notifications")){
            $saltarvalidacion = true;
        }
        //Usuario
        if($uris[0] == "users" && (isset($uris[1]) && $uris[1] == "validateAccount")){
            $saltarvalidacion = true;
        }
        //Eventos
        if($uris[0] == "eventos") {
            $saltarvalidacion = true;
        }

		//Mi Perfil
		if($uris[0] == "sys_profile" && (!isset($uris[1]) || $uris[1] == "setuser" || $uris[1] == "setlogin")){
            $saltarvalidacion = true;
        }
        if ($saltarvalidacion) {
            $adminResources = $this->model->getResources('admin');
            $uriResource = array_values(array_filter($adminResources, function($res) use ($uris) {
                return $res['funcion'] == $uris[0];
            }));
            $uriResource = reset($uriResource);
            $this->callFunctionsByURI($uris, $uriResource);
            return;
        }
        //Validar sesión
        if (!isset($_COOKIE['token'])) {
            // No hay sesión iniciada, ir al login
            $this->login_view->html();
        } else {
            //Usuario con token / Enviar al home
            if ($uris[0] == "") {
                header('Location: /home');
                return;
            }
            //Validar token
            $userData = $this->login_model->getTokenData($_COOKIE['token']);
            if (!$userData) {
                //Error en token
                $this->unsetCookie();
                $this->login_view->html([
                    'error' => 2
                ]);
            } else if ($userData == 'admin') {
                // LOGIN ADMIN
                // -----------
                $adminResources = $this->model->getResources($userData);
                $uriResource = array_values(array_filter($adminResources, function($res) use ($uris) {
                    return $res['funcion'] == $uris[0];
                }));
                $uriResource = reset($uriResource);
                // Login Admin
                // Set Search Path
                if (isset($_POST['client']) && $_POST['client']) {
                    $this->model->setSearchPath($_POST['client']);
                }
                // Call function by URI array
                $this->callFunctionsByURI($uris, $uriResource);
            } else if (is_object($userData)) {
                // LOGIN USER DB
                // -------------
                if ((!isset($config->global->noclientlogin) || $config->global->noclientlogin == false) && !$this->sys_clients_model->get()) {
                    //Borrar token / Usuario no tiene permisos
                    $this->unsetCookie();
                    $this->login_view->html([
                        'error' => 4
                    ]);
                    exit;
                }

                //Get Clientes
                if ($uris[0] == "sys_clients" && ($uris[1] == "get")) {
                    $this->callFunctionsByURI($uris);
                    return;
                }

                $userResources = $this->model->getResources($userData);
                //Filtrar los recursos activos / quitar los recursos padre
                $activeResources = array_values(array_filter($userResources, function($res) {
                    return $res['activo'] == 1;
                }));
                //Buscar el recurso que se está llamando por uri
                $uriResource = array_values(array_filter($activeResources, function($res) use ($uris) {
                    return $res['funcion'] == $uris[0];
                }));
                $uriResource = reset($uriResource);

                if ($uriResource) {
                    // Set Search Path
                    if (isset($_POST['client']) && $_POST['client']) {
                        $this->model->setSearchPath($_POST['client']);
                    }
                    //Si tiene acceso al recurso llamado por uri, llamar
                    $this->callFunctionsByURI($uris, $uriResource);
                } else {
                    if ($activeResources) {
                        if ($uris[0] == "home") {
                            // Set Search Path
                            if (isset($_POST['client']) && $_POST['client']) {
                                $this->model->setSearchPath($_POST['client']);
                            }
                            //Si quiere ir al home y no tiene permisos, redirigir al primer recurso disponible
                            header("Location: /{$activeResources[0]['funcion']}");
                            return;
                        } else if (in_array($uris[0], array_column($userResources, 'funcion')) && !in_array($uris[0], array_column($activeResources, 'funcion'))) {
                            //Está intentando acceder a una uri a la que no tiene permiso
                            $this->forbiddenUrl();
                        } else if (!in_array($uris[0], array_column($userResources, 'funcion'))) {
                            //La uri no existe
                            $this->notFoundUrl($uris[1]);
                        }
                    } else {
                        //Borrar token / Usuario no tiene permisos
                        $this->unsetCookie();
                        $this->login_view->html([
                            'error' => 3
                        ]);
                    }
                }
            }
        }
    }

    function callFunctionsByURI($uris, $resource = null) {
        global $config;
        
        //Funciones del sistema
        $sys_res = array_map(function($res) {return $res->funcion;}, $config->sysres);
        $grid_res = $this->model->getResourceGrid($uris[0]);

        //Si no es una función del sistema y Si tiene una grilla asociada
        if (!in_array($uris[0], $sys_res) && $grid_res) {
            
            //Usar controlador genérico
            // include_once(controllers."sys_generic.php");
            //Instancia de controlador genérico
            $instancia = new sys_generic($uris[0], $resource);
        } else {
            //Controlador desde URI
            if (!class_exists($uris[0])) {
                $this->notFoundUrl($uris[0]);
            }
            // if (!file_exists(controllers."{$uris[0]}.php")) {
            //     $this->notFoundUrl();
            // }
            //Controlador
            // include_once(controllers."{$uris[0]}.php");
            //Instancia de objeto desde uri
            $instancia = new $uris[0]($resource);
        }
        
        //Funcion de objeto desde uri
        $funcion = isset($uris[1]) && $uris[1] != "" ? $uris[1] : null;
        //Si se está llamando a una función desde la uri
        if ($funcion) {
            //Validar si existe esa función en la instancia
            if (method_exists($instancia, $funcion)) {
                //Llamar a la función
                $result = $instancia->$funcion();
                if (is_array($result)) {
                    echo json_encode($result);
                } else {
                    echo $result;
                }
            } else {
                $this->notFoundUrl($uris[1]);
            }
        } else {
            //Si no se está llamando a una función, llamar a la función main
            $instancia->main();
        }
    } 

    function getURIArray() {
        $pathFolder = '/';
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        if (substr($uri, 0, strlen($pathFolder)) == $pathFolder) {
            return explode('/', substr($uri, strlen($pathFolder)));
        } else {
            return explode('/', $uri);
        }
    }

    function unsetCookie() {
        if (isset($_COOKIE['token'])) {
            unset($_COOKIE['token']);
            setcookie('token', null, -1, '/');
        }
    }

    function forbiddenUrl() {
        header("HTTP/1.0 403 Forbidden");
        $this->view->errorpage(403, 'Acceso Denegado');
        exit();
    }

    function notFoundUrl($string = '') {
        header("HTTP/1.0 404 Not Found");
        $this->view->errorpage(404, "Página no encontrada".($string?"<br>{$string}":""));
        exit();
    }

}