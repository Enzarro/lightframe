<?php
//Global
define('path', '../');
define('root', $_SERVER['DOCUMENT_ROOT']);
define('base', root.'/'.path);
define('models', base.'model/');
define('views', base.'view/');
define('controllers', base.'controller/');
define('classes', base.'class/');
//Load config file
$config = [];
if (file_exists(root."/config.json")) {
    $config = json_decode(file_get_contents(root."/config.json"));
}
//Global from config file
define('base_url', $config->global->public_url);
define('public_url', $config->global->public_url);

new lightframe();

class lightframe {

    function __construct() {
        //Incluir todos los archivos del frame (?)
        include(classes.'utils.php');
        utils::load([
            classes.'db.php',
            classes.'ssp.class.pg.php',
            controllers.'login.php',
            models.'frame',
            views.'frame'
        ]);
        $this->login = new login();
        $this->model = new frame_model();
        $this->main();
    }

    function main() {
        $uris = $this->getURIArray();
        // echo $_SERVER['REQUEST_URI'];
        // return;
        if ($uris[0] == "login" && ($uris[1] == "login" || $uris[1] == "logout" || $uris[1] == "error")) {
            $this->callFunctionsByURI($uris);
            return;
        }
        
        //Validar sesión
        if (!isset($_COOKIE['token'])) {
            // No hay sesión iniciada, ir al login
            $this->login->main();
        } else {
            //Validar token
            $userData = $this->login->model->getTokenData($_COOKIE['token']);
            if (!$userData) {
                //Error en token
                unset($_COOKIE['token']);
                setcookie('token', null, -1, '/');
                $this->login->error(2);
            } else if ($userData == 'admin') {
                // Login Admin / Call function by URI array
                $this->callFunctionsByURI($this->getURIArray());
            } else if (is_object($userData)) {
                // Login User DB
                $userResources = $this->model->getResources($userData);
                //Si el usuario tiene recursos asociados llamar funciones por uri
                $userResources = array_filter($userResources, function($res) {
                    return $res['activo'] == 1;
                });
                if ($userResources) {
                    $this->callFunctionsByURI($this->getURIArray());
                } else {
                    //Error en token
                    unset($_COOKIE['token']);
                    setcookie('token', null, -1, '/');
                    $this->login->error(3);
                }
            }
        }
    }

    function callFunctionsByURI($uris) {
        global $config;
        if ($uris[0] == "") {
            $uris = ["home"];
        }
        
        if (!in_array($uris[0], array_map(function($res) {
                return $res->funcion;
            }, $config->sysres)) && $this->model->getResourceGrid($uris[0])) {
            //Controlador genérico
            include_once(controllers."sys_generic.php");
            //Instancia de controlador genérico
            $instancia = new sys_generic($uris[0]);
        } else {
            //Controlador desde URI
            if (!file_exists(controllers."{$uris[0]}.php")) {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
            //Controlador
            include_once(controllers."{$uris[0]}.php");
            //Instancia de objeto desde uri
            $instancia = new $uris[0]();
        }
        
        //Funcion de objeto desde uri
        $funcion = isset($uris[1]) && $uris[1] != "" ? $uris[1] : null;
        //Si se está llamando a una función desde la uri
        if ($funcion) {
            //Validar si existe esa función en la instancia
            if (method_exists($instancia, $funcion)) {
                //Llamar a la función
                $instancia->$funcion();
            } else {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        } else {
            //Si no se está llamando a una función, llamar a la función main
            $instancia->main();
        }
    } 

    function getURIArray() {
        $pathFolder = '/';
        if (substr($_SERVER['REQUEST_URI'], 0, strlen($pathFolder)) == $pathFolder) {
           return explode('/', substr($_SERVER['REQUEST_URI'], strlen($pathFolder)));
        } else {
            return explode('/', $_SERVER['REQUEST_URI']);
        }
    }

}