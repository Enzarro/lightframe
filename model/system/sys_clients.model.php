<?php

class sys_clients_model {

	var $db;
	var $table = 'sys_clients';
	var $primaryKey = 'client_id';
	var $fieldTypes = ['int', 'float', 'text', 'check', 'select', 'bselect', 'dtpicker', 'rut'];
	var $fieldAttributes = ['primary' => 'PK', 'autonum' => 'Autonumeric', 'notnull' => 'Obligatorio', 'hidden' => 'Ocultar'];
	var $system_schemas = [
		'dbo',
		'guest',
		'INFORMATION_SCHEMA',
		'sys',
		'db_owner',
		'db_accessadmin',
		'db_securityadmin',
		'db_ddladmin',
		'db_backupoperator',
		'db_datareader',
		'db_datawriter',
		'db_denydatareader',
		'db_denydatawriter'
	];
    function __construct() {
		global $_DB;
		$this->db = $_DB;
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
							<button class="btn btn-warning main-consolidate" title="Consolidar en Base de Datos" type="button"><span aria-hidden="true" class="fa fa-database"></span></button>
							<button class="btn btn-warning main-sp" title="Consolidar SP" type="button"><span aria-hidden="true" class="fa fa-file-code"></span></button>
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
		$userData = isset($_COOKIE['token']) ? $this->login_model->getTokenData($_COOKIE['token']) : 'admin';
		// $userData = $this->login_model->getTokenData($_COOKIE['token']);
		$grid = [];
        //Grilla según ID
        if ($id != null) {
			// error_log($id);
			if ($userData == "admin") {
				$grid = $_DB->queryToArray("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = {$id} AND (NOT deleted = 1 OR deleted IS NULL)");
			} else {
				$grid = $_DB->queryToArray(
					"SELECT * FROM {$this->table}
					WHERE 
					client_id IN (SELECT {$this->primaryKey} FROM sys_usuarios_clients WHERE usuario_id = {$userData->usuario_id})
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
				$query = 
					"SELECT
						*
					FROM
						{$this->table}
					WHERE
						client_id IN (SELECT {$this->primaryKey} FROM sys_usuarios_clients WHERE usuario_id = '{$userData->usuario_id}')
						AND ( NOT deleted = 1 OR deleted IS NULL )";
				// $query = "SELECT * FROM {$this->table} 
				// INNER JOIN sys_usuarios_clients ON {$this->table}.{$this->primaryKey} = sys_usuarios_clients.{$this->primaryKey}
				// WHERE sys_usuarios_clients.usuario_id = {$userData->usuario_id} AND (NOT deleted = 1 OR deleted IS NULL)";
				$grid = $_DB->queryToArray($query);
			}
			if ($grid) {
				$grid = $this->filterExistingProyectos($grid);
			}
        }
		
		//Retornar objeto final
		return $grid;
	}

	function filterExistingProyectos($clientsData) {
		if (!$clientsData) return [];

		//Validar que los esquemas existan
		// $esquemas = array_map(fn($row) => $row['db_name'], $clientsData);
		$esquemas = array_map(function($row) {
			return $row['db_name'];
		} , $clientsData);
		$esquemasExistentes = $this->db->queryToArray("SELECT schema_name FROM information_schema.schemata WHERE schema_name IN ".$this->utils->arrayToQuery('in', $esquemas));
		$esquemasExistentes = array_map(function($row) {
			return $row['schema_name'];
		}, $esquemasExistentes);

		if (!$esquemasExistentes) return [];

		//Consultar
		$clientsData = array_filter($clientsData, function($row) use ($esquemasExistentes) {
			return in_array($row['db_name'], $esquemasExistentes);
		});

		return $clientsData;
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
	
    function delete($list, $hard = false) {
		global $_DB;
        $data = [
            'deleted' => 1
        ];
		$_DB->queryToArray("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $data)." WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $list));

		if ($hard) {
			$schemas = $_DB->queryToArray("SELECT [db_name] FROM {$this->table} WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $list));
			if ($schemas) {
				$client_existing_schemas = array_column($schemas, 'db_name');
				$client_existing_tables = $this->getExistingTables($client_existing_schemas);
				// error_log('Existing schemas: '.json_encode($client_existing_tables));
				//Eliminar tablas y esquemas; esquemas que no son del sistema
				foreach (array_keys($client_existing_tables) as $schema) {
					foreach ($client_existing_tables[$schema] as $table) {
						//Drop tables
						$_DB->query("DROP TABLE {$schema}.{$table};");
					}
				}
			}
		}
		
        return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
        ];
	}

	
	function consolidate($id, $emit = false) {
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
		//Traer listado de grillas
		$grids = $this->sys_grid_model->get();

		//Tablas de clientes
		$client_grids = array_filter($grids, function($grid) {
			return $grid["target_schema"] == 2;
		});

		$grid_current = 1;

		//Iterar sobre cada una y...
		foreach ($client_grids as $grid) {
			// if ($grid['table_name'] != 'tbl_etapa') continue;
			//Consolidar
			$this->sys_grid_model->consolidate($grid['grid_id'], $clientData->db_name);

			//Emitir actualizacion barra de carga
			if ($emit) {
				if(is_array($emit) && isset($emit['name'])){
					$bar = [
						'bar_clients' => [
							'name' => $emit['name'],
							'current' => $emit['current'],
							'max' => $emit['max'] 
						],
						'bar_grids' => [
							'name' => $grid['name'],
							'current' => $grid_current,
							'max' => count($client_grids)
						]
					];
				} else {
					$bar = [
						'bar_grids' => [
							'name' => $grid['name'],
							'current' => $grid_current,
							'max' => count($client_grids)
						]
					];
				}

				utils::emit($emit['event'], $bar);

				$grid_current++;
			}			
			
		}

		if ($emit) {
			if(is_array($emit) && isset($emit['name'])){
				$bar = [
					'bar_clients' => [
						'name' => $emit['name'],
						'current' => $emit['current'],
						'max' => $emit['max'] 
					],
					'bar_grids' => [
						'name' => "Ejecutando scripts SP",
						'current' => count($client_grids),
						'max' => count($client_grids)
					]
				];
			} else {
				$bar = [
					'bar_grids' => [
						'name' => "Ejecutando scripts SP",
						'current' => count($client_grids),
						'max' => count($client_grids)
					]
				];
			}

			utils::emit($emit['event'], $bar);
		}
		
		$this->utils->executeSP($clientData->db_name);

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
							'name' => $clientData->label." ".$this->utils->memory_usage(),
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

				utils::emit('sys_clients:update-bar', [
					'bar_clients' => [
						'name' => $clientData->label,
						'current' => $client_current,
						'max' => count($clients) + ($system_grids?1:0)
					],
					'bar_grids' => [
						'name' => "Ejecutando scripts SP",
						'current' => count($client_grids),
						'max' => count($client_grids)
					]
				]);

				$this->utils->executeSP($clientData->db_name);

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

			utils::emit('sys_clients:update-bar', [
				'bar_clients' => [
					'name' => 'Tablas comunes',
					'current' => $client_current,
					'max' => count($clients) + ($system_grids?1:0)
				],
				'bar_grids' => [
					'name' => "Ejecutando scripts SP",
					'current' => count($system_grids),
					'max' => count($system_grids)
				]
			]);

			$this->utils->executeSP("dbo");
		}

		return [
			'type' => 'success',
			'title' => 'Éxito',
			'text' => 'Las tablas han sido consolidadas'
		];
	}

	function cleansing($resume = false) {
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

		//Traer listado de tablas en esquema DBO
		$system_existing_tables = $this->getExistingTables('dbo');

		//Traer listado de tablas por cada esquema de cliente
		$client_existing_tables = [];

		$client_existing_tables = $this->getExistingTables(array_column($clients, "db_name"));

		foreach (array_keys($clients) as $key) {
			// $client_existing_tables[$clients[$key]["db_name"]] = $this->getExistingTables($clients[$key]["db_name"]);
			if ($client_existing_tables[$clients[$key]["db_name"]]) {
				$client_existing_tables[$clients[$key]["db_name"]] = array_values(array_filter($client_existing_tables[$clients[$key]["db_name"]], function($table) use ($client_grids) {
					return !in_array($table, array_column($client_grids, 'table_name'));
				}));
			}
		}

		$system_existing_tables = array_values(array_filter($system_existing_tables, function($table) use ($system_grids) {
			return !in_array($table, array_column($system_grids, 'table_name'));
		}));

		

		return compact('system_existing_tables', 'clients', 'client_existing_tables');

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

	function getExistingTables($schema) {
		global $_DB;
		if (!$schema) return [];
		if (is_string($schema)) {
			$exists = $_DB->queryToSingleVal("SELECT COUNT(*) FROM sys.schemas WHERE name = '{$schema}'");
			if ($exists) {
				return array_column($_DB->queryToArray("SELECT table_name FROM information_schema.tables WHERE table_schema = '{$schema}'"), 'table_name');
			} else {
				return $exists;
			}
		} else if (is_array($schema)) {
			error_log(json_encode($schema));
			$result = [];
			$exists = $_DB->queryToArray("SELECT name FROM sys.schemas WHERE name IN ".$this->utils->arrayToQuery('in', $schema));
			$tables = [];
			if ($exists) {
				$exists = array_column($exists, 'name');
				error_log(json_encode($exists));
				$tables = $_DB->queryToArray("SELECT table_schema, table_name FROM information_schema.tables WHERE table_schema IN ".$this->utils->arrayToQuery('in', $exists));
			}
			foreach ($schema as $sch) {
				if (in_array($sch, $exists)) {
					$result[$sch] = array_filter($tables, function($table) use ($sch) {
						return $table['table_schema'] == $sch;
					});
					$result[$sch] = array_column(array_values($result[$sch]), 'table_name');
				} else {
					$result[$sch] = 0;
				}
			}
			return $result;
		}
		
	}

	function getExistingSchemas($excludesystemschemas = true) {
		global $_DB;
		$system_schemas = $this->system_schemas;
		$sql = "SELECT * FROM information_schema.schemata WHERE SCHEMA_NAME NOT IN ".$this->utils->arrayToQuery('in', $this->system_schemas);
		$schemas = $_DB->queryToArray($sql);
		if ($excludesystemschemas) {
			$schemas = array_filter($schemas, function($schema) use ($system_schemas) {
				return !in_array($schema['SCHEMA_NAME'], $system_schemas);
			});
		}
		return $schemas;
	}
}