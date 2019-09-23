<?php

class sys_grid_model {

    var $db;
	var $fieldTypes = ['int', 'text', 'check', 'select', 'bselect', 'dtpicker', 'rut'];
	var $fieldAttributes = ['primary' => 'PK', 'autonum' => 'Autonumeric', 'notnull' => 'Obligatorio', 'hidden' => 'Ocultar'];

    function __construct() {
        if (file_exists(root."/grids.json")) {
            $this->grids = json_decode(file_get_contents(root."/grids.json"));
        }
        if (!isset($this->grids)) {
            $this->grids = [];
        }
    }

    function list() {
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
            'attr' => null,
            'estado' => 'edit'
        ];
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
}