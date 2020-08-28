<?php

class sys_config_model {

    var $dbo_reset_tables = [
        'sys_clients',
        'sys_clients_plus',
        'sys_tokens',
        'sys_usuarios',
        'sys_usuarios_plus',
        'sys_usuarios_clients',
        'sys_historial',

        'sys_temp_activacion',
        'sys_usuarios_administradoras',


        'cont_cuentas_corrientes',
        'cont_fk_grupo_cc',
        'cont_cc_adm_plus',


        'tbl_privilegios_usuario',
        'tbl_proyecto_usuario_perfil',
        'tbl_token_movil',

        'lnd_user_client_profile',
        'lnd_historial_envio',
        'lnd_planes_clients',

        'bck_formulario_evento'
    ];

    function __construct() {
        $this->grid_model = new sys_grid_model();
        $this->clients_model = new sys_clients_model;
    }

    function wipedb($resume = false) {
		global $_DB;
        global $config;

        //Vaciar ciertas tablas del sistema
        $system_clean_tables = $this->dbo_reset_tables;
        foreach ($system_clean_tables as $table) {
            $_DB->query("DELETE FROM dbo.{$table};");
        }

        //Traer listado de grillas public o dbo
        $system_grids = $this->grid_model->get();
		$system_grids = array_filter($system_grids, function($grid) {
			return $grid["target_schema"] == 1;
        });
        //Traer listado de tablas en esquema DBO
		$system_existing_tables = $this->clients_model->getExistingTables('dbo');
        //Filtrar y dropear tablas no declaradas de dbo
        $system_existing_tables = array_values(array_filter($system_existing_tables, function($table) use ($system_grids) {
			return !in_array($table, array_column($system_grids, 'table_name'));
        }));
        foreach ($system_existing_tables as $table) {
            $_DB->query("DROP TABLE dbo.{$table};");
        }

        //Traer listado de tablas por cada esquema de cliente
        $client_existing_schemas = $this->clients_model->getExistingSchemas();
        $client_existing_schemas = array_map(function($schema) {
            return $schema['SCHEMA_NAME'];
        }, $client_existing_schemas);

		$client_existing_tables = [];
        $client_existing_tables = $this->clients_model->getExistingTables($client_existing_schemas);

        $existing_procedures = $_DB->queryToArray("SELECT [schema] = OBJECT_SCHEMA_NAME([object_id]), name FROM sys.procedures;");

        //Eliminar tablas y esquemas; esquemas que no son del sistema
        foreach (array_keys($client_existing_tables) as $schema) {
            foreach ($client_existing_tables[$schema] as $table) {
                //Drop tables
                $_DB->query("DROP TABLE {$schema}.{$table};");
            }
            //Drop SP
            $procedures = array_filter($existing_procedures, function($procedure) use ($schema) {
                return $procedure['schema'] == $schema;
            });
            foreach ($procedures as $procedure) {
                $_DB->query("DROP PROCEDURE {$schema}.{$procedure['name']};");
            }
            
            //Drop schema
            $_DB->query("DROP SCHEMA {$schema};");
        }

        return compact('system_clean_tables', 'system_existing_tables', 'client_existing_tables');
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
        $config->database->port = $data->port;
        $config->database->name = $data->name;
        $config->database->user = $data->user;
        $config->database->pass = $data->pass;
        $config->database->type = $data->type;
        return $this->setFile($config);
    }

    function testDB($data) {
        global $frame_start;
        $time_start = microtime(true);
        $db = new database($data);
        $conn_time = round((microtime(true) - $time_start) * 1000, 3);
        $db->query("SELECT version();");
        $exec_time = round((microtime(true) - $time_start) * 1000, 3) - $conn_time;

        $frame_time = round((microtime(true) - $frame_start) * 1000, 3);
        

        if (!is_string($db->conn)) {
            return [
                'type' => 'success',
                'title' => 'Conexión exitosa',
                'html' => 'Fue posible conectar con los parámetros dispuestos<br>
                        <small>Conexión <span class="fa fa-tachometer-alt"></span> '.$conn_time.' ms</small><br>
                        <small>Consulta <span class="fa fa-tachometer-alt"></span> '.$exec_time.' ms</small><br>
                        <small>Frame <span class="fa fa-tachometer-alt"></span> '.$frame_time.' ms</small>'
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

    function createSP($schema){
        $utils = new utils();
		$utils->executeSP($schema);
	}

    //Create tables
    function createTables() {
        global $_DB;
        $utils = new utils();

        //Crear tablas base para usar modelo sys_grid
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
                'type' => ['varchar'],
                'column_name' => ['varchar'],
                'column_type' => ['varchar'],
                'column_length' => ['varchar'],
                'origin' => ['int'],
                'orden' => ['int']
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

        // :: Grillas ::
        $sys_grids = $this->grid_model->set([
            'name' => 'Grillas',
            'table' => 'sys_grids',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID',
                    'column' => 'grid_id',
                    'type' => 'int', 
                    'origin' => null,
                    'orden' => 1,
                    'attr' => ['autonum', 'primary', 'notnull']
                ], [
                    'name' => 'Nombre',
                    'column' => 'name',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2
                ], [
                    'name' => 'Tabla',
                    'column' => 'table_name',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 3
                ], [
                    'name' => 'Esquema objetivo',
                    'column' => 'target_schema',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 4
                ]
            ]
        ]);
        //Grids Fields
        $sys_grids_fields = $this->grid_model->set([
            'name' => 'Campos Grillas',
            'table' => 'sys_grids_fields',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID',
                    'column' => 'field_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 1,
                    'attr' => ['autonum', 'primary', 'notnull']
                ], [
                    'name' => 'ID Grilla',
                    'column' => 'grid_id',
                    'type' => 'bselect',
                    'origin' => $sys_grids['id'],
                    'orden' => 2
                ], [
                    'name' => 'Nombre',
                    'column' => 'name',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 3
                ], [
                    'name' => 'Tipo',
                    'column' => 'type',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 4
                ], [
                    'name' => 'DB Columna',
                    'column' => 'column_name',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 5
                ], [
                    'name' => 'DB Tipo',
                    'column' => 'column_type',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 6
                ], [
                    'name' => 'DB Longitud',
                    'column' => 'column_length',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 7
                
                ], [
                    'name' => 'Configuración de campo',
                    'column' => 'type_config',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 8
                ], [
                    'name' => 'Origen',
                    'column' => 'origin',
                    'type' => 'bselect',
                    'origin' => $sys_grids['id'],
                    'orden' => 9
                ], [
                    'name' => 'Orden',
                    'column' => 'orden',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 10
                ]
            ]
        ]);
        //Fields Attrs
        $sys_fields_attrs = $this->grid_model->set([
            'name' => 'Atributos Campos Grillas',
            'table' => 'sys_fields_attrs',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID Campo',
                    'column' => 'field_id',
                    'type' => 'bselect',
                    'origin' => $sys_grids_fields['id'],
                    'orden' => 1
                ], [
                    'name' => 'Atributo',
                    'column' => 'attr',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2
                ]
            ]
        ]);

        //:: Mantenedores ::
        //Clients
        $sys_clients = $this->grid_model->set([
            'name' => 'Clientes',
            'table' => 'sys_clients',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID',
                    'column' => 'client_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 1,
                    'attr' => ['autonum', 'primary', 'notnull', 'hidden']
                ], [
                    'name' => 'Nombre Esquema',
                    'column' => 'db_name',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Etiqueta',
                    'column' => 'label',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 3
                ], [
                    'name' => 'Imágen',
                    'column' => 'image',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 4
                ], [
                    'name' => 'Eliminado',
                    'column' => 'deleted',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 5
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_clients['id']);
        //:: Control de Accesos ::
        //Recursos
        $sys_recursos = $this->grid_model->set([
            'name' => 'Recursos',
            'table' => 'sys_recursos',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID',
                    'column' => 'recurso_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 1,
                    'attr' => ['autonum', 'primary', 'notnull', 'hidden']
                ], [
                    'name' => 'Parent ID',
                    'column' => 'parent_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 2
                ], [
                    'name' => 'Texto',
                    'column' => 'texto',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 3
                ], [
                    'name' => 'Ícono',
                    'column' => 'icono',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 4
                ], [
                    'name' => 'Función',
                    'column' => 'funcion',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 5
                ], [
                    'name' => 'Orden',
                    'column' => 'orden',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 6
                ], [
                    'name' => 'Grilla ID',
                    'column' => 'grid_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 7
                ], [
                    'name' => 'Permisos OBJ',
                    'column' => 'permisos_obj',
                    'type' => 'textarea',
                    'origin' => null,
                    'orden' => 8
                ], [
                    'name' => 'Eliminado',
                    'column' => 'eliminado',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 9
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_recursos['id']);
        //Datos
        $sys_recursos_data = [
            [
                'texto' => 'Inicio',
                'icono' => 'fa fa-home',
                'funcion' => 'home',
                'orden' => 1,
                'permisos_obj' => '{"1":{"key":"read","permiso":"Ver"}}'
            ],
            [
                'texto' => 'Usuarios',
                'icono' => 'fa fa-user-circle',
                'funcion' => 'users',
                'orden' => 2,
                'permisos_obj' => '[{"key":"create","permiso":"Nuevo"},{"key":"read","permiso":"Ver"},{"key":"update","permiso":"Editar"},{"key":"delete","permiso":"Eliminar"},{"key":"export","permiso":"Exportar"},{"key":"import","permiso":"Importar"}]'
            ],
            [
                'texto' => 'Roles',
                'icono' => 'fa fa-id-card',
                'funcion' => 'roles',
                'orden' => 3,
                'permisos_obj' => '[{"key":"create","permiso":"Nuevo"},{"key":"read","permiso":"Ver"},{"key":"update","permiso":"Editar"},{"key":"delete","permiso":"Eliminar"},{"key":"export","permiso":"Exportar"},{"key":"import","permiso":"Importar"}]'
            ]
        ];
        $sys_recursos_data_validcols = array_map(function($res) {
            return [
                'funcion' => $res['funcion']
            ];
        }, $sys_recursos_data);
        if (!$_DB->queryToSingleVal("SELECT COUNT(*) FROM sys_recursos WHERE ".$utils->multipleArrayToWhere($sys_recursos_data_validcols))) {
            $_DB->queryToSingleVal("INSERT INTO sys_recursos ".$utils->multipleArrayToInsert($sys_recursos_data));
        }
        //Roles
        $sys_roles = $this->grid_model->set([
            'name' => 'Roles',
            'table' => 'sys_roles',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID',
                    'column' => 'id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 1,
                    'attr' => ['autonum', 'primary', 'notnull']
                ], [
                    'name' => 'Nombre',
                    'column' => 'nombre',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Orden',
                    'column' => 'orden',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 3,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Eliminado',
                    'column' => 'eliminado',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 4
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_roles['id']);
        //Usuarios
        $sys_usuarios = $this->grid_model->set([
            'name' => 'Usuarios',
            'table' => 'sys_usuarios',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID',
                    'column' => 'usuario_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 1,
                    'attr' => ['autonum', 'primary', 'notnull', 'hidden']
                ], [
                    'name' => 'Usuario',
                    'column' => 'username',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Contraseña',
                    'column' => 'password',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 3,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Rol',
                    'column' => 'rol_id',
                    'type' => 'bselect',
                    'origin' => $sys_roles['id'],
                    'orden' => 4,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Estado',
                    'column' => 'estado',
                    'type' => 'int',
                    'orden' => 5
                ], [
                    'name' => 'Eliminado',
                    'column' => 'eliminado',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 6,
                    'attr' => ['hidden', 'hiddenForm']
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_usuarios['id']);
        //Usuarios Plus
        $sys_usuarios_plus = $this->grid_model->set([
            'name' => 'Usuarios Plus',
            'table' => 'sys_usuarios_plus',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'Nombre',
                    'column' => 'nombre',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 1
                ], [
                    'name' => 'Apellido',
                    'column' => 'apellido',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2,
                    'attr' => ['hidden']
                ], [
                    'name' => 'Teléfono',
                    'column' => 'telefono',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 3,
                    'attr' => ['hidden']
                ], [
                    'name' => 'Dirección',
                    'column' => 'direccion',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 4,
                    'attr' => ['hidden']
                ], [
                    'name' => 'RUT',
                    'column' => 'rut',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 5
                ], [
                    'name' => 'ID Login',
                    'column' => 'sys_usuarios_id',
                    'type' => 'bselect',
                    'origin' => $sys_usuarios['id'],
                    'orden' => 6
                ], [
                    'name' => 'ID Usuario PV',
                    'column' => 'id_usuario_pv',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 7,
                    'attr' => ['hidden']
                ], [
                    'name' => 'Base de Datos PV	',
                    'column' => 'base_dato_pv',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 8,
                    'attr' => ['hidden']
                ], [
                    'name' => 'ID',
                    'column' => 'sys_usuarios_plus_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 9,
                    'attr' => ['autonum', 'primary', 'notnull', 'hidden']
                ], [
                    'name' => 'Correo',
                    'column' => 'correo',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 10
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_usuarios_plus['id']);
        //Tokens
        $sys_tokens = $this->grid_model->set([
            'name' => 'Tokens',
            'table' => 'sys_tokens',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'Usuario',
                    'column' => 'usuario_id',
                    'type' => 'bselect',
                    'origin' => $sys_usuarios['id'],
                    'orden' => 1,
                    'attr' => ['primary', 'notnull']
                ], [
                    'name' => 'Token',
                    'column' => 'token',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Fecha creación',
                    'column' => 'created_at',
                    'type' => 'datetime',
                    'origin' => null,
                    'orden' => 3,
                    'attr' => ['notnull']
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_tokens['id']);

        //Permisos
        $sys_permisos = $this->grid_model->set([
            'name' => 'Permisos',
            'table' => 'sys_permisos',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'Rol',
                    'column' => 'rol_id',
                    'type' => 'bselect',
                    'origin' => $sys_roles['id'],
                    'orden' => 1,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Recurso',
                    'column' => 'recurso_id',
                    'type' => 'bselect',
                    'origin' => $sys_recursos['id'],
                    'orden' => 2,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Permisos',
                    'column' => 'permisos_obj',
                    'type' => 'textarea',
                    'origin' => null,
                    'orden' => 3
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_permisos['id']);
        //Usuarios / Clientes
        $sys_usuarios_clients = $this->grid_model->set([
            'name' => 'Usuarios Clientes',
            'table' => 'sys_usuarios_clients',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'Usuario',
                    'column' => 'usuario_id',
                    'type' => 'bselect',
                    'origin' => $sys_usuarios['id'],
                    'orden' => 1,
                    'attr' => ['notnull']
                ], [
                    'name' => 'Cliente',
                    'column' => 'client_id',
                    'type' => 'bselect',
                    'origin' => $sys_clients['id'],
                    'orden' => 2,
                    'attr' => ['notnull']
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_usuarios_clients['id']);
        //Historial Acciones
        $sys_historial_acciones = $this->grid_model->set([
            'name' => 'Historial Acciones',
            'table' => 'sys_historial_acciones',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'ID Acción',
                    'column' => 'accion_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 1
                ], [
                    'name' => 'Nombre',
                    'column' => 'nombre',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 2
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_historial_acciones['id']);
        //Datos
        $historial_acciones_data = [
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
        ];
        if (!$_DB->queryToSingleVal("SELECT COUNT(*) FROM sys_historial_acciones WHERE ".$utils->multipleArrayToWhere($historial_acciones_data))) {
            $_DB->queryToSingleVal("INSERT INTO sys_historial_acciones ".$utils->multipleArrayToInsert($historial_acciones_data));
        }
        
        //Usuarios / Historial
        $sys_historial = $this->grid_model->set([
            'name' => 'Historial',
            'table' => 'sys_historial',
            'target_schema' => 1,
            'fields' => [
                [
                    'name' => 'Usuario',
                    'column' => 'usuario_id',
                    'type' => 'bselect',
                    'origin' => $sys_usuarios['id'],
                    'orden' => 1
                ], [
                    'name' => 'Cliente',
                    'column' => 'client_id',
                    'type' => 'bselect',
                    'origin' => $sys_clients['id'],
                    'orden' => 2
                ], [
                    'name' => 'Fecha acción',
                    'column' => 'fecha_accion',
                    'type' => 'datetime',
                    'origin' => null,
                    'orden' => 3
                ], [
                    'name' => 'Tabla',
                    'column' => 'nombre_tabla',
                    'type' => 'text',
                    'origin' => null,
                    'orden' => 4
                ], [
                    'name' => 'ID Registro',
                    'column' => 'registro_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 5
                ], [
                    'name' => 'ID Acción',
                    'column' => 'accion_id',
                    'type' => 'int',
                    'origin' => null,
                    'orden' => 6
                ]
            ]
        ]);
        $this->grid_model->consolidate($sys_historial['id']);

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