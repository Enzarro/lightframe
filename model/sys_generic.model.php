<?php

class sys_generic_model {

    var $db;
	var $fieldTypes = ['int', 'text', 'check', 'select', 'bselect', 'dtpicker', 'rut'];
	var $fieldAttributes = ['primary' => 'PK', 'autonum' => 'Autonumeric', 'notnull' => 'Obligatorio', 'hidden' => 'Ocultar'];

    function __construct() {
		$this->utils = new utils();
        if (file_exists(root."/grids.json")) {
            $this->grids = json_decode(file_get_contents(root."/grids.json"));
        }
        if (!isset($this->grids)) {
            $this->grids = [];
        }
    }

	function list($object) {
		global $config;

		$primaryKey = '';
		//Primary key
		foreach($object->fields as $field) {
			if (in_array('primary', $field->attr)) {
				$primaryKey = $field->column;
			}
		}

		$dtNum = 0;
		$columns = [];
		//Columns
		foreach($object->fields as $field) {
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
				if ($field->type == "text") {
					$column['formatter'] = function($d) {
						ob_start(); ?>
						<div style="overflow: auto; height: 34px;">
							<?=utf8_decode($d)?>
						</div>
						<?php return ob_get_clean();
					};
				}
				//Campos de tipo select ir a buscar valor a grilla asociada
				if (in_array($field->type, ['select', 'bselect']) && $field->origin) {
					$tableCbo = $this->getGridCboTableName($field->origin);
					$column['alias'] = $column['db'];
					$column['db'] = "(SELECT name FROM {$tableCbo} WHERE id = {$object->table}.{$field->column})";
					$column['formatter'] = function($d) {
						ob_start(); ?>
						<div style="overflow: auto; height: 34px;">
							<?=utf8_decode($d)?>
						</div>
						<?php return ob_get_clean();
					};
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
						<button class="btn btn-success main-edit" title="Editar registro" type="button"><span aria-hidden="true" class="fa fa-pencil"></span></button>
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

		return SSP::simple($_POST, $config->database, $object->table, $primaryKey, $columns, $filtro);
	}
	
	function get($id, $object) {
		global $config;
		$_DB = new database($config->database);

		$primaryKey = '';
		//Primary key
		foreach($object->fields as $field) {
			if (in_array('primary', $field->attr)) {
				$primaryKey = $field->column;
			}
		}

		//Quitar los (el) campo de llave primaria
        $fields = array_filter($object->fields, function($field) {
            return !in_array('primary', $field->attr);
        });

		//Columns
		$columns = array_column($fields, 'column');

		//Data según ID
		$data = $_DB->queryToArray("SELECT ".implode(', ', $columns)." FROM {$object->table} WHERE {$primaryKey} = {$id}");
		//Convertir registro en objeto
		$data = (object)$data[0];
		//Retornar objeto final
		return $data;
	}

	function getGridCbo($id) {
		global $config;
		$_DB = new database($config->database);
		//Get table name
		$table = $_DB->queryToSingleVal("SELECT table_name FROM grids WHERE grid_id = {$id}");
		$data = array_map(function ($row) {
			return array_values($row);
		}, $_DB->queryToArray("SELECT id, name AS text FROM {$table}"));
		return $data;
	}

	function getGridCboSingleVal($id, $value) {
		global $config;
		$_DB = new database($config->database);
		//Get table name
		$table = $_DB->queryToSingleVal("SELECT table_name FROM grids WHERE grid_id = {$id}");
		return $_DB->queryToSingleVal("SELECT name FROM {$table} WHERE id = {$value}");
	}

	function getGridCboTableName($id) {
		global $config;
		$_DB = new database($config->database);
		//Get table name
		return $_DB->queryToSingleVal("SELECT table_name FROM grids WHERE grid_id = {$id}");
	}
	
	function set($data, $object) {
		global $config;
		$_DB = new database($config->database);

		$primaryKey = '';
		//Primary key
		foreach($object->fields as $field) {
			if (in_array('primary', $field->attr)) {
				$primaryKey = $field->column;
			}
		}

		//Quitar los (el) campo de llave primaria
        $fields = array_filter($object->fields, function($field) {
            return !in_array('primary', $field->attr);
        });

		//Columns
		$columns = array_column($fields, 'column');

		$obj_set = [];
		foreach($columns as $column) {
			if (isset($data[$column])) {
				$obj_set[$column] = $data[$column];
			}
		}

		if (isset($data['id']) && $data['id']) {
			//:: UPDATE ::
			$obj_set_id = $_DB->queryToSingleVal("UPDATE {$object->table} SET ".$this->utils->arrayToQuery('update', $obj_set)." WHERE {$primaryKey} = {$data['id']} RETURNING {$primaryKey}");
			if ($obj_set_id) {
				return [
					'type' => 'success',
					'text' => 'Registro actualizado correctamente.'
				];
			}
		} else {
			//:: NEW ::
			$obj_set_id = $_DB->queryToSingleVal("INSERT INTO {$object->table} ".$this->utils->arrayToQuery('insert', $obj_set)." RETURNING {$primaryKey}");
			return [
				'type' => 'success',
				'text' => 'Registro insertado correctamente.'
			];
		}
	}

    function delete($list) {
		$grids = $this->grids;
		$grids = array_filter($grids, function($row) use ($list) {
			return !in_array($row->id, $list); 
		});
		return $this->setFile($grids);
    }

}