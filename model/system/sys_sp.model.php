<?php

class sys_sp_model {

	var $db;
	var $table = 'information_schema.tables';
	var $primaryKey = 'table_name';
	var $schema;

    function __construct() {
		global $config;
		if ($config->database->type == "pgsql") {
			$this->schema = 'public';
		} else if ($config->database->type == "mssql") {
			$this->schema = 'dbo';
		}
		
		$this->utils = new utils();
		$this->sys_clients_model = new sys_clients_model();
        if (file_exists(root."/grids.json")) {
            $this->grids = json_decode(file_get_contents(root."/grids.json"));
        }
        if (!isset($this->grids)) {
            $this->grids = [];
        }
    }

	function list() {
		global $config;

		if (isset($_POST['client']) && $_POST['client']) {
			$client = $this->sys_clients_model->get($_POST['client']);
        	$this->schema = $client->db_name;
		}

		$schema = $this->schema;

		$dtNum = 0;
		$columns = [
			[
				//DB
				'dt' => $dtNum++,
				'db' => $this->primaryKey,
				//DT
				'title' => 'Tabla'
			],
            [
				'dt' => $dtNum++,
				'db_pgsql' => "(SELECT jsonb_agg(fields) FROM (SELECT table_name, column_name, udt_name FROM information_schema.columns WHERE table_schema = '{$this->schema}' AND table_name = information_schema.tables.table_name) AS fields)",
				'db_mssql' => "(SELECT table_name, column_name, data_type AS udt_name FROM information_schema.columns WHERE table_schema = '{$this->schema}' AND table_name = information_schema.tables.table_name FOR JSON AUTO)",
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
					foreach($data as $row) {
						echo "<b>Nombre:</b> ".utf8_decode($row->column_name).", <b>Tipo:</b> $row->udt_name<br>";
					}
					?></div><?php return ob_get_clean();
				}
			],
			[
				'dt' => $dtNum++,
				'db' => "''",
				'alias' => 'count',
				'title' => 'Registros',
				'formatter' => function($data, $row) use ($schema) {
					global $_DB;
					return $_DB->queryToSingleVal("SELECT COUNT(*) FROM {$schema}.{$row['table_name']}");
				},
				'orderable' => false,
				'searchable' => false
			]
		];

		//Filtro: Contenido de cláusula WHERE, también puede contener JOIN
		$filtro = "table_schema = '$this->schema'";

		if (isset($_POST['config'])) {
			return SSP::simple( $_POST, $config->database, $this->table, $this->primaryKey, $columns, $filtro);
		} else {
			return [
				"draw"            => intval( $_POST['draw'] ),
				"recordsTotal"    => 0,
				"recordsFiltered" => 0,
				"data"            => []
			];
		}
		
	}

	// function load_files($target) {
    //     if(is_dir($target)) {
    //         $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
    //         foreach( $files as $file ) {
    //             $this->load_files( $file );
    //         }
    //     } elseif(is_file($target)) {
    //         $this->load($target);
    //     }
    // }
	
	function get($id) {
		global $_DB;

		//Grilla según ID
		$grid = $_DB->queryToArray("SELECT {$this->primaryKey} AS id, name, table_name AS table FROM {$this->table} WHERE {$this->primaryKey} = {$id}");
		//Campos de la grilla
		$grid_fields = $_DB->queryToArray("SELECT {$this->primaryKeyII} AS id, name, column_name AS column, type, origin FROM {$this->tableII} WHERE {$this->primaryKey} = {$id}");
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

    function getObj($id) {
        $filtered = array_values(array_filter($this->grids, function($row) use ($id) {
            return $row->id == $id;
        }));
        if ($filtered) {
            return $filtered[0];
        } else {
            return [];
        }
	}
	
	function set($data) {
		global $_DB;
		if (isset($data['id']) && $data['id']) {
			//:: UPDATE ::
			//Grid
			$obj_grid = [
				'name' => $data['name'],
				'table_name' => $data['table']
			];
			$obj_grid_id = $_DB->queryToSingleVal("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $obj_grid)." WHERE {$this->primaryKey} = {$data['id']} RETURNING {$this->primaryKey}");
			
			//Grid Fields
			$updateParams = [
				"{$this->primaryKey}" => "excluded.{$this->primaryKey}",
				'name' => 'excluded.name',
				'column_name' => 'excluded.column_name',
				'type' => 'excluded.type',
				'origin' => 'excluded.origin'
			];
			$obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
				return [
					"{$this->primaryKeyII}" => $field['id']?$field['id']:'DEFAULT',
					"{$this->primaryKey}" => $obj_grid_id,
					'name' => $field['name'],
					'column_name' => $field['column'],
					'type' => $field['type'],
					'origin' => $field['origin']
				];
			}, $data['fields']);

			$obj_grid_fields_id = $_DB->queryToArray("INSERT INTO {$this->tableII} ".$this->utils->multipleArrayToInsert($obj_grid_fields)." ON CONFLICT ({$this->primaryKeyII}) DO UPDATE SET ".$this->utils->arrayToQuery('update', $updateParams)." RETURNING {$this->primaryKeyII}");
			if ($obj_grid_fields_id) {
				$deleted_grid_fields = $_DB->queryToArray("DELETE FROM {$this->tableII} WHERE {$this->primaryKey} = {$obj_grid_id} AND {$this->primaryKeyII} NOT IN ".$this->utils->arrayToQuery('in', array_column($obj_grid_fields_id, $this->primaryKeyII))." RETURNING {$this->primaryKeyII}");
				if ($deleted_grid_fields) {
					$_DB->queryToArray("DELETE FROM {$this->tableIII} WHERE {$this->primaryKeyII} IN ".$this->utils->arrayToQuery('in', array_column($deleted_grid_fields, $this->primaryKeyII)));
				}
			}
			
			//Field Attrs
			foreach(array_keys($data['fields']) as $keyf) {
				if (isset($data['fields'][$keyf]['attr'])) {
					$obj_field_attrs = array_map(function($row) use ($keyf, $obj_grid_fields_id) {
						return [
							"{$this->primaryKeyII}" => $obj_grid_fields_id[$keyf][$this->primaryKeyII],
							'attr' => $row
						];
					}, array_unique($data['fields'][$keyf]['attr']));
					$qryDelete = "DELETE FROM {$this->tableIII} WHERE {$this->primaryKeyII} IN ".$this->utils->arrayToQuery('in', array_column($obj_field_attrs, $this->primaryKeyII));
					$_DB->query($qryDelete);
					$qryInsert = "INSERT INTO {$this->tableIII} ".$this->utils->multipleArrayToInsert($obj_field_attrs);
					$_DB->query($qryInsert);
					error_log($qryInsert);
				} else {
					$qryDelete = "DELETE FROM {$this->tableIII} WHERE {$this->primaryKeyII} = ".$obj_grid_fields_id[$keyf][$this->primaryKeyII];
					$_DB->query($qryDelete);
				}
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
				'table_name' => $data['table']
			];
			$obj_grid_id = $_DB->queryToSingleVal("INSERT INTO {$this->table} ".$this->utils->arrayToQuery('insert', $obj_grid)." RETURNING {$this->primaryKey}");
			//Grid Fields
			$obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
				return [
					"{$this->primaryKey}" => $obj_grid_id,
					'name' => $field['name'],
					'column_name' => $field['column'],
					'type' => $field['type']
				];
			}, $data['fields']);
			$obj_grid_fields_id = $_DB->queryToArray("INSERT INTO {$this->tableII} ".$this->utils->multipleArrayToInsert($obj_grid_fields)." RETURNING {$this->primaryKeyII}");
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
		$grids = $this->grids;
		$grids = array_filter($grids, function($row) use ($list) {
			return !in_array($row->id, $list); 
		});
		return $this->setFile($grids);
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
				'width' => "100px",
				'editType' => 'select',
				//editConfig => [liveSearch => true],
				'editData' => array_map(function($row) {
					return ['id' => $row, 'text' => $row];
				}, $this->fieldTypes)
			],
			[
				'targets' => $dtNum++,
				'title' => "Origen",
				'data' => 'origin',
				'width' => "85px",
				'editType' => 'bselect',
				'editConfig' => [
					'liveSearch' => true,
					'width' => '100px'
				],
				'editData' => $this->getGridCboList()
			],
			[
				'targets' => $dtNum++,
				'title' => "Atributos",
				'data' => 'attr',
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
        return [
            'id' => null,
            'name' => null,
            'column' => null,
			'type' => null,
			'origin' => null,
			'attr' => null,
            'estado' => 'edit'
        ];
	}
	
	function getGridCboList() {
		global $_DB;
		return $_DB->queryToArray("SELECT {$this->primaryKey} AS id, name AS text FROM {$this->table}");
	}
	
	function consolidate($id, $schema) {
		global $_DB;
		//Traer datos de grilla
		$_DB->query("SET search_path TO public, {$schema}");
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
			if (in_array($field->type, ['dtpicker'])) {
				$columns[$field->column][] = 'timestamp';
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