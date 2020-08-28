<?php

class sys_roles_model {

	var $table = 'sys_roles';
	var $primaryKey = 'id';
	var $permisosTable = 'sys_permisos';

	function __construct($resource = null) {
		$this->resource = $resource;
		$this->utils = new utils();
		$this->sys_tree_model = new sys_tree_model();
		$this->permisos_model = new permisos_model();
	}
	
    function list() {
		global $config;
		
		$table = "{$this->table} AS r";

		$dtNum = 0;
		$columns = [
			[
				//DB
				'dt' => $dtNum++,
				'db' => "r.{$this->primaryKey}",
				'alias' => $this->primaryKey,
				//DT
				'title' => 'ID',
				'searchable' => false,
				'visible' => false,
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "r.nombre",
				'alias' => 'nombre',
				//DT
                'title' => 'Nombre',
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'r.orden',
				'alias' => 'orden',
				//DT
				'title' => 'Orden',
				'searchable' => false,
				'visible' => false,
            ],
            [
				//DB
				'dt' => $dtNum++,
				'db' => 'r.eliminado',
				'alias' => 'eliminado',
				//DT
				'title' => 'Eliminado',
				'searchable' => false,
				'visible' => false,
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
		$filtro = ['', "(NOT r.eliminado = 1 OR r.eliminado IS NULL)"];

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

		return SSP::simple($_POST, $config->database, $table, $this->primaryKey, $columns, $filtro);
	}

	function set($data) {
		global $_DB;

		$rol = [
			'nombre' => $data['rol']['nombre'],
			'orden' => $data['rol']['orden'],
			'parent_id' => $data['rol']['parent_id']?:null,
			'system' => $data['rol']['system']?:null,
			'oculto' => $data['rol']['oculto']?:null,
		];
		$permisos = $data['permisos'];

		$id_rol = null;
		
		if(isset($data["rol"]["id"])) {
			$id_rol = $data["rol"]["id"];
			$message = ($this->update($id_rol, $rol))?'editado':'';
		} else {
			$id_rol = $this->create($rol);
			$message = ($id_rol)?'creado':'';
		}
		if($id_rol){
			$this->permisos_model->setPermisos($id_rol, $permisos);
		}
		return [
			'type' => 'success',
			'title' => 'Rol '.$message,
			'html' => "El rol <b>{$data["rol"]["nombre"]}</b> ha sido {$message} con éxito"
		];
	}

	function setJSON($data) {
		global $_DB;
		$_DB->query("DELETE FROM {$this->table}");
		$_DB->query("DELETE FROM {$this->permisosTable}");

		$insert_roles = [];
		$insert_permisos = [];

		$count = 0;
		foreach ($data as $rol) {
			$count++;

			//id	nombre	orden	eliminado
			$insert_roles[] = [
				'id' => $rol['id'],
				'nombre' => $rol['nombre'],
				'parent_id' => $rol['parent_id'],
				'orden' => $rol['orden'],
				'system' => $rol['system'],
				'oculto' => $rol['oculto']
			];

			//rol_id	recurso_id	permisos_obj
			foreach ($rol['permission'] as $permiso) {
				$insert_permisos[] = [
					'rol_id' => $rol['id'],
					'recurso_id' => $permiso['recurso_id'],
					'permisos_obj' => $permiso['permisos_obj']?json_encode($permiso['permisos_obj']):null
				];
			}

		}

		// var_dump($insert_permisos);exit;

		//Activar insert tabla 1
		$_DB->query("SET IDENTITY_INSERT {$this->table} ON");

		//Insertar tabla 1
		$_DB->query("INSERT INTO {$this->table} ".$this->utils->multipleArrayToInsert($insert_roles, $this->primaryKey));

		//Desactivar insert tabla 1
		$_DB->query("SET IDENTITY_INSERT {$this->table} OFF");

		//Insertar tabla 2 (no tiene autoincrement)
		$_DB->query("INSERT INTO {$this->permisosTable} ".$this->utils->multipleArrayToInsert($insert_permisos));

	}

	function create($rol){
		global $_DB;
		if (!in_array('create', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para crear en este módulo.'
			];
		}

		$id_rol = $_DB->queryToSingleVal("INSERT INTO {$this->table} ".$this->utils->arrayToQuery([
			'action' => "insert", 
			'array' => $rol, 
			'return' => $this->primaryKey
		]));
		return $id_rol;
	}

	function update($id_rol, $rol){
		global $_DB;
		if (!in_array('update', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para actualizar en este módulo.'
			];
		}

		$sql = "UPDATE {$this->table} SET ".$this->utils->arrayToQuery([
			'action' => 'update', 
			'array' => $rol, 
			'where' => "WHERE {$this->primaryKey} = {$id_rol}"
		]);
		return $_DB->query($sql);
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

	public function get($id = null) {
		global $_DB;
		if (!in_array('read', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para eliminar en este módulo.'
			];
		}

		if (!$id) {
			return $_DB->queryToArray(
				"SELECT
					*
				FROM
					{$this->table}");
		}

		$data = $_DB->queryToArray(
			"SELECT
				*
			FROM
				{$this->table}
			WHERE {$this->primaryKey} = {$id}"
		);
		if (!$data) {
			return;
		}
		$data = (object)$data[0];
		$permission = $this->permisos_model->get($id);

		$data->permission = $permission;
		return $data;
	}

	function getChildList($parent) {
		global $_DB;
		if (!$parent) {
			return;
		}
		if (!in_array('read', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para eliminar en este módulo.'
			];
		}

		$data = $_DB->queryToArray(
			"SELECT
				*
			FROM
				{$this->table}
			WHERE parent_id = {$parent}"
		);
		if (!$data) {
			return;
		}
		return $data;
	}

	public function delete($list) {
		global $_DB;
		if (!in_array('delete', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para eliminar en este módulo.'
			];
		}

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