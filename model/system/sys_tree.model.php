<?php

class sys_tree_model {

    var $table = 'sys_recursos';
	var $primaryKey = 'recurso_id';

    function __construct() {
        $this->utils = new utils();
        $this->sys_grid_model = new sys_grid_model();
    }

    function list() {
        global $config;

        $table = "{$this->table} T1  
        RIGHT JOIN {$this->table} T2 ON T1.{$this->primaryKey} = T2.parent_id
        LEFT JOIN {$this->sys_grid_model->table} GR ON GR.{$this->sys_grid_model->primaryKey} = T2.{$this->sys_grid_model->primaryKey}";
        $primaryKey = "T2.{$this->primaryKey}";

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
                'title' => 'URL',
                'visible' => false
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => "GR.name",
				'alias' => 'grilla',
                'title' => 'Grilla',
                'visible' => false
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
                            <button class="btn btn-success main-edit" title="Editar registro" type="button"><i class="fas fa-edit"></i></button>
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

    function getCampos() {
        return [
            [
                'id' => 1,
                'permiso' => 'Nuevo',
                'key' => 'create',
                'activo' => false,
                'fixed' => true
            ],
            [
                'id' => 2,
                'permiso' => 'Ver',
                'key' => 'read',
                'activo' => false,
                'fixed' => true
            ],
            [
                'id' => 3,
                'permiso' => 'Editar',
                'key' => 'update',
                'activo' => false,
                'fixed' => true
            ],
            [
                'id' => 4,
                'permiso' => 'Eliminar',
                'key' => 'delete',
                'activo' => false,
                'fixed' => true
            ],
            [
                'id' => 5,
                'permiso' => 'Exportar',
                'key' => 'export',
                'activo' => false,
                'fixed' => true
            ],
            [
                'id' => 6,
                'permiso' => 'Importar',
                'key' => 'import',
                'activo' => false,
                'fixed' => true
            ]
        ];
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
				'title' => "Permiso",
				'data' => 'permiso',
                'editType' => 'string',
                'editConfig' => [
                    'blockFixed' => true
                ]
            ],
            [
				'targets' => $dtNum++,
                'title' => "Key",
                'data' => 'key',
                'width' => "100px",
                'editType' => 'string',
                'editConfig' => [
                    'blockFixed' => true
                ]
            ],
            [
                'targets' => $dtNum++,
                'title' => 'Activo',
                'data' => 'activo',
                'width' => "30px",
                'editType' => 'checkbox'
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
            'activo' => true,
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

    function set($data_crud, $import = false) {
        global $_DB;
        $_DB->update_sequence($this->table, $this->primaryKey);
        if (isset($data_crud["permisos_obj"])) {
            //Quitar los que no están marcados
            $data_crud["permisos_obj"] = array_values(array_filter($data_crud["permisos_obj"], function($permiso) {
                return $this->utils->is_true($permiso["activo"]);
            }));
            //Crear arreglo sólo con los campos relevantes
            $data_crud["permisos_obj"] = array_map(function($permiso) {
                return [
                    'key' => $permiso["key"],
                    'permiso' => $permiso["permiso"]
                ];
            }, $data_crud["permisos_obj"]);
            //Setear como json para la consulta
            $data_crud["permisos_obj"] = json_encode($data_crud["permisos_obj"]);
        } else {
            $data_crud["permisos_obj"] = null;
        }
        
        $data = [
            'parent_id' => $data_crud["padre"],
            'texto' => $data_crud["name"],
            'icono' => $data_crud["icono"],
            'funcion' => $data_crud["path"],
            'orden' => $data_crud["orden"],
            'grid_id' => $data_crud["grilla"],
            'permisos_obj' => $data_crud["permisos_obj"]
        ];
        if(!$import && isset($data_crud["id"])) {
            $_DB->queryToSingleVal("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $data)." WHERE {$this->primaryKey} = {$data_crud['id']}");
            return [
                'type' => 'success',
                'title' => 'Recurso editado!',
                'text' => 'El recurso '.$data_crud["name"].' ha sido editado con éxito'
            ]; 
        } else {
            if ($import) $data["{$this->primaryKey}"] = $data_crud["id"];
            if ($import) {
                if ($config->database->type == 'pgsql') {

				} else if ($config->database->type == 'mssql') {
                    $_DB->query("SET IDENTITY_INSERT {$this->table} ON");
				}
            }
            $_DB->query("INSERT INTO {$this->table} ".$this->utils->arrayToQuery("insert", $data));
            if ($import) {
                if ($config->database->type == 'pgsql') {

				} else if ($config->database->type == 'mssql') {
                    $_DB->query("SET IDENTITY_INSERT {$this->table} OFF");
				}
            }
            return [
                'type' => 'success',
                'title' => 'Recurso creado',
                'text' => 'El recurso '.$data_crud["name"].' ha sido creado con éxito'
            ];  
        }           
    }

    function load_father() {
        global $_DB;
        return [
            'resources' => $_DB->queryToArray("SELECT {$this->primaryKey}, texto, icono FROM {$this->table} WHERE (eliminado != 1 OR eliminado IS NULL)"),
            'grids' => $_DB->queryToArray("SELECT {$this->sys_grid_model->primaryKey}, name FROM {$this->sys_grid_model->table}")
        ];
    }

    function get($id = null, $export = false) {
        global $_DB;
        if (!$id) {
            return $_DB->queryToArray("SELECT {$this->primaryKey}, parent_id, texto, icono, funcion, orden, grid_id, permisos_obj FROM {$this->table} WHERE (eliminado != 1 OR eliminado IS NULL)");
        }
        $campos = $this->getCampos();
        $res = $_DB->queryToArray(
            "SELECT
                ".($export?"{$this->primaryKey} AS id,":"")."
                parent_id".($export?" AS padre":"").", 
                texto".($export?" AS name":"").", 
                icono, 
                funcion".($export?" AS path":"").", 
                orden, 
                grid_id".($export?" AS grilla":"").", 
                permisos_obj 
            FROM 
                {$this->table} 
            WHERE (eliminado != 1 OR eliminado IS NULL) AND {$this->primaryKey} = {$id}");
        $res = (object)$res[0];
        $res->permisos_obj = json_decode($res->permisos_obj);
        if (is_array($res->permisos_obj)) {
            $selection = array_column($res->permisos_obj, 'key');
            //Marcar los predeterminados
            $permisos_final = array_map(function($row) use ($selection) {
                $row['activo'] = in_array($row['key'], $selection);
                return $row;
            }, $campos);
            //Agregar los adicionales
            $current = array_column($campos, 'key');
            
            foreach($res->permisos_obj as $permiso) {
                //Si la llave no está en el arreglo de ítems por defecto...
                if (!in_array($permiso->key, $current)) {
                    //Hacer push al arreglo final sin id, para que sea eliminable
                    $permisos_final[] = [
                        'id' => null,
                        'activo' => true,
                        'key' => $permiso->key,
                        'permiso' => $permiso->permiso
                    ];
                }
            }
            $res->permisos_obj = $permisos_final;
        } else {
            $res->permisos_obj = $campos;
        }
        
        return $res;
    }

    function getIconList() {
        $iconos = $this->utils->get('https://glyphsearch.com/data/batch.json', [], true);
        return array_filter($iconos, function($row) {
            return isset($row['_tags']) ? in_array($row['_tags'][0], ['font-awesome', 'glyphicons', 'foundation']) : false;
        });
    }

    function delete($list) {
        global $_DB;
        $data = [
            'eliminado' => 1
        ];
        $_DB->queryToArray("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $data)." WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $list));
        return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
        ];
    }
}