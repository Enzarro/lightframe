<?php

class sys_grid_model {

	var $db;
	var $table = 'sys_grids';
	var $primaryKey = 'grid_id';
	var $tableII = 'sys_grids_fields';
	var $primaryKeyII = 'field_id';
	var $tableIII = 'sys_fields_attrs';
	var $fieldTypes = [
		'int',
		'float',
		'text',
		'check',
		'select',
		'bselect',
		'month',
		'date',
		'time',
		'datetime',
		'dtpicker',
		'rut'
	];
	var $fieldAttributes = [
		'primary' => 'PK',
		'autonum' => 'Autonumeric',
		'notnull' => 'Obligatorio',
		'hidden' => 'Ocultar',
		'hiddenForm' => 'Ocultar (Form)',
		'uppercase' => 'Mayusculas',
		'select-text' => 'Texto (select)',
		'select-subtext' => 'Subtexto (bselect)'
	];

    function __construct() {
		$this->utils = new utils();
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
				'dt' => $dtNum++,
				'db' => "CASE target_schema WHEN 1 THEN 'Sistema' WHEN 2 THEN 'Proyecto' ELSE 'No hay seleccion' END",
				'alias' => 'target_schema',
				'title' => 'Esquema'
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'name',
				//DT
                'title' => 'Nombre'
            ],
            [
				'dt' => $dtNum++,
				'db_pgsql' => "(SELECT jsonb_agg(fields) FROM (SELECT field_id AS id, name, column_name AS column, type FROM {$this->tableII} WHERE {$this->primaryKey} = {$this->table}.{$this->primaryKey}) AS fields)",
				'db_mssql' => "(SELECT field_id AS id, name, column_name AS 'column', type FROM {$this->tableII} WHERE {$this->primaryKey} = {$this->table}.{$this->primaryKey} FOR JSON AUTO)",
				'alias' => 'fields',
				'title' => 'Campos',
				'orderable' => false,
				'searchable' => false,
				'formatter' => function ($data) {
					$data = json_decode($data);
					ob_start(); ?>
					
					<!--<button type="button" class="btn btn-icon btn-pure secondary" data-toggle="popover" data-content="<div class='popover' role='popover'><div class='arrow'></div><div class='popover-header'></div><div class='popover-body'></div></div>" data-original-title="Default Template Structure" data-trigger="hover" data-placement="top" aria-describedby="popover446548">
					<i class="fas fa-eye"></i>
					</button>-->
					
					<div style="overflow: auto; height: 60px;"><?php
					if (is_array($data)) foreach($data as $row) {
						echo "<b>Nombre:</b> ".($row->name).", <b>Tipo:</b> $row->type<br>";
					}
					?></div><?php return ob_get_clean();
				}
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
		// $filtro = "(NOT eliminado = 1 OR eliminado IS NULL)";
        $filtro = null;

		return SSP::simple( $_POST, $config->database, $this->table, $this->primaryKey, $columns, $filtro);
	}
	
	function get($id = null) {
		global $_DB;

		if (!$id) {
			//Listado de grillas
			return $_DB->queryToArray("SELECT * FROM {$this->table}");
		}

		//Grilla según ID
		$grid = $_DB->queryToArray("SELECT {$this->primaryKey} AS id, name, table_name AS 'table', target_schema FROM {$this->table} WHERE {$this->primaryKey} = {$id}");
		//Campos de la grilla
		$grid_fields = $_DB->queryToArray("SELECT {$this->primaryKeyII} AS id, name, column_name AS 'column', type, origin FROM {$this->tableII} WHERE {$this->primaryKey} = {$id}");
		//ID's de los campos de la grilla
		$fields_ids = array_column($grid_fields, 'id');
		//Atributos de los campos de la grilla por id de campo
		$grid_fields_attrs = $_DB->queryToArray("SELECT field_id, attr FROM {$this->tableIII} WHERE {$this->primaryKeyII} IN ".$this->utils->arrayToQuery('in', $fields_ids));
		//Convertir registro de grilla en objeto
		$grid = (object)$grid[0];
		//Arreglo de campos de objeto grilla
		$grid->fields = array_map(function($field) use ($grid_fields_attrs) {
			//Convertir cada campo en objeto
			$field = (object)$field;
			//Extraer atributos de campo
			$field->attr = array_column(array_filter($grid_fields_attrs, function($attr) use ($field) {
				return $attr['field_id'] == $field->id; 
			}), 'attr');
			//Retornar objeto campo
			return $field;
		}, $grid_fields);
		//Retornar objeto final
		return $grid;
	}

	/**
	 * attr: primary || text || select-text || select-subtext
	 */
	function getSelectColFromObj($obj, $attr) {
		if ($attr == 'primary') {
			$foundField = array_values(array_filter($obj->fields, function($field) {
				return in_array('primary', $field->attr);
			}));
		} else if ($this->utils->endsWith($attr, 'text')) {
			$foundField = array_values(array_filter($obj->fields, function($field) {
				return in_array('select-text', $field->attr);
			}));
			$firstTextField = array_values(array_filter($obj->fields, function($field) {
				return in_array($field->type, ['text', 'rut']);
			}));
		} else {
			return false;
		}
		
		if ($foundField) {
			$foundField = $foundField[0];
			return $foundField->column;
		} else if ($firstTextField && $this->utils->endsWith($attr, 'text')) {
			$firstTextField = $firstTextField[0];
			return $firstTextField->column;
		} else {
			return false;
		}
	}

	// function getSelectValueColFromObj($obj) {
	// 	$foundField = array_values(array_filter($obj->fields, function($field) {
	// 		return in_array('primary', $field->attr);
	// 	}));
	// 	if ($foundField) {
	// 		$foundField = $foundField[0];
	// 		return $foundField->name;
	// 	} else {
	// 		return false;
	// 	}
	// }
	
	function set($data) {
		global $_DB;
		global $config;
		if (isset($data['id']) && $data['id']) {
			//:: UPDATE ::
			//Grid
			$obj_grid = [
				'name' => $data['name'],
				'table_name' => $data['table'],
				'target_schema' => $data['target_schema']
			];
			$obj_grid_id = $_DB->queryToSingleVal("UPDATE {$this->table} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $obj_grid, 'where' => " WHERE {$this->primaryKey} = {$data['id']}", 'return' => $this->primaryKey]));
			
			//Grid Fields
			$obj_grid_fields_id = [];

			$obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
				$newfield = [
					"{$this->primaryKey}" => $obj_grid_id,
					'name' => $field['name'],
					'column_name' => $field['column'],
					'type' => $field['type'],
					'origin' => $field['origin']
				];
				if ($field['id']) {
					$newfield[$this->primaryKeyII] = $field['id'];
				}
				return $newfield;
			}, $data['fields']);

			//Update
			$obj_grid_fields_update = array_values(array_filter($obj_grid_fields, function($field) {
				return array_key_exists($this->primaryKeyII, $field);
			}));
			if ($obj_grid_fields_update) {
				foreach ($obj_grid_fields_update as $field) {
					$field_id = $field[$this->primaryKeyII];
					unset($field[$this->primaryKeyII]);
					$_DB->queryToSingleVal("UPDATE {$this->tableII} SET ".$this->utils->arrayToQuery('update', $field)." WHERE {$this->primaryKeyII} = {$field_id}");
					$obj_grid_fields_id[] = [
						"{$this->primaryKeyII}" => $field_id
					];
				}
			}

			//Insert
			$obj_grid_fields_insert = array_values(array_filter($obj_grid_fields, function($field) {
				return !array_key_exists($this->primaryKeyII, $field);
			}));
			if ($obj_grid_fields_insert) {
				$obj_grid_fields_insert = array_map(function($field) {
					unset($field[$this->primaryKeyII]);
					return $field;
				}, $obj_grid_fields_insert);
				$insert_res = $_DB->queryToArray("INSERT INTO {$this->tableII} ".$this->utils->multipleArrayToInsert($obj_grid_fields_insert, $this->primaryKeyII));
				foreach ($insert_res as $inserted) {
					$obj_grid_fields_id[] = $inserted;
				}
			}

			//UPSERT SYNTAX
			// $updateParams = [
			// 	"{$this->primaryKey}" => "excluded.{$this->primaryKey}",
			// 	'name' => 'excluded.name',
			// 	'column_name' => 'excluded.column_name',
			// 	'type' => 'excluded.type',
			// 	'origin' => 'excluded.origin'
			// ];
			// $obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
			// 	return [
			// 		"{$this->primaryKeyII}" => $field['id']?$field['id']:'DEFAULT',
			// 		"{$this->primaryKey}" => $obj_grid_id,
			// 		'name' => $field['name'],
			// 		'column_name' => $field['column'],
			// 		'type' => $field['type'],
			// 		'origin' => $field['origin']
			// 	];
			// }, $data['fields']);

			// $obj_grid_fields_id = $_DB->queryToArray("INSERT INTO {$this->tableII} ".$this->utils->multipleArrayToInsert($obj_grid_fields)." ON CONFLICT ({$this->primaryKeyII}) DO UPDATE SET ".$this->utils->arrayToQuery('update', $updateParams)." RETURNING {$this->primaryKeyII}");
			//UPSERT SYNTAX

			if ($obj_grid_fields_id) {
				if ($config->database->type == "mssql") {
					$deleted_grid_fields = $_DB->queryToArray("DELETE FROM {$this->tableII} ".$this->utils->returnToQuery($this->primaryKeyII, 'deleted')." WHERE {$this->primaryKey} = {$obj_grid_id} AND {$this->primaryKeyII} NOT IN ".$this->utils->arrayToQuery('in', array_column($obj_grid_fields_id, $this->primaryKeyII)));
				} else if ($config->database->type == "pgsql") {
					$deleted_grid_fields = $_DB->queryToArray("DELETE FROM {$this->tableII} WHERE {$this->primaryKey} = {$obj_grid_id} AND {$this->primaryKeyII} NOT IN ".$this->utils->arrayToQuery('in', array_column($obj_grid_fields_id, $this->primaryKeyII))." ".$this->utils->returnToQuery($this->primaryKeyII, 'deleted'));
				}
				
				if ($deleted_grid_fields) {
					$_DB->queryToArray("DELETE FROM {$this->tableIII} WHERE {$this->primaryKeyII} IN ".$this->utils->arrayToQuery('in', array_column($deleted_grid_fields, $this->primaryKeyII)));
				}
			}
			
			
			//Field Attrs
			$deleteAttrs = [];
			$insertAttrs = [];
			foreach(array_keys($data['fields']) as $keyf) {
				//Si la llave attr está seteada
				if (isset($data['fields'][$keyf]['attr'])) {
					//Arreglo para insert
					$obj_field_attrs = array_map(function($row) use ($keyf, $obj_grid_fields_id) {
						return [
							"{$this->primaryKeyII}" => $obj_grid_fields_id[$keyf][$this->primaryKeyII],
							'attr' => $row
						];
					}, array_unique($data['fields'][$keyf]['attr']));
					//Eliminar existentes
					$deleteAttrs = array_merge($deleteAttrs, array_column($obj_field_attrs, $this->primaryKeyII));
					//Insertar
					$insertAttrs = array_merge($insertAttrs, $obj_field_attrs);
				} else {
					//Eliminar existentes
					$deleteAttrs = array_merge($deleteAttrs, [$obj_grid_fields_id[$keyf][$this->primaryKeyII]]);
				}
			}

			//Delete Attrs
			if ($deleteAttrs) {
				$deleteAttrs = array_unique($deleteAttrs);
				$qryDelete = "DELETE FROM {$this->tableIII} WHERE {$this->primaryKeyII} IN ".$this->utils->arrayToQuery('in', $deleteAttrs);
				$_DB->query($qryDelete);
			}

			//Insert Attrs
			if ($insertAttrs) {
				$qryInsert = "INSERT INTO {$this->tableIII} ".$this->utils->multipleArrayToInsert($insertAttrs);
				$_DB->query($qryInsert);
				// error_log($qryInsert);
			}

			//Success
			return [
				'type' => 'success',
				'title' => 'Cambios guardados',
				'text' => 'Los cambios fueron guardados con éxito'
			];
			
		} else {
			//:: NEW ::
			//Grid
			$obj_grid = [
				'name' => $data['name'],
				'table_name' => $data['table'],
				'target_schema' => $data['target_schema']
			];
			$obj_grid_id = $_DB->queryToSingleVal("INSERT INTO {$this->table} ".$this->utils->arrayToQuery(['action' => 'insert', 'array' => $obj_grid, 'return' => $this->primaryKey]));
			//Grid Fields
			$obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
				return [
					"{$this->primaryKey}" => $obj_grid_id,
					'name' => $field['name'],
					'column_name' => $field['column'],
					'type' => $field['type']
				];
			}, $data['fields']);
			$obj_grid_fields_id = $_DB->queryToArray("INSERT INTO {$this->tableII} ".$this->utils->multipleArrayToInsert($obj_grid_fields, $this->primaryKeyII));
			//Field Attrs
			foreach(array_keys($data['fields']) as $keyf) {
				if (isset($data['fields'][$keyf]['attr'])) {
					$obj_field_attrs = array_map(function($row) use ($keyf, $obj_grid_fields_id) {
						return [
							"{$this->primaryKeyII}" => $obj_grid_fields_id[$keyf][$this->primaryKeyII],
							'attr' => $row
						];
					}, array_unique($data['fields'][$keyf]['attr']));
					$_DB->query("INSERT INTO {$this->tableIII} ".$this->utils->multipleArrayToInsert($obj_field_attrs));
				}
			}

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
		$_DB->query("DELETE FROM sys_fields_attrs WHERE field_id IN (SELECT field_id FROM sys_grids_fields WHERE grid_id IN ".$this->utils->arrayToQuery('in', $list).")");
		$_DB->query("DELETE FROM sys_grids_fields WHERE grid_id IN ".$this->utils->arrayToQuery('in', $list));
		$_DB->query("DELETE FROM sys_grids WHERE grid_id IN ".$this->utils->arrayToQuery('in', $list));
		return [
            'type' => 'success',
            'title' => 'Registros eliminados',
            'text' => 'Se eliminaron '.count($list).' registros!'
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
				'title' => "Nombre",
				'data' => 'name',
				'editType' => 'string'
            ],
            [
				'targets' => $dtNum++,
				'title' => "Columna",
				'data' => 'column',
				'editType' => 'string'
            ],
            [
				'targets' => $dtNum++,
				'title' => "Tipo",
				'data' => 'type',
				'width' => "150px",
				'editType' => 'select',
				// 'editConfig' => [
				// 	"efields" => [
				// 		"int" => ["attr"],
				// 		"float" => ["attr"],
				// 		"text" => ["attr"],
				// 		"check" => ["attr"],
				// 		"select" => ["attr", "origen"],
				// 		"bselect" => ["attr", "origen"],
				// 		"dtpicker" => ["attr"],
				// 		"rut" => ["attr"]
				// 	]
				// ],
				'editData' => array_map(function($row) {
					return ['id' => $row, 'text' => $row];
				}, $this->fieldTypes)
			],
			[
				'targets' => $dtNum++,
				'title' => "Origen",
				'data' => 'origin',
				'width' => "85px",
				// 'visible' => false,
				'editType' => 'bselect',
				'editConfig' => [
					'liveSearch' => true,
					'width' => '100px'
				],
				'editData' => array_merge([["id" => "", "text" => ""]], $this->getGridCboList())
			],
			[
				'targets' => $dtNum++,
				'title' => "Atributos",
				'data' => 'attr',
				// 'visible' => false,
				'editType' => 'bselect',
				'width' => "185px",
				'editConfig' => [
					'liveSearch' => true,
					'width' => '200px',
					//'selectedTextFormat' => 'count',
					'multiple' => true
				],
				'editData' => array_map(function($key, $val) {
					return ['id' => $key, 'text' => $val];
				}, array_keys($this->fieldAttributes), $this->fieldAttributes)
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
		$columns = $this->getCamposDTConfig();
		$columns = array_values(array_filter($columns, function($col) {
			return $col['data'] != null;
		}));
		$finalRow = [];
		foreach ($columns as $col) {
			$finalRow[$col['data']] = null;
		}
		$finalRow['estado'] = 'edit';
		return $finalRow;
        // return [
        //     'id' => null,
        //     'name' => null,
        //     'column' => null,
		// 	'type' => null,
		// 	'origin' => null,
		// 	'attr' => null,
        //     'estado' => 'edit'
        // ];
	}
	
	function getGridCboList() {
		global $_DB;
		return $_DB->queryToArray("SELECT {$this->primaryKey} AS id, name AS text FROM {$this->table}");
	}
	
	function consolidate($id, $schema = null) {
		global $_DB;
		global $config;

		if ($config->database->type == 'pgsql') {
			if (!$schema) {
				$schema = 'public';
				$_DB->query("SET search_path TO public");
			} else {
				$_DB->query("SET search_path TO public, {$schema}");
			}
		} else if ($config->database->type == 'mssql') {
			if (!$schema) {
				$schema = 'dbo';
			}
		}

		//Traer datos de grilla
		$data = $this->get($id);

		$columns = [];
		foreach ($data->fields as $field) {
			$columns[$field->column] = $field->attr;
			//Varchar
			if (in_array($field->type, ['rut', 'text'])) {
				$columns[$field->column][] = 'varchar';
			}
			if (in_array($field->type, ['int', 'select', 'bselect', 'check'])) {
				$columns[$field->column][] = 'int';
			}
			if (in_array($field->type, ['float'])) {
				$columns[$field->column][] = 'float';
			}
			if (in_array($field->type, ['dtpicker', 'datetime'])) {
				$columns[$field->column][] = 'timestamp';
			}
			if (in_array($field->type, ['date', 'month'])) {
				$columns[$field->column][] = 'date';
			}
			if (in_array($field->type, ['time'])) {
				$columns[$field->column][] = 'time';
			}
		}

		//Crear tabla
		$this->utils->arrayToTable([
			'table' => $data->table,
			'schema' => $schema,
            'columnDefs' => $columns,
            'delete' => false,
            'duplicate' => false
		]);
		
		return [
			'type' => 'success',
			'text' => 'La tabla ha sido consolidada'
		];
	}
}