<?php

class users_model {

	function __construct() {
		$this->utils = new utils();
		// $this->frame_view = new frame_view();
	}
	

    function list() {
		global $config;
		$table = 'usuarios';
		$primaryKey = 'usuario_id';

		$dtNum = 0;
		$columns = [
			[
				//DB
				'dt' => $dtNum++,
				'db' => $primaryKey,
				//DT
				'title' => 'ID',
				'searchable' => false,
				'visible' => false
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'nombre',
				'formatter' => function ($d) {
					return utf8_decode($d);
				},
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

		return SSP::simple($_POST, $config->database, $table, $primaryKey, $columns, $filtro);
	}

	function set($data) {
		global $config;
		$_DB = new database($config->database);
		$resources_data = [];
		$db_data = [
			'username' => $data["usuario"]["user"],
			'password' => $data["usuario"]["pass"],
			'nombre' => $data["usuario"]["name"],
			'correo' => $data["usuario"]["email"],
			'avatar' => null
		];
		if(isset($data["usuario"]["id"])) {
			$message = 'editado';
			$_DB->query(
				"UPDATE usuarios SET ".$this->utils->arrayToQuery('update', $db_data)." WHERE usuario_id = {$data["usuario"]["id"]}"
			);
			$_DB->query(
				"DELETE FROM permisos WHERE usuario_id = {$data["usuario"]["id"]}"
			);
			if($data["permisos"]) {	
				foreach ($data["permisos"] as $key=>$permisos) {
					if(is_array($permisos)) {
						$resources_data[] = [
							'usuario_id' => $data["usuario"]["id"],
							'recurso_id' => $key,
							'permisos_obj' => json_encode($permisos)
						];
					}
				}
				$_DB->query("INSERT INTO permisos ".$this->utils->multipleArrayToInsert($resources_data));
			}
		} else {
			$message = 'creado';
			$user_id = $_DB->queryToArray("INSERT INTO usuarios ".$this->utils->arrayToQuery("insert", $db_data). "RETURNING usuario_id");
			if($data["permisos"]) {	
				foreach ($data["permisos"] as $key=>$permisos) {
					if(is_array($permisos)) {
						$resources_data[] = [
							'usuario_id' => $user_id[0]["usuario_id"],
							'recurso_id' => $key,
							'permisos_obj' => json_encode($permisos)
						];
					}
				}
				$_DB->query("INSERT INTO permisos ".$this->utils->multipleArrayToInsert($resources_data));
			}
		}
		return [
			'type' => 'success',
			'title' => 'Usuario '.$message,
			'text' => 'El usuario '.$data["usuario"]["name"].' ha sido '.$message.' con éxito'
		]; 
	}

	public function load_resources() {
		global $config;
		$_DB = new database($config->database);
		$res = $_DB->queryToArray(
			"SELECT 
				recurso_id AS id, 
				parent_id,
				texto, 
				icono,
				funcion,
				0 AS activo,
				permisos_obj
			FROM recursos
			WHERE eliminado IS NULL"
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
		global $config;
		$_DB = new database($config->database);
		$personal_data = $_DB->queryToArray(
			"SELECT 
				username,
				password,
				nombre,
				correo,
				avatar
			FROM
				usuarios
			WHERE usuario_id = {$id}"
		);
		$permission = $_DB->queryToArray(
			"SELECT 
				recurso_id,
				permisos_obj
			FROM
				permisos
			WHERE usuario_id = {$id}"
		);
		$data = [
			'personal_data' => $personal_data,
			'permission' => $permission
		];
		return $data;
	}

	public function delete($list) {
		global $config;
        $_DB = new database($config->database);
        $data = [
            'eliminado' => 1
        ];
        $_DB->queryToArray("UPDATE usuarios SET ".$this->utils->arrayToQuery('update', $data)." WHERE usuario_id IN ".$this->utils->arrayToQuery('in', $list));
        return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
        ];
	}

}