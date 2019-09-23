<?php
//Variables globales
define('path', '../');
define('root', $_SERVER['DOCUMENT_ROOT']);
define('base', root.'/'.path);
define('models', base.'model/');
define('views', base.'view/');
define('controllers', base.'controller/');
define('classes', base.'class/');
define('base_url', 'http://localhost');
define('public_url', 'http://localhost');
//Load initial configurations
$config = [];
if (file_exists(root."/config.json")) {
    $config = json_decode(file_get_contents(root."/config.json"));
}

// echo json_encode($config->superuser->username);
// exit;

new lightframe();

class lightframe {

    function __construct() {
        //Incluir todos los archivos del frame (?)
        include(classes.'utils.php');
        utils::load([
            classes.'db.php',
            controllers.'login.php',
            views.'frame'
        ]);

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
        session_start();
        if (!isset($_SESSION['key'])) {
            // No hay sesión iniciada, ir al login
            $cLogin = new login();
            $cLogin->main();
        } else {
            // Get URI Array
            $uris = $this->getURIArray();
            $this->callFunctionsByURI($uris);
            return;
        }
    }

    function callFunctionsByURI($uris) {
        if ($uris[0] == "") {
            $uris = ["home"];
        }

        if (!file_exists(controllers."{$uris[0]}.php")) {
            header("HTTP/1.0 404 Not Found");
            exit();
        }
        //Controlador
        include_once(controllers."{$uris[0]}.php");
        $instancia = new $uris[0]();
        $funcion = isset($uris[1]) && $uris[1] != "" ? $uris[1] : null;
        if ($funcion) {
            if (method_exists($instancia, $funcion)) {
                //Función
                $instancia->$funcion();
            } else {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        } else {
            //Home
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