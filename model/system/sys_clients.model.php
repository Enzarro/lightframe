<?php

class sys_clients_model {

	var $db;
	var $table = 'sys_clients';
	var $primaryKey = 'client_id';
	var $fieldTypes = ['int', 'float', 'text', 'check', 'select', 'bselect', 'dtpicker', 'rut'];
	var $fieldAttributes = ['primary' => 'PK', 'autonum' => 'Autonumeric', 'notnull' => 'Obligatorio', 'hidden' => 'Ocultar'];

    function __construct() {
		$this->utils = new utils();
		$this->sys_grid_model = new sys_grid_model();
		$this->login_model = new login_model();
        if (file_exists(root."/grids.json")) {
            $this->grids = json_decode(file_get_contents(root."/grids.json"));
        }
        if (!isset($this->grids)) {
            $this->grids = [];
        }
    }

	function list() {
		global $config;

		$dtNum = 0;
		$columns = [
			[
				//DB
				'dt' => $dtNum++,
				'db' => $this->primaryKey,
				//DT
				'title' => 'ID',
				'searchable' => false,
				'visible' => false
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'label',
				//DT
                'title' => 'Nombre'
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'db_name',
				//DT
                'title' => 'Esquema'
			],
			[
				'dt' => $dtNum++,
				'db' => "CASE WHEN (SELECT count(*) FROM information_schema.schemata WHERE schema_name = sys_clients.db_name) = 1 THEN 'Creado' ELSE 'No creado' END ",
				'alias' => 'status',
				'title' => 'Estado'
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'image',
				//DT
                'title' => 'Logo'
            ],
			[
				
				//DT
				'dt' => $dtNum++,
				'db' => "''",
				'alias' => 'actions',
				'formatter' => function( $d, $row ) {
					ob_start(); ?>
						<div class="btn-group btn-group" role="group" style="width: auto;">
							<button class="btn btn-success main-edit" title="Editar registro" type="button"><i class="fas fa-edit"></i></button>
							<!-- <button class="btn btn-warning main-consolidate" title="Consilidar en Base de Datos" type="button"><span aria-hidden="true" class="fa fa-database"></span></button> -->
						</div>
					<?php return ob_get_clean();
				},
				'title' => 'Acciones',
				"responsivePriority" => 2,
				"orderable" => false,
				"width" => "80px",
				"searchable" => false
            ],
            [
                //DT
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

		//Filtro: Contenido de cláusula WHERE, también puede contener JOIN
		$filtro = "(NOT deleted = 1 OR deleted IS NULL)";

		return SSP::simple( $_POST, $config->database, $this->table, $this->primaryKey, $columns, $filtro);
	}
	
	function get($id = null) {
		global $_DB;
		$userData = $this->login_model->getTokenData($_COOKIE['token']);
		$grid = [];
        //Grilla según ID
        if ($id) {
			if ($userData == "admin") {
				$grid = $_DB->queryToArray("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = {$id} AND (NOT deleted = 1 OR deleted IS NULL)");
			} else {
				$grid = $_DB->queryToArray(
					"SELECT * FROM {$this->table}
					INNER JOIN sys_usuarios_clients ON {$this->table}.{$this->primaryKey} = sys_usuarios_clients.{$this->primaryKey}
					WHERE sys_usuarios_clients.usuario_id = {$userData->usuario_id}
					AND {$this->table}.{$this->primaryKey} = {$id}
					AND (NOT deleted = 1 OR deleted IS NULL)");
			}
			if ($grid) {
				$grid = (object)$grid[0];
			}
        } else {
			if ($userData == "admin") {
				$grid = $_DB->queryToArray("SELECT * FROM {$this->table} WHERE (NOT deleted = 1 OR deleted IS NULL)");
			} else {
				$grid = $_DB->queryToArray(
					"SELECT * FROM {$this->table} 
					INNER JOIN sys_usuarios_clients ON {$this->table}.{$this->primaryKey} = sys_usuarios_clients.{$this->primaryKey}
					WHERE sys_usuarios_clients.usuario_id = {$userData->usuario_id} AND (NOT deleted = 1 OR deleted IS NULL)");
			}
        }
		
		//Retornar objeto final
		return $grid;
	}
	
	function set($data) {
		global $_DB;
		$obj_grid = [
			'db_name' => $data['db_name'],
			'label' => $data['label'],
			'image' => $data['image']
		];
		
		if (isset($data['id']) && $data['id']) {
			//:: UPDATE ::
			//Grid
			
			$_DB->query("UPDATE {$this->table} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $obj_grid, 'where' => "WHERE {$this->primaryKey} = {$data['id']}", 'return' => $this->primaryKey]));

			//Success
			return [
				'type' => 'success',
				'title' => 'Cambios guardados',
				'text' => 'Los cambios fueron guardados con éxito'
			];
			
		} else {
			//:: NEW ::
			//Grid
			$_DB->query("INSERT INTO {$this->table} ".$this->utils->arrayToQuery(['action' => 'insert', 'array' => $obj_grid, 'return' => $this->primaryKey]));

			//Success
			return [
				'type' => 'success',
				'title' => 'Cambios guardados',
				'text' => 'Los cambios fueron guardados con éxito'
			];
		}
	}
	
    function delete($list) {
		global $_DB;
        $data = [
            'deleted' => 1
        ];
        $_DB->queryToArray("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $data)." WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $list));
        return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
        ];
    }
	
	function consolidate($id) {
		global $_DB;
		global $config;
		//Traer datos del cliente
		$clientData = $this->get($id);
		//CREACIÓN DEL ESQUEMA
		if ($config->database->type == 'pgsql') {
			$_DB->query("CREATE SCHEMA IF NOT EXISTS {$clientData->db_name} AUTHORIZATION {$config->database->user}");
		} else if ($config->database->type == 'mssql') {
			$_DB->query("IF NOT EXISTS ( SELECT  *
				FROM    sys.schemas
				WHERE   name = N'{$clientData->db_name}' )
			EXEC('CREATE SCHEMA [{$clientData->db_name}]')");
		}
		

		//CREACIÓN DE LAS TABLAS
		//Traer todos los ids de las grillas
		$idgrillas = array_column($this->sys_grid_model->getGridCboList(), 'id');

		//Iterar sobre cada una y...
		foreach ($idgrillas as $grid_id) {
			//Consolidar
			$this->sys_grid_model->consolidate($grid_id, $clientData->db_name);
		}

		return [
			'type' => 'success',
			'text' => 'El esquema ha sido consolidado'
		];
	}

	function consolidate_all($resume = false) {
		global $_DB;
		global $config;

		//Traer listado de clientes
		$clients = $this->get();

		//Traer listado de grillas
		$grids = $this->sys_grid_model->get();

		//Tablas de clientes
		$client_grids = array_filter($grids, function($grid) {
			return $grid["target_schema"] == 2;
		});

		//Tablas esquema public o dbo
		$system_grids = array_filter($grids, function($grid) {
			return $grid["target_schema"] == 1;
		});

		if ($resume) {
			//Devolver resumen
			return compact('clients', 'client_grids', 'system_grids');
		}

		$client_current = 0;
		//Consolidar tablas de clientes
		if ($clients && $client_grids) {
			//Recorrer clientes
			foreach (array_keys($clients) as $key) {
				//Sumar 1 al contador clientes
				$client_current++;
				//Objeto
				$clients[$key]["db_name"] = trim($clients[$key]["db_name"]);
				$clientData = (object)$clients[$key];
				//CREACIÓN DEL ESQUEMA
				if ($config->database->type == 'pgsql') {
					$_DB->query("CREATE SCHEMA IF NOT EXISTS {$clientData->db_name} AUTHORIZATION {$config->database->user}");
				} else if ($config->database->type == 'mssql') {
					$_DB->query("IF NOT EXISTS ( SELECT  *
						FROM    sys.schemas
						WHERE   name = N'{$clientData->db_name}' )
					EXEC('CREATE SCHEMA [{$clientData->db_name}]')");
				}
				//Iterar sobre cada una de las grillas y...
				$grid_current = 0;
				foreach ($client_grids as $grid) {
					//Sumar 1 al contador grillas
					$grid_current++;
					//Emitir actualizacion barra de carga
					utils::emit('sys_clients:update-bar', [
						'bar_clients' => [
							'name' => $clientData->label,
							'current' => $client_current,
							'max' => count($clients) + ($system_grids?1:0)
						],
						'bar_grids' => [
							'name' => $grid['name'],
							'current' => $grid_current,
							'max' => count($client_grids)
						]
					]);
					//Consolidar
					$this->sys_grid_model->consolidate($grid['grid_id'], $clientData->db_name);
				}
			}
		}

		//Consolidar tablas del sistema
		if ($system_grids) {
			//Sumar 1 al contador clientes
			$client_current++;
			$grid_current = 0;
			foreach ($system_grids as $grid) {
				//Sumar 1 al contador grillas
				$grid_current++;
				//Emitir actualizacion barra de carga
				utils::emit('sys_clients:update-bar', [
					'bar_clients' => [
						'name' => 'Tablas comunes',
						'current' => $client_current,
						'max' => count($clients) + ($system_grids?1:0)
					],
					'bar_grids' => [
						'name' => $grid['name'],
						'current' => $grid_current,
						'max' => count($system_grids)
					]
				]);
				//Consolidar
				$this->sys_grid_model->consolidate($grid['grid_id']);
			}
		}

		return [
			'type' => 'success',
			'title' => 'Éxito',
			'text' => 'Las tablas han sido consolidadas'
		];
	}
}