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

    //Create tables
    function createTables() {
        $utils = new utils();
        
        //:: Control de Accesos ::
        //Recursos
        $utils->arrayToTable([
            'table' => 'recursos',
            'columnDefs' => [
                'recurso_id' => ['int', 'autonum', 'primary', 'notnull'],
                'parent_id' => ['int'],
                'texto' => ['varchar'],
                'icono' => ['varchar'],
                'funcion' => ['varchar'],
                'grid_id' => ['int'],
                'eliminado' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Usuarios
        $utils->arrayToTable([
            'table' => 'usuarios',
            'columnDefs' => [
                'usuario_id' => ['int', 'autonum', 'primary', 'notnull'],
                'username' => ['varchar', 'notnull'],
                'password' => ['varchar', 'notnull'],
                'nombre' => ['varchar'],
                'correo' => ['varchar'],
                'avatar' => ['varchar'],
                'eliminado' => ['int']
            ],
            'data' => [
                [
                    'username' => 'asd',
                    'password' => 'zxc'
                ]
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Tokens
        $utils->arrayToTable([
            'table' => 'tokens',
            'columnDefs' => [
                'usuario_id' => ['int', 'primary', 'notnull'],
                'token' => ['varchar', 'notnull'],
                'created_at' => ['timestamp', 'notnull']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Permisos
        $utils->arrayToTable([
            'table' => 'permisos',
            'columnDefs' => [
                'usuario_id' => ['int'],
                'recurso_id' => ['int'],
                'permisos_obj' => ['json']
            ],
            'delete' => false,
            'duplicate' => false
        ]);

        //:: Mantenedores ::
        //Grids
        $utils->arrayToTable([
            'table' => 'grids',
            'columnDefs' => [
                'grid_id' => ['int', 'autonum', 'primary', 'notnull'],
                'name' => ['varchar'],
                'table_name' => ['varchar']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Grids Fields
        $utils->arrayToTable([
            'table' => 'grids_fields',
            'columnDefs' => [
                'field_id' => ['int', 'autonum', 'primary', 'notnull'],
                'grid_id' => ['int'],
                'name' => ['varchar'],
                'column_name' => ['varchar'],
                'type' => ['varchar'],
                'origin' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Fields Attrs
        $utils->arrayToTable([
            'table' => 'fields_attrs',
            'columnDefs' => [
                'field_id' => ['int'],
                'attr' => ['varchar']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
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