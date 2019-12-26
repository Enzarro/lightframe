<?php

class users_model {

	var $table = 'sys_usuarios';
	var $primaryKey = 'usuario_id';
	var $permisosTable = 'sys_permisos';
	var $userClientTable = 'sys_usuarios_clients';

	function __construct($resource = null) {
		$this->resource = $resource;
		$this->utils = new utils();
		// $this->frame_view = new frame_view();
		$this->sys_tree_model = new sys_tree_model();
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
				'db' => 'nombre',
				//DT
                'title' => 'Nombre'
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'username',
				//DT
                'title' => 'Usuario'
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'correo',
				//DT
                'title' => 'Correo'
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
		$filtro = ['', "(NOT eliminado = 1 OR eliminado IS NULL)"];
		// $filtro = null;

		if (!isset($_POST["config"])) {
			if (!isset($this->resource['permisos_user_obj']) || !in_array('read', $this->resource['permisos_user_obj'])) {
				return [
					"draw" => intval( $_POST['draw'] ),
					"recordsTotal" => 0,
					"recordsFiltered" => 0,
					"swal" => [
						"type" => "error",
						"text" => "No tiene permisos para ver en este módulo."
					],
					"data" => []
				];
			}
		}

		return SSP::simple($_POST, $config->database, $this->table, $this->primaryKey, $columns, $filtro);
	}

	function set($data) {
		global $_DB;
		$resources_data = [];
		$db_data = [
			'username' => $data["usuario"]["user"],
			'nombre' => $data["usuario"]["name"],
			'correo' => $data["usuario"]["email"],
			'avatar' => null
		];
		if ($data["usuario"]["pass"]) {
			$db_data['password'] = sha1($data["usuario"]["pass"]);
		}
		if(isset($data["usuario"]["id"])) {
			if (!in_array('update', $this->resource['permisos_user_obj'])) {
				return [
					'type' => 'error',
					'text' => 'No tiene permisos para actualizar en este módulo.'
				];
			}

			$message = 'editado';
			$sql = "UPDATE {$this->table} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $db_data, 'where' => "WHERE {$this->primaryKey} = {$data["usuario"]["id"]}"]);
			error_log(json_encode($db_data));
			$_DB->query($sql);

			// usuarios permisos
			$_DB->query(
				"DELETE FROM {$this->permisosTable} WHERE {$this->primaryKey} = {$data["usuario"]["id"]}"
			);
			if($data["permisos"]) {
				foreach ($data["permisos"] as $key=>$permisos) {
					if(is_array($permisos)) {
						$resources_data[] = [
							"{$this->primaryKey}" => $data["usuario"]["id"],
							"{$this->sys_tree_model->primaryKey}" => $key,
							'permisos_obj' => json_encode($permisos)
						];
					}
				}
				if ($resources_data) {
					$_DB->query("INSERT INTO {$this->permisosTable} ".$this->utils->multipleArrayToInsert($resources_data));
				}
			}

			// usuarios clientes
			$_DB->query(
				"DELETE FROM {$this->userClientTable} WHERE {$this->primaryKey} = {$data["usuario"]["id"]}"
			);
			if($data["clientes"]) {
				$resources_data_cl = [];
				foreach ($data["clientes"] as $key=>$cliente) {
					if($cliente == 1){
						$idClient = str_replace("cl_","",$key);
						$resources_data_cl[] = [
							"{$this->primaryKey}" => $data["usuario"]["id"],
							"client_id" => $idClient
						];
					}
				}

				if ($resources_data_cl) {
					$_DB->query("INSERT INTO {$this->userClientTable} ".$this->utils->multipleArrayToInsert($resources_data_cl));
				}
			}

		} else {
			if (!in_array('create', $this->resource['permisos_user_obj'])) {
				return [
					'type' => 'error',
					'text' => 'No tiene permisos para crear en este módulo.'
				];
			}

			$message = 'creado';
			$user_id = $_DB->queryToArray("INSERT INTO {$this->table} ".$this->utils->arrayToQuery(['action' => "insert", 'array' => $db_data, 'return' => $this->primaryKey]));
			if($data["permisos"]) {
				$resources_data = [];
				foreach ($data["permisos"] as $key=>$permisos) {
					if(is_array($permisos)) {
						$resources_data[] = [
							"{$this->primaryKey}" => $user_id[0][$this->primaryKey],
							"{$this->sys_tree_model->primaryKey}" => $key,
							'permisos_obj' => json_encode($permisos)
						];
					}
				}
				if ($resources_data) {
					$_DB->query("INSERT INTO {$this->permisosTable} ".$this->utils->multipleArrayToInsert($resources_data));
				}
				
			}
		}
		return [
			'type' => 'success',
			'title' => 'Usuario '.$message,
			'text' => 'El usuario ('.$data["usuario"]["name"].') ha sido '.$message.' con éxito'
		]; 
	}

	public function load_resources() {
		global $_DB;
		$res = $_DB->queryToArray(
			"SELECT 
				{$this->sys_tree_model->primaryKey} AS id, 
				parent_id,
				texto, 
				icono,
				funcion,
				0 AS activo,
				permisos_obj
			FROM {$this->sys_tree_model->table}
			WHERE eliminado IS NULL ORDER BY orden"
		);
		$res = array_map(function($row) {
			$row["id"] = intval($row["id"]);
			$row["activo"] = intval($row["activo"]);
			$row["permisos_obj"] = json_decode($row["permisos_obj"]);
			$row["permisos_user_obj"] = [];
			return $row;
		}, $res);
		return $res;
	}

	public function get($id) {
		global $_DB;
		$personal_data = $_DB->queryToArray(
			"SELECT 
				username,
				password,
				nombre,
				correo,
				avatar,
				usuario_id
			FROM
				{$this->table}
			WHERE {$this->primaryKey} = {$id}"
		);
		$permission = $_DB->queryToArray(
			"SELECT 
				{$this->sys_tree_model->primaryKey},
				permisos_obj
			FROM
				{$this->permisosTable}
			WHERE {$this->primaryKey} = {$id}"
		);
		$data = [
			'personal_data' => $personal_data,
			'permission' => $permission
		];
		return $data;
	}

	public function delete($list) {
		if (!in_array('delete', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para eliminar en este módulo.'
			];
		}
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

	public function clientGet($id = null) {
		global $_DB;
		if($id){
			$sql = "SELECT 
                    sc.* 
					,case when suc.usuario_id > 0 then 'checked' else '' end AS activo
                FROM sys_clients AS sc 
                    LEFT JOIN sys_usuarios_clients AS suc
                    ON suc.client_id = sc.client_id
					AND suc.usuario_id = {$id}
				WHERE (NOT sc.deleted = 1 OR sc.deleted IS NULL)
                ORDER BY sc.label";
		}else{
			$sql = "SELECT 
                    sc.* 
					,'' AS activo
                FROM sys_clients AS sc 
                    LEFT JOIN sys_usuarios_clients AS suc
                    ON suc.client_id = sc.client_id
                ORDER BY sc.label";
		}
        
        $grid = $_DB->queryToArray($sql);
		return $grid;
	}

}