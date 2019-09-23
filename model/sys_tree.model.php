<?php

class sys_tree_model {

    function __construct() {
        if (file_exists(base."/tree.json")) {
            $this->tree = json_decode(file_get_contents(base."/tree.json"));
        }
        if (!isset($this->tree)) {
            $this->tree = [];
        }
    }

    function list() {
        if (isset($_POST["config"]) && $_POST["config"]) {
            $targets = 0;
            return [[
				'targets' => $targets++,
                'title' => 'ID',
                'visible' => false
            ], [
                'targets' => $targets++,
				'title' => 'Padre'
			], [
                'targets' => $targets++,
                'title' => 'Texto'
            ], [
                'targets' => $targets++,
                'title' => 'Icono'
            ], [
                'targets' => $targets++,
                'title' => 'Función'
            ], [
                'targets' => $targets++,
                'title' => 'Acciones',
                'data' => null,
                'searchable' => false,
                'orderable' => false,
                'width' => '100px',
                'defaultContent' => 
                    '<div class="btn-group btn-group" role="group" style="width: auto;">
                        <button class="btn btn-success main-edit" title="Editar registro" type="button"><span aria-hidden="true" class="fa fa-pencil"></span></button>
                    </div>'
            ]];
        } else {
            return [
                'data' => array_map(function($resource) {
                    return [
						$resource->id,
						$resource->parent_id,
                        $resource->texto,
                        $resource->icono,
                        $resource->funcion
                    ];
                }, $this->tree)
            ];
        }
    }

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