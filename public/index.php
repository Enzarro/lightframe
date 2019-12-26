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
if (file_exists(base."/config.json")) {
    $config = json_decode(file_get_contents(base."/config.json"));
} else {
    echo "No se encuentra el archivo config.json";
    exit();
}
if (file_exists(base."/sysres.json")) {
    $config->sysres = json_decode(file_get_contents(base."/sysres.json"));
} else {
    echo "No se encuentra el archivo sysres.json";
    exit();
}
//Global from config file
define('base_url', "{$config->global->public_url}:{$_SERVER['SERVER_PORT']}");
define('public_url', "{$config->global->public_url}:{$_SERVER['SERVER_PORT']}");

//DB object
include(classes.'utils.php');
if ($config->database->type == "pgsql") {
    utils::load([
        classes.'db.pgsql.php',
        classes.'ssp.class.pgsql.php'
    ]);
} else if ($config->database->type == "mssql") {
    utils::load([
        classes.'db.mssql.php',
        classes.'ssp.class.mssql.php'
    ]);
}

if (!isset($_COOKIE['token']) && isset($_POST['token'])) {
    // unset($_COOKIE);
    $_COOKIE['token'] = $_POST['token'];
}

//Incluir todos los archivos del frame (?)
utils::load([
    classes.'lightframe.php'
]);
utils::autoLoad();

$_DB = new database($config->database);

$client = null;
if (!is_string($_DB->conn) && isset($_POST['client']) && $_POST['client']) {
    $sys_clients_model = new sys_clients_model();
    $client = $sys_clients_model->get($_POST['client']);
}

new lightframe();