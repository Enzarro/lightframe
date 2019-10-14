<?php

class sys_tree_model {

    function __construct() {
        $this->utils = new utils();
        if (file_exists(root."/tree.json")) {
            $this->tree = json_decode(file_get_contents(root."/tree.json"));
        }
        if (!isset($this->tree)) {
            $this->tree = [];
        }
    }

    function list() {
        global $config;

        $table = 'recursos T1  
        RIGHT JOIN recursos T2 ON T1.recurso_id = T2.parent_id
        LEFT JOIN grids GR ON GR.grid_id = T2.grid_id';
        $primaryKey = 'T2.recurso_id';
        $dtNum = 0;
        $columns = [
            [
				//DB
				'dt' => $dtNum++,
				'db' => $primaryKey,
				//DT
                'title' => 'ID',
                'alias' => 'id',
				'searchable' => false,
				'visible' => false
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'T2.texto',
				'alias' => 'nombre',
                'title' => 'Nombre',
                'formatter' => function( $d, $row ) {
					ob_start(); ?>
                        <span aria-hidden="true" class="<?=$row["icono"]?>"></span> <?=$d?>
					<?php return ob_get_clean();
				}
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'T2.icono',
				'alias' => 'icono',
                'title' => 'Icono',
                'visible' => false
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'T2.funcion',
				'alias' => 'funcion',
				'title' => 'URL'
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => "GR.name",
				'alias' => 'grilla',
				'title' => 'Grilla'
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'T1.texto',
				//DT
				'title' => 'Padre',
                'alias' => 'padre',
                'formatter' => function( $d, $row ) {
					ob_start(); ?>
                        <span aria-hidden="true" class="<?=$row["icono_padre"]?>"></span> <?=$d?>
					<?php return ob_get_clean();
				}
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'T1.icono',
				'alias' => 'icono_padre',
                'title' => 'Icono Padre',
                'visible' => false
            ],
            [
				'dt' => $dtNum++,
				'db' => "''",
				'alias' => 'actions',
				'formatter' => function($d, $row) {
					ob_start(); ?>
						<div class="btn-group btn-group" role="group" style="width: auto;">
							<button class="btn btn-success main-edit" title="Editar registro" type="button"><span aria-hidden="true" class="fa fa-pencil"></span></button>
						</div>
					<?php return ob_get_clean();
				},
				'title' => 'Acciones',
				"responsivePriority" => 2,
				"orderable" => false,
				"width" => "50px",
				"searchable" => false
            ],
            [
                'dt' => $dtNum++,
				"title" => '<span class="glyphicon glyphicon-trash text-center" aria-hidden="true"></span>',
				"responsivePriority" => 1,
				"width" => "16px",
				"data" => null,
				"defaultContent" => "",
				"orderable" => false,
				"className" => 'select-checkbox',
				"searchable" => false
            ]
        ];
        $filtro = ['', "(T2.eliminado != 1 OR T2.eliminado IS NULL)"];

		return SSP::simple( $_POST, $config->database, $table, $primaryKey, $columns, $filtro);
    }
    
    function getCamposDTConfig() {
        $dtNum = 0;
        return [
            [
				'targets' => $dtNum++,
				'title' => "ID",
                'data' => 'id',
                'visible' => false,
                'searchable' => false,
                'editType' => 'id'
			],
			[
				'targets' => $dtNum++,
				'title' => "Key",
				'data' => 'key',
				'editType' => 'string'
            ],
            [
				'targets' => $dtNum++,
				'title' => "Permiso",
				'data' => 'permiso',
				'editType' => 'string'
            ],
			[
				'targets' => $dtNum++,
				'title' => "Acciones",
				'name' => 'actions',
				'data' => null,
				'width' => "105px",
				'defaultContent' => '',
				'editConfig' => [
					'deleteExisting' => true,
					'editExisting' => true
				]
			]
		];
    }
    
    function getCamposDTEmptyRow() {
        return [
            'id' => null,
            'key' => null,
            'permiso' => null,
            'estado' => 'edit'
        ];
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

    function set($data_crud) {
        // var_dump(json_encode($data_crud["permisos_obj"]));
        // exit;
        global $config;
        $_DB = new database($config->database);
        $data_crud["permisos_obj"] = array_map(function($permiso) {
            return [
                'key' => $permiso["key"],
                'permiso' => $permiso["permiso"]
            ];
        }, $data_crud["permisos_obj"]);
        $data = [
            'parent_id' => $data_crud["padre"],
            'texto' => $data_crud["name"],
            'icono' => $data_crud["icono"],
            'funcion' => $data_crud["path"],
            'grid_id' => $data_crud["grilla"],
            'permisos_obj' => json_encode($data_crud["permisos_obj"])
        ];
        if(isset($data_crud["id"])) {
            $_DB->queryToSingleVal("UPDATE recursos SET ".$this->utils->arrayToQuery('update', $data)." WHERE recurso_id = {$data_crud['id']}");
            return [
                'type' => 'success',
                'title' => 'Recurso editado!',
                'text' => 'El recurso '.$data_crud["name"].' ha sido editado con éxito'
            ]; 
        } else {          
            $_DB->query("INSERT INTO recursos ".$this->utils->arrayToQuery("insert", $data));
            return [
                'type' => 'success',
                'title' => 'Recurso creado',
                'text' => 'El recurso '.$data_crud["name"].' ha sido creado con éxito'
            ];  
        }           
    }

    function load_father() {
        global $config;
        $_DB = new database($config->database);
        $resources = $_DB->queryToArray(
            "SELECT 
                recurso_id, 
                texto 
            FROM 
                recursos"
        );
        $grids = $_DB->queryToArray(
            "SELECT 
                grid_id, 
                name 
            FROM 
            grids"
        );
        $res = [
            'resources' => $resources,
            'grids' => $grids
        ];
        return $res;
    }

    function get($id) {
        global $config;
        $_DB = new database($config->database);
        $res = $_DB->queryToArray("SELECT parent_id, texto, icono, funcion, grid_id, permisos_obj FROM recursos WHERE eliminado IS NULL AND recurso_id = {$id}");
        $res = (object)$res[0];
        $res->permisos_obj = json_decode($res->permisos_obj);
        $i = 1;
        foreach (array_keys($res->permisos_obj) as $key) {
            $permiso = $res->permisos_obj[$key];
            $res->permisos_obj[$key] = [
                'id' => $i++,
                'key' => $permiso->key,
                'permiso' => $permiso->permiso
            ];
        }
        return $res;
    }

    function delete($list) {
        global $config;
        $_DB = new database($config->database);
        $data = [
            'eliminado' => 1
        ];
        $_DB->queryToArray("UPDATE recursos SET ".$this->utils->arrayToQuery('update', $data)." WHERE recurso_id IN ".$this->utils->arrayToQuery('in', $list));
        return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
        ];
    }
}