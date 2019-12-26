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
        $execution_time = round((microtime(true) - $time_start) * 1000, 3);

        if (!is_string($db->conn)) {
            return [
                'type' => 'success',
                'title' => 'Conexión exitosa',
                'html' => 'Fue posible conectar con los parámetros dispuestos<br><small><span class="fa fa-tachometer-alt"></span> '.$execution_time.' ms</small>'
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
            'table' => 'sys_recursos',
            'key' => 'funcion',
            'columnDefs' => [
                'recurso_id' => ['int', 'autonum', 'primary', 'notnull'],
                'parent_id' => ['int'],
                'texto' => ['varchar'],
                'icono' => ['varchar'],
                'funcion' => ['varchar'],
                'orden' => ['int'],
                'grid_id' => ['int'],
                'permisos_obj' => ['json'],
                'eliminado' => ['int']
            ],
            'data' => [
                [
                    'texto' => 'Inicio',
                    'icono' => 'fa fa-home',
                    'funcion' => 'home',
                    'orden' => 1
                ],
                [
                    'texto' => 'Usuarios (Login)',
                    'icono' => 'fa fa-user-circle',
                    'funcion' => 'users',
                    'orden' => 2
                ]
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Usuarios
        $utils->arrayToTable([
            'table' => 'sys_usuarios',
            'columnDefs' => [
                'usuario_id' => ['int', 'autonum', 'primary', 'notnull'],
                'username' => ['varchar', 'notnull'],
                'password' => ['varchar', 'notnull'],
                'nombre' => ['varchar'],
                'correo' => ['varchar'],
                'avatar' => ['varchar'],
                'eliminado' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Tokens
        $utils->arrayToTable([
            'table' => 'sys_tokens',
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
            'table' => 'sys_permisos',
            'columnDefs' => [
                'usuario_id' => ['int'],
                'recurso_id' => ['int'],
                'permisos_obj' => ['json']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Usuarios / Clientes
        $utils->arrayToTable([
            'table' => 'sys_usuarios_clients',
            'columnDefs' => [
                'usuario_id' => ['int'],
                'client_id' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Usuarios / Historial
        $utils->arrayToTable([
            'table' => 'sys_historial',
            'columnDefs' => [
                'usuario_id' => ['int'],
                'client_id' => ['int'],
                'fecha_accion' => ['timestamp'],
                'nombre_tabla' => ['varchar'],
                'registro_id' => ['int'],
                'accion_id' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Historial ID
        $utils->arrayToTable([
            'table' => 'sys_historial_acciones',
            'key' => 'accion_id',
            'columnDefs' => [
                'accion_id' => ['int'],
                'nombre' => ['varchar']
            ],
            'data' => [
                [
                    'accion_id' => '1',
                    'nombre' => 'Crear'
                ],
                [
                    'accion_id' => '2',
                    'nombre' => 'Modificar'
                ],
                [
                    'accion_id' => '3',
                    'nombre' => 'Eliminar'
                ]
            ],
            'delete' => false,
            'duplicate' => false
        ]);

        //:: Mantenedores ::
        //Clients
        $utils->arrayToTable([
            'table' => 'sys_clients',
            'columnDefs' => [
                'client_id' => ['int', 'autonum', 'primary', 'notnull'],
                'db_name' => ['varchar', 'notnull'],
                'label' => ['varchar', 'notnull'],
                'image' => ['varchar'],
                'deleted' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);

        //Grids
        $utils->arrayToTable([
            'table' => 'sys_grids',
            'columnDefs' => [
                'grid_id' => ['int', 'autonum', 'primary', 'notnull'],
                'name' => ['varchar'],
                'table_name' => ['varchar'],
                'target_schema' => ['int']
            ],
            'delete' => false,
            'duplicate' => false
        ]);
        //Grids Fields
        $utils->arrayToTable([
            'table' => 'sys_grids_fields',
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
            'table' => 'sys_fields_attrs',
            'columnDefs' => [
                'field_id' => ['int'],
                'attr' => ['varchar']
            ],
            'delete' => false,
            'duplicate' => false
        ]);

        return [
            'type' => 'success',
            'title' => 'Inicialización',
            'html' => "Tablas creadas correctamente"
        ];
    }

    //Save to file / Response
    function setFile($config) {
        if (!file_put_contents(base."/config.json", json_encode($config, JSON_PRETTY_PRINT))) {
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