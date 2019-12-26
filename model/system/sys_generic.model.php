<?php

class sys_generic_model {

    var $db;
	var $primaryKey;

    function __construct($resource, $object) {
		$this->resource = $resource;
		$this->object = $object;
		$this->utils = new utils();
		$this->sys_clients_model = new sys_clients_model();
		$this->sys_grid_model = new sys_grid_model();
		$this->frame_model = new frame_model();
		$this->login_model = new login_model();

		//Primary key
		foreach($this->object->fields as $field) {
			if (in_array('primary', $field->attr)) {
				$this->primaryKey = $field->column;
			}
		}
    }

	function list() {
		global $config;
		global $client;

		$dtNum = 0;
		$columns = [];
		//Columns
		foreach($this->object->fields as $field) {
			$column = [
				'dt' => $dtNum++,
				'db' => $field->column,
				'title' => $field->name
			];
			if (in_array('hidden', $field->attr)) {
				$column['visible'] = false;
			}
			if (!isset($_POST["config"])) {
				//Campos de tipo texto decodificar utf8
				/*if ($field->type == "text") {
					$column['formatter'] = function($d) {
						ob_start(); ?>
						<div style="overflow: auto; height: 34px;">
							<?=utf8_decode($d)?>
						</div>
						<?php return ob_get_clean();
					};
				}*/
				//Campos de tipo select ir a buscar valor a grilla asociada
				if (in_array($field->type, ['select', 'bselect']) && $field->origin) {
					$objOrigin = $this->sys_grid_model->get($field->origin);
					if ($objOrigin->target_schema == 2) {
						$objOrigin->table = "{$client->db_name}.{$objOrigin->table}";
					} else if ($objOrigin->target_schema == 1) {
						if ($config->database->type == "pgsql") {
							$objOrigin->table = "public.{$objOrigin->table}";
						} else if ($config->database->type == "mssql") {
							$objOrigin->table = "dbo.{$objOrigin->table}";
						}
					}
					$primary = $this->sys_grid_model->getSelectColFromObj($objOrigin, 'primary');
					$text = $this->sys_grid_model->getSelectColFromObj($objOrigin, 'select-text');
					
                    if ($this->object->table == $objOrigin->table) {
                        $column['alias'] = $column['db'];
                        $column['db'] = "(SELECT {$objOrigin->table}_self.{$text} FROM {$objOrigin->table} AS {$objOrigin->table}_self WHERE {$objOrigin->table}_self.{$primary} = {$this->object->table}.{$field->column})";
                    } else {
                        $column['alias'] = $column['db'];
					    $column['db'] = "(SELECT {$text} FROM {$objOrigin->table} WHERE {$objOrigin->table}.{$primary} = {$this->object->table}.{$field->column})";
                    }
					/*$column['formatter'] = function($d) {
						ob_start(); ?>
						<div style="overflow: auto; height: 34px;">
							<?=utf8_decode($d)?>
						</div>
						<?php return ob_get_clean();
					};*/
				}
			}
			
			$columns[] = $column;
		}

		$columns[] = [
			//DT
			'dt' => $dtNum++,
			'db' => "''",
			'alias' => 'actions',
			'formatter' => function( $d, $row ) {
				ob_start(); ?>
					<div class="btn-group btn-group" role="group" style="width: auto;">
						<button class="btn btn-success main-edit" title="Editar registro" type="button"><span aria-hidden="true" class="fas fa-edit"></span></button>
					</div>
				<?php return ob_get_clean();
			},
			'title' => 'Acciones',
			"responsivePriority" => 2,
			"orderable" => false,
			"width" => "80px",
			"searchable" => false
		];
		$columns[] = [
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
		];

		//Filtro: Contenido de cláusula WHERE, también puede contener JOIN
		// $filtro = "(NOT eliminado = 1 OR eliminado IS NULL)";
        $filtro = null;
        
        if (!isset($_POST["config"])) {
			$failed = false;
			if ($this->object->target_schema == 2) {
				if (!$_POST["client"] || !$this->sys_clients_model->get($_POST["client"]) || !in_array('read', $this->resource['permisos_user_obj'])) {
					$failed = true;
				}
			} else if ($this->object->target_schema == 1) {
				if (!in_array('read', $this->resource['permisos_user_obj'])) {
					$failed = true;
				}
			}
			if ($failed) {
				return [
					"draw"            => intval( $_POST['draw'] ),
					"recordsTotal"    => 0,
					"recordsFiltered" => 0,
					"data"            => []
			   ];
			}
		}

		return SSP::simple($_POST, $config->database, $this->object->table, $this->primaryKey, $columns, $filtro);
	}
	
	function get($id) {
		global $_DB;
		
		$fields = $this->object->fields;
        
		//Quitar los (el) campo de llave primaria
        // $fields = array_filter($this->object->fields, function($field) {
        //     return !in_array('primary', $field->attr);
        // });

		//Columns
		$columns = array_column($fields, 'column');

		//Data según ID
		$data = $_DB->queryToArray("SELECT ".implode(', ', $columns)." FROM {$this->object->table} WHERE {$this->primaryKey} = '{$id}'");
		//Convertir registro en objeto
		$data = (object)$data[0];
		//Retornar objeto final
		return $data;
	}

	function getGridCbo($id) {
		global $_DB;
		global $client;
		global $config;
		//Get table name
		$grid = $this->sys_grid_model->get($id);
		if ($grid->target_schema == 2) {
			$grid->table = "{$client->db_name}.{$grid->table}";
		} else if ($grid->target_schema == 1) {
			$grid->table = "{$_DB->schema}.{$grid->table}";
		}
		$primary = $this->sys_grid_model->getSelectColFromObj($grid, 'primary');
		$text = $this->sys_grid_model->getSelectColFromObj($grid, 'select-text');
        // $table = $_DB->queryToSingleVal("SELECT table_name FROM {$this->sys_grid_model->table} WHERE grid_id = {$id}");
        $gridData = $_DB->queryToArray("SELECT {$primary} AS id, {$text} AS name FROM {$grid->table}");
        $data = array_map(function ($row) {
            return array_values($row);
        }, $gridData?$gridData:[]);
        return $data;
	}

	function getGridCboSingleVal($id, $value) {
		global $_DB;
		$grid = $this->sys_grid_model->get($id);
		if ($grid->target_schema == 2) {
			$grid->table = "{$client->db_name}.{$grid->table}";
		} else if ($grid->target_schema == 1) {
			$grid->table = "{$_DB->schema}.{$grid->table}";
		}
		$primary = $this->sys_grid_model->getSelectColFromObj($grid, 'primary');
		$text = $this->sys_grid_model->getSelectColFromObj($grid, 'select-text');
		//Get table name
		// $table = $_DB->queryToSingleVal("SELECT table_name FROM {$_DB->schema}.{$this->sys_grid_model->table} WHERE grid_id = {$id}");
		return $_DB->queryToSingleVal("SELECT {$text} FROM {$grid->table} WHERE {$primary} = '{$value}'");
	}
	
	function set($data) {
        global $_DB;

		//Quitar los (el) campo de llave primaria
        $fields = array_filter($this->object->fields, function($field) {
            return !in_array('primary', $field->attr) &&  !in_array('hiddenForm', $field->attr);
        });

		//Columns
		$columns = array_column($fields, 'column');
		$obj_set = [];
		foreach($fields as $field) {
			if (isset($data[$field->column])) {
				if(in_array('uppercase', $field->attr)){
					$obj_set[$field->column] = strtoupper($data[$field->column]);
				}else{
					$obj_set[$field->column] = $data[$field->column];
				}
			}
		}
		if (isset($data['id']) && $data['id']) {
			if (!in_array('update', $this->resource['permisos_user_obj'])) {
				return [
					'type' => 'error',
					'text' => 'No tiene permisos para actualizar en este módulo.'
				];
			}
			//:: UPDATE ::
			$obj_set_id = $_DB->queryToSingleVal("UPDATE {$this->object->table} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $obj_set, 'where' => "WHERE {$this->primaryKey} = {$data['id']}", 'return' => $this->primaryKey]));
			if ($obj_set_id) {
				$this->frame_model->setHistory($this->object->table, $obj_set_id, 2);
				return [
					'type' => 'success',
					'text' => 'Registro actualizado correctamente.'
				];
			}
		} else {
			if (!in_array('create', $this->resource['permisos_user_obj'])) {
				return [
					'type' => 'error',
					'text' => 'No tiene permisos para crear en este módulo.'
				];
			}
			//:: NEW ::
			$obj_set_id = $_DB->queryToSingleVal("INSERT INTO {$this->object->table} ".$this->utils->arrayToQuery(['action' => 'insert', 'array' => $obj_set, 'return' => $this->primaryKey]));
			$this->frame_model->setHistory($this->object->table, $obj_set_id, 1);
			return [
				'type' => 'success',
				'text' => 'Registro insertado correctamente.'
			];
		}
	}

    function delete($list) {
		global $_DB;
		if (!in_array('delete', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para eliminar en este módulo.'
			];
		}
		//Search "eliminar" column
		//si no encuentra la columna, elimina en duro el registro de la bd
		$hardDelete = true;
		foreach($this->object->fields as $field) {
			if ($field->column == 'eliminar') $hardDelete = false;
		}
		if ($hardDelete) {
			$_DB->queryToSingleVal("DELETE FROM {$this->object->table} WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $list));
		} else {
			$_DB->queryToSingleVal("UPDATE {$this->object->table} SET eliminado = 1 WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $list));
		}
		return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
        ];
    }

}