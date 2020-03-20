<?php

class sys_grid_model {

    var $db;
	var $fieldTypes = ['int', 'float', 'text', 'check', 'select', 'bselect', 'dtpicker', 'rut'];
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

	function list() {
		global $config;

		$table = 'grids';
		$primaryKey = 'grid_id';

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
				'db' => 'name',
				//DT
                'title' => 'Nombre'
            ],
            [
				'dt' => $dtNum++,
				'db' => '(SELECT jsonb_agg(fields) FROM (SELECT field_id AS id, name, column_name AS column, type FROM grids_fields WHERE grid_id = grids.grid_id) AS fields)',
				'alias' => 'fields',
				'title' => 'Campos',
				'orderable' => false,
				'searchable' => false,
				'formatter' => function ($data) {
					$data = json_decode($data);
					ob_start(); ?><div style="overflow: auto; height: 34px;"><?php
					foreach($data as $row) {
						echo "<b>Nombre:</b> ".utf8_decode($row->name).", <b>Tipo:</b> $row->type<br>";
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
							<button class="btn btn-success main-edit" title="Editar registro" type="button"><span aria-hidden="true" class="fa fa-pencil"></span></button>
							<button class="btn btn-warning main-consolidate" title="Consilidar en Base de Datos" type="button"><span aria-hidden="true" class="fa fa-database"></span></button>
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

		return SSP::simple( $_POST, $config->database, $table, $primaryKey, $columns, $filtro);
	}

    function listObj() {
		$targets = 0;
		$columns = [[
			'targets' => $targets++,
			'data' => 'id',
			'title' => 'ID',
			'visible' => false
		], [
			'targets' => $targets++,
			'data' => 'name',
			'title' => 'Nombre'
		], [
			'targets' => $targets++,
			'data' => 'table',
			'title' => 'Tabla'
		], [
			'targets' => $targets++,
			'data' => 'fields',
			'title' => 'Campos',
			'orderable' => false,
			'format' => function ($data) {
				ob_start(); ?><div style="overflow: auto; height: 34px;"><?php
				foreach($data as $row) {
					echo "<b>Nombre:</b> $row->name, <b>Tipo:</b> $row->type<br>";
				}
				?></div><?php return ob_get_clean();
			}
		], [
			'targets' => $targets++,
			'title' => 'Acciones',
			'data' => null,
			'searchable' => false,
			'orderable' => false,
			'width' => '50px',
			'defaultContent' => 
				'<div class="btn-group btn-group" role="group" style="width: auto;">
					<button class="btn btn-success main-edit" title="Editar registro" type="button"><span aria-hidden="true" class="fa fa-pencil"></span></button>
				</div>'
		], [
			//DT
			'targets' => $targets++,
			"title" => '<span class="glyphicon glyphicon-trash text-center" aria-hidden="true"></span>',
			"width" => "16px",
			"data" => null,
			"defaultContent" => "",
			"orderable" => false,
			"className" => 'select-checkbox',
			"searchable" => false
		]];
        if (isset($_POST["config"]) && $_POST["config"]) {
            return $columns;
        } else {
			return utils::dtBuildDataFromConfig($columns, $this->grids);
        }
	}
	
	function get($id) {
		global $config;
		$_DB = new database($config->database);

		//Grilla según ID
		$grid = $_DB->queryToArray("SELECT grid_id AS id, name, table_name AS table FROM grids WHERE grid_id = {$id}");
		//Campos de la grilla
		$grid_fields = $_DB->queryToArray("SELECT field_id AS id, name, column_name AS column, type, origin FROM grids_fields WHERE grid_id = {$id}");
		//ID's de los campos de la grilla
		$fields_ids = array_column($grid_fields, 'id');
		//Atributos de los campos de la grilla por id de campo
		$grid_fields_attrs = $_DB->queryToArray("SELECT field_id, attr FROM fields_attrs WHERE field_id IN ".$this->utils->arrayToQuery('in', $fields_ids));
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
		global $config;
		$_DB = new database($config->database);
		if (isset($data['id']) && $data['id']) {
			//:: UPDATE ::
			//Grid
			$obj_grid = [
				'name' => $data['name'],
				'table_name' => $data['table']
			];
			$obj_grid_id = $_DB->queryToSingleVal("UPDATE grids SET ".$this->utils->arrayToQuery('update', $obj_grid)." WHERE grid_id = {$data['id']} RETURNING grid_id");
			
			//Grid Fields
			$updateParams = [
				'grid_id' => 'excluded.grid_id',
				'name' => 'excluded.name',
				'column_name' => 'excluded.column_name',
				'type' => 'excluded.type',
				'origin' => 'excluded.origin'
			];
			$obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
				return [
					'field_id' => $field['id']?$field['id']:'DEFAULT',
					'grid_id' => $obj_grid_id,
					'name' => $field['name'],
					'column_name' => $field['column'],
					'type' => $field['type'],
					'origin' => $field['origin']
				];
			}, $data['fields']);

			$obj_grid_fields_id = $_DB->queryToArray("INSERT INTO grids_fields ".$this->utils->multipleArrayToInsert($obj_grid_fields)." ON CONFLICT (field_id) DO UPDATE SET ".$this->utils->arrayToQuery('update', $updateParams)." RETURNING field_id");
			if ($obj_grid_fields_id) {
				$deleted_grid_fields = $_DB->queryToArray("DELETE FROM grids_fields WHERE grid_id = {$obj_grid_id} AND field_id NOT IN ".$this->utils->arrayToQuery('in', array_column($obj_grid_fields_id, 'field_id'))." RETURNING field_id");
				if ($deleted_grid_fields) {
					$_DB->queryToArray("DELETE FROM fields_attrs WHERE field_id IN ".$this->utils->arrayToQuery('in', array_column($deleted_grid_fields, 'field_id')));
				}
			}
			
			//Field Attrs
			foreach(array_keys($data['fields']) as $keyf) {
				if (isset($data['fields'][$keyf]['attr'])) {
					$obj_field_attrs = array_map(function($row) use ($keyf, $obj_grid_fields_id) {
						return [
							'field_id' => $obj_grid_fields_id[$keyf]['field_id'],
							'attr' => $row
						];
					}, array_unique($data['fields'][$keyf]['attr']));
					$qryDelete = "DELETE FROM fields_attrs WHERE field_id IN ".$this->utils->arrayToQuery('in', array_column($obj_field_attrs, 'field_id'));
					$_DB->query($qryDelete);
					$qryInsert = "INSERT INTO fields_attrs ".$this->utils->multipleArrayToInsert($obj_field_attrs);
					$_DB->query($qryInsert);
					error_log($qryInsert);
				} else {
					$qryDelete = "DELETE FROM fields_attrs WHERE field_id = ".$obj_grid_fields_id[$keyf]['field_id'];
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
			$obj_grid_id = $_DB->queryToSingleVal("INSERT INTO grids ".$this->utils->arrayToQuery('insert', $obj_grid)." RETURNING grid_id");
			//Grid Fields
			$obj_grid_fields = array_map(function($field) use ($obj_grid_id) {
				return [
					'grid_id' => $obj_grid_id,
					'name' => $field['name'],
					'column_name' => $field['column'],
					'type' => $field['type']
				];
			}, $data['fields']);
			$obj_grid_fields_id = $_DB->queryToArray("INSERT INTO grids_fields ".$this->utils->multipleArrayToInsert($obj_grid_fields)." RETURNING field_id");
			//Field Attrs
			foreach(array_keys($data['fields']) as $keyf) {
				if (isset($data['fields'][$keyf]['attr'])) {
					$obj_field_attrs = array_map(function($row) use ($keyf, $obj_grid_fields_id) {
						return [
							'field_id' => $obj_grid_fields_id[$keyf]['field_id'],
							'attr' => $row
						];
					}, array_unique($data['fields'][$keyf]['attr']));
					$_DB->query("INSERT INTO fields_attrs ".$this->utils->multipleArrayToInsert($obj_field_attrs));
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

    function setObj($data) {
		$grids = $this->grids;
        if (isset($data['id']) && $data['id']) {
			//Update
			$found = false;
			//Buscar id de objeto en arreglo local
			foreach(array_keys($grids) as $key) {
				if ($grids[$key]->id == $data['id']) {
					$found = true;
					//Objeto encontrado, setear nuevos valores
					$obj = $grids[$key];

					//Valores base
					$obj->name = $data['name'];
					$obj->table = $data['table'];

					//Subregistros
					foreach(array_keys($data['fields']) as $keyf) {
						$data['fields'][$keyf] = (object)$data['fields'][$keyf];
						$data['fields'][$keyf]->id = (int)$data['fields'][$keyf]->id;
					}

					$obj->fields = $this->generateIDs('id', $data['fields']);

					$grids[$key] = $obj;
				}
			}
			if ($found) {
				return $this->setFile($grids);
			} else {
				return $found;
			}
			
		} else {
			//New
			$data = (object)$data;
			$data->id = null;
			//Generar ids de subregistros
			//Subregistros
			foreach(array_keys($data->fields) as $key) {
				$data->fields[$key] = (object)$data->fields[$key];
			}
			$data->fields = $this->generateIDs('id', $data->fields);

			//Meter regsitro en arreglo
			$grids[] = $data;

			//Generar id
			$grids = $this->generateIDs('id', $grids);

			//Guardar
			return $this->setFile($grids);
		}
	}
	
	function generateIDs($id, $data) {
		//Obtener ID más alto
		$highestid = 0;
		foreach($data as $field) {
			if ($field->$id && $highestid < $field->$id) {
				$highestid = $field->$id;
			}
		}
		//Generar ID a nuevos registros
		foreach(array_keys($data) as $key) {
			$data[$key] = $data[$key];
			if (!$data[$key]->$id) {
				$highestid++;
				$data[$key]->$id = $highestid;
			}
		}
		return $data;
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
		global $config;
		$_DB = new database($config->database);
		return $_DB->queryToArray("SELECT grid_id AS id, name AS text FROM grids");
	}
	
	function getCampos($where) {
		global $_DB;
		//Sin where no hay data amigo
		if (!$where) return [];
		
		$sql = "SELECT
					carga_id,
					carga_rut,
					carga_nombre,
					carga_fecha_nac,
					carga_fecha_ven,
					carga_fecha_ing,
					carga_tipo,
					carga_parentesco,
					activo,
					null as estado
				FROM r_trabajadores_cargas 
				WHERE ".arrayToQuery("and", $where)." AND (NOT eliminado = '1' OR eliminado IS NULL)
				ORDER BY carga_id";
		return $_DB->query_to_array($sql);
	}
	
	function setCampos($id, $cargasJson) {
		global $_DB;
		$existingCargas = $this->getCargas([
			'trabajador_id' => $id
		]);
		$cargas = json_decode($cargasJson, true);

		//Acciones para las cargas que vienen en el objeto
		foreach (array_keys($cargas) as $key) {
			/* id, rut, nombre, fecha_nacimiento, fecha_vencimiento, fecha_ingreso, tipo, parentesco, activo, estado */
			$data = [
				'carga_rut' => $cargas[$key]['carga_rut'],
				'carga_nombre' => $cargas[$key]['carga_nombre'],
				'carga_fecha_nac' => $cargas[$key]['carga_fecha_nac'],
				'carga_fecha_ven' => $cargas[$key]['carga_fecha_ven'],
				'carga_fecha_ing' => $cargas[$key]['carga_fecha_ing'],
				'carga_tipo' => $cargas[$key]['carga_tipo'],
				'carga_parentesco' => $cargas[$key]['carga_parentesco'],
				'activo' => $cargas[$key]['activo']
			];
			if ($cargas[$key]['carga_id']) {
				//Update
				$_DB->query("UPDATE r_trabajadores_cargas SET ".arrayToQuery("update", $data)." WHERE carga_id = {$cargas[$key]['carga_id']}");
			} else {
				//Insert
				$data = array_replace($data, [
					'trabajador_id' => $id
				]);
				$_DB->query("INSERT INTO r_trabajadores_cargas ".arrayToQuery("insert", $data));
			}
		}

		//Acciones para las cargas que no vienen en el objeto (eliminar)
		$idFinales = array_column($cargas, 'carga_id');
		$idExistentes = array_column($existingCargas, 'carga_id');
		foreach ($idExistentes as $oldId) {
			if (!in_array($oldId, $idFinales)) {
				//Update
				$_DB->query("UPDATE r_trabajadores_cargas SET eliminado = 1 WHERE carga_id = {$oldId}");
			}
		}
	}
	
	//Save to file / Response
    function setFile($grids) {
		$result = file_put_contents(root."/grids.json", json_encode($grids, JSON_PRETTY_PRINT));
        if (!$result) {
            //Error
            return [
                'type' => 'warning',
                'title' => 'Cambios no guardados',
				'text' => 'Hubo un problema al guardar el fichero',
				'result' => $result
            ];
        } else {
            //Success
            return [
                'type' => 'success',
                'title' => 'Cambios guardados',
                'text' => 'Los cambios fueron guardados con éxito',
				'result' => $result
            ];
        }
	}
	
	function consolidate($id) {
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
			if (in_array($field->type, ['dtpicker'])) {
				$columns[$field->column][] = 'timestamp';
			}
		}

		//Crear tabla
		$this->utils->arrayToTable([
            'table' => $data->table,
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