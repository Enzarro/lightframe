<?php

class sys_config_model {

    //User
    function setUser($data) {
        global $config;
        $config->superuser->username = $data->username;
        $config->superuser->password = $data->password;
        return $this->setFile($config);
    }

    //DB
    function setDB($data) {
        global $config;
        $config->database->host = $data->host;
        $config->database->port = $data->port;
        $config->database->name = $data->name;
        $config->database->user = $data->user;
        $config->database->pass = $data->pass;
        $config->database->type = $data->type;
        return $this->setFile($config);
    }

    function testDB($data) {
        $time_start = microtime(true);
        $db = new database($data);
        $execution_time = round((microtime(true) - $time_start) * 1000);

        if (!is_string($db->conn)) {
            return [
                'type' => 'success',
                'title' => 'Conexión exitosa',
                'html' => 'Fue posible conectar con los parámetros dispuestos<br><small><span class="fa fa-tachometer"></span> '.$execution_time.' ms</small>'
            ];
        } else {
            return [
                'type' => 'warning',
                'title' => 'Conexión fallida',
                'html' => "No fue posible conectar con los parámetros dispuestos<br><pre>".$db->conn."</pre>"
            ];
        }
    }

    //Login
    function setLogin($data) {
        global $config;
        $config->login->host = $data->loginhost;
        return $this->setFile($config);
    }

    function testLogin($data) {
        $data->loginhost;
        $data->testuser;
        $data->testpass;
    }

    //Save to file / Response
    function setFile($config) {
        if (!file_put_contents(root."/config.json", json_encode($config, JSON_PRETTY_PRINT))) {
            //Error
            return [
                'type' => 'warning',
                'title' => 'Cambios no guardados',
                'text' => 'Hubo un problema al guardar el fichero'
            ];
        } else {
            //Success
            return [
                'type' => 'success',
                'title' => 'Cambios guardados',
                'text' => 'Los cambios fueron guardados con éxito'
            ];
        }
    }
}