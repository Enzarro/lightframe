<?php

use Aws\S3\S3Client;

class sys_generic_model {

    var $db;
	var $primaryKey;

    function __construct($resource = null, $object = null) {
		global $config;
		$this->resource = $resource;
		$this->object = $object;
		$this->utils = new utils();
		$this->sys_clients_model = new sys_clients_model();
		$this->sys_grid_model = new sys_grid_model();
		$this->frame_model = new frame_model();
		$this->login_model = new login_model();
		$this->aws3 = $config->global->aws3;

		$this->s3 = new S3Client([
			'region'  => $this->aws3->region,
			'version' => $this->aws3->version,
			'credentials' => [
				'key'    => $this->aws3->key,
				'secret' => $this->aws3->secret,
			]
		]);

		//Primary key
		if(isset($object)){
			foreach($this->object->fields as $field) {
				if (in_array('primary', $field->attr)) {
					$this->primaryKey = $field->column;
				}
			}
		}
    }

	function list($returnconfig = false) {
		global $config;
		global $client;
		
		$filtro = null;
		$dtNum = 0;
		$columns = [];
		//Columns
		foreach($this->object->fields as $field) {
			if ($field->column == 'eliminado') $filtro = " (NOT eliminado = 1 OR eliminado IS NULL) ";
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
				if ($field->type == "month") {
					$column['formatter'] = function($d) {
						return substr($d, 0, 7);
					};
				}
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
					if ($objOrigin->target_schema == 2 && isset($client)) {
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

		if ($returnconfig) return $columns;

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
        
        if (!isset($_POST["config"])) {
			$failed = false;
			if ($this->object->target_schema == 2) {
				if (!$_POST["client"] || !$this->sys_clients_model->get($_POST["client"]) || $this->resource&&!in_array('read', $this->resource['permisos_user_obj'])) {
					$failed = true;
				}
			} else if ($this->object->target_schema == 1) {
				if ($this->resource&&!in_array('read', $this->resource['permisos_user_obj'])) {
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
	
	function get($id = null) {
		global $_DB;
		$fields = $this->object->fields;
        
		//Quitar los (el) campo de llave primaria
        // $fields = array_filter($this->object->fields, function($field) {
        //     return !in_array('primary', $field->attr);
        // });

		//Columns
		$columns = array_column($fields, 'column');
		if ($id) {
			//Data según ID
			$query = "SELECT ".implode(', ', $columns)." FROM {$this->object->schema}.{$this->object->table} WHERE {$this->primaryKey} = '{$id}'";
			$data = $_DB->queryToArray($query);
			//Convertir registro en objeto
			// $data = (object)$data[0];
		} else {
			//Toda la data
			$data = $_DB->queryToArray("SELECT ".implode(', ', $columns)." FROM {$this->object->schema}.{$this->object->table}");
		}

		$data = $this->parseGet($data);

		if ($id) {
			$data = reset($data);
			$data = (object)$data;
		}
		
		//Retornar objeto final
		return $data;
	}

	function getall($all = false) {
		global $_DB;
		$fields = $this->object->fields;
		$columns = array_column($fields, 'column');
		$hardDelete = "";
		if ($all==false){
			foreach($this->object->fields as $field) {
				if ($field->column == 'eliminado') $hardDelete = " WHERE (NOT eliminado = 1 OR eliminado IS NULL) ";
			}
		}

		$data = $_DB->queryToArray("SELECT ".implode(', ', $columns)." FROM {$this->object->schema}.{$this->object->table} {$hardDelete}");

		$data = $this->parseGet($data);

		return $data;
	}

	function parseGet($data) {
		$fields = $this->object->fields;
		//Parse
		foreach (array_keys($data) as $key) {
			foreach ($fields as $field) {
				if ($data[$key][$field->column]) {
					//Mes
					if ($field->type == 'month') {
						$data[$key][$field->column] = substr($data[$key][$field->column], 0, 7);
					}
				}
			}
		}
		return $data;
	}

	function getGridCbo($id, $assoc = false) {
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
		//Columnas a usar
		$primary = $this->sys_grid_model->getSelectColFromObj($grid, 'primary');
		$text = $this->sys_grid_model->getSelectColFromObj($grid, 'select-text');
		//Subtext
		$datasub = [];
		$subtext = $this->sys_grid_model->getSelectColFromObj($grid, 'select-subtext');
		$subtext = $subtext != $text ? $subtext : false;
		//Group
		$datagrp = [];
		$group = $this->sys_grid_model->getSelectColFromObj($grid, 'select-group');
		$group = $group != $text ? $group : false;
		if ($subtext || $group) {
			//Get column sub
			$foundsub = array_values(array_filter($grid->fields, function($field) use ($subtext) {
				return $field->column == $subtext;
			}));
			$foundsub = count($foundsub)?$foundsub[0]:false;
			//Get column grp
			$foundgrp = array_values(array_filter($grid->fields, function($field) use ($group) {
				return $field->column == $group;
			}));
			$foundgrp = count($foundgrp)?$foundgrp[0]:false;

			if ($foundsub || $foundgrp) {
				if ($foundsub && in_array($foundsub->type, ['select', 'bselect'])) {
					$datasub = $this->getGridCbo($foundsub->origin, true);
					$datasub = array_combine(array_column($datasub, 'value'), array_column($datasub, 'name'));
				}
				if ($foundgrp && in_array($foundgrp->type, ['select', 'bselect'])) {
					$datagrp = $this->getGridCbo($foundgrp->origin, true);
					$datagrp = array_combine(array_column($datagrp, 'value'), array_column($datagrp, 'name'));
				}
			}
		}
		//Get column eliminado
		$foundeliminado = array_values(array_filter($grid->fields, function($field) {
			return $field->column == 'eliminado';
		}));
		//Consulta
		if ($config->database->type == "mssql") {
			$gridData = $_DB->queryToArray(
				"SELECT
					".($subtext?"{$subtext} AS subtext,":"")."
					".($group?"{$group} AS 'group',":"")."
					{$primary} AS value, 
					{$text} AS name
				FROM {$grid->table}".(count($foundeliminado)?" WHERE (NOT eliminado = 1 OR eliminado IS NULL) ":""));
		} else if ($config->database->type == "pgsql") {
			$gridData = $_DB->queryToArray(
				"SELECT
					".($subtext?"{$subtext} AS subtext,":"")."
					".($group?"{$group} AS group,":"")."
					{$primary} AS value, 
					{$text} AS name
				FROM {$grid->table}".(count($foundeliminado)?" WHERE (NOT eliminado = 1 OR eliminado IS NULL) ":""));
		}
		//Retorna consulta directamente? (assoc)
		if ($assoc) {

			//Mapear arreglo
			$gridData = array_map(function ($row) use ($datasub, $datagrp, $group, $subtext) {

				if($subtext){
					if (isset($row['subtext'])) {
						$row['subtext'] = ($datasub?$datasub[$row['subtext']]:$row['subtext']);
					} else {
						$row['subtext'] = '';
					}
				}

				if($group){
					if (isset($row['group'])) {
						$row['group'] = ($datagrp?$datagrp[$row['group']]:$row['group']);
					}else{
						$row['group'] = '';
					}
				}


				return $row;

			}, $gridData?$gridData:[]);



			return $gridData;
		} else {
			//Mapear arreglo
			$data = array_map(function ($row) use ($datasub, $datagrp,$group,$subtext) {
				$basedata = [
					$row['value'],
					$row['name']
				];
				$dataparams = [];
				if($subtext){
					if (isset($row['subtext'])) {
						$dataparams[] = ($datasub?$datasub[$row['subtext']]:$row['subtext']);
					} else {
						$dataparams[] = '';
					}
				}
				if($group){
					if (isset($row['group'])) {
						$dataparams[] = ($datagrp?$datagrp[$row['group']]:$row['group']);
					}else{
						$dataparams[] = '';
					}
				}
				if ($dataparams) $basedata[] = $dataparams;
				return $basedata;
			}, $gridData?$gridData:[]);
			//Retornar arreglo mapeado
			if ($subtext || $group) {
				$return = [
					'table' => $data,
					'data' => []
				];
				if ($subtext) $return['data'][] = ['subtext'];
				if ($group) $return['data'][] = ['group'];
				return $return;
			} else {
				return [
					'table' => $data
				];
			}
		}
		
        
	}

	function getGridCboSingleVal($id, $value) {
		global $_DB;
		global $client;
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


	function createFolderAWS($folder){
		global $config;
		
		$objpath = '';
		$this->s3->registerStreamWrapper();
		foreach(array_keys($folder) as $key){
			$name = $folder[$key];			
			if (!file_exists('s3://'.$this->aws3->bucket.'/'.$objpath.$name)){
				$name = $name.'/';
				// $this->s3->putObject([
				// 	'Bucket' => $this->aws3->bucket,
				// 	'Key'    => $name,
				// 	'Body'   => "",
                //    	'ACL'    => 'public-read-write'		
				// ]);
			} else {
				$name = $name.'/';
			}
			$objpath .= $name;
		}
		return $objpath;
	}

	
	function uploadImageAWS($field,$folder){
		global $config;
		global $client;
		global $_DB;

		//Carpeta por cliente o sistema
		$idgrid = $_DB->queryToSingleVal("SELECT grid_id FROM sys_grids_fields where field_id = {$field->id}");
		$grid = $this->sys_grid_model->get($idgrid);
		if ($grid->target_schema == 2) {
			$db = $client->db_name;
		} else if ($grid->target_schema == 1) {
			$db = "sistema";
		}

		//Une carpetas base con las declaradas
		$folder = array_merge([$this->aws3->path, $db], $folder);
		//Crea las carpetas
		$path = $this->createFolderAWS($folder);	

		//Obtiene extensión y crea el nombre de la imagen
		$ext = pathinfo($_FILES[$field->column]["name"], PATHINFO_EXTENSION);
		$name = $path.sha1(date("YmdHisv")).".".$ext;
				
		//Guarda en el bucket  la imagen subida y retorna la url
		$link = $this->s3->putObject([
			'Bucket' => $this->aws3->bucket,
			'Key'    => $name,
			'SourceFile' => $_FILES[$field->column]["tmp_name"],
			'ACL'    => 'public-read'		
		]);

		return $link['ObjectURL'];
	}
	
	function set($data, $file = null) {
		global $_DB;
		global $client;

		$data = $this->parseSet([$data])[0];

		//Quitar los (el) campo de llave primaria
        $fields = array_filter($this->object->fields, function($field) {
            return !in_array('primary', $field->attr) && !in_array('hiddenForm', $field->attr);
        });

		//Columns
		$columns = array_column($fields, 'column');
		$obj_set = [];
		foreach($fields as $field) {
			//Campos normales
			if (isset($data[$field->column])) {
				$obj_set[$field->column] = $data[$field->column];

				//Uppercase
				if (in_array('uppercase', $field->attr)) {
					$obj_set[$field->column] = strtoupper($data[$field->column]);
				}

				//Mes
				if ($field->type == 'month' && strlen($data[$field->column]) == 7) {
					$obj_set[$field->column] = $data[$field->column].'-01';
				}
			}

			//Campos tipo file
			if(isset($file[$field->column])) {
				//crea carpetas donde se almacenara la foto
				$f_table = explode('.', $this->object->table);
				$f_table = end($f_table);
				$folder = [$f_table, $field->column];
				//sube imagen a AWS
				$obj_set[$field->column] = $this->uploadImageAWS($field,$folder);
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
					'text' => 'Registro actualizado correctamente.',
					'key' => $obj_set_id
				];
			}
		} else {
			$_DB->update_sequence($this->object->table, $this->primaryKey);
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
				'text' => 'Registro insertado correctamente.',
				'key' => $obj_set_id 
			];
		}
	}

	function parseSet($data) {
		$fields = $this->object->fields;
		//Parse
		foreach (array_keys($data) as $key) {
			foreach ($fields as $field) {
				if (isset($data[$key][$field->column])) {
					//Mes
					if ($field->type == 'month') {
						$data[$key][$field->column] = substr($data[$key][$field->column], 0, 7).'-01';
					}
				}
			}
		}
		return $data;
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
			if ($field->column == 'eliminado') $hardDelete = false;
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
	
	function export($resdata = null) {
		global $_DB;
		if (!$resdata && $this->resource) {
			$resdata = (object)$this->resource;
		}
		$fields = array_filter($this->object->fields, function($field) {
            return !in_array('hiddenForm', $field->attr) || in_array('primary', $field->attr);
		});
		$cont=0;
		$excel["name"] = $resdata->texto;
        $excel["includetimestamp"] = false;
        $excel["sheetname"] = $resdata->texto;
		foreach($fields as $field) {
            $finalField[$cont] = [
				'title' => $field->name,
				'width' => 13,
				'data' => $field->column
            ];
            if (in_array($field->type, ['select', 'bselect'])) {
				$finalField[$cont]['combo'] = $this->getGridCbo($field->origin, true);

				error_log(json_encode($finalField[$cont]['combo']));
			}
			$cont++;
		}
		$excel["columns"] = $finalField;
		
		return $excel;
	}

	function import($newdata) {
		// var_dump($newdata);exit;
		$newdata = $this->parseSet($newdata);
		global $_DB;
		global $config;
		$errordata = [];
		$hardDelete = true;
		$keys = array_column($this->object->fields, 'column');
		$names = array_column($this->object->fields, 'name');
		$columns = array_combine($keys,$names);
		//borrando null de primary
		foreach($newdata as $key => $new) {
			if (!$new[$this->primaryKey]) {
				unset($newdata[$key][$this->primaryKey]);
			}
		}
		// validando id duplicados
		$val_duplic = array_count_values(array_column($newdata,$this->primaryKey));
		if (max($val_duplic)>1) {
			foreach (array_keys($val_duplic) as $key) {
				$error = false;
				if ($val_duplic[$key] > 1) {
					$file = array_filter($newdata, function($field) use ($key){
						if(isset($field[$this->primaryKey])) {
							return $field[$this->primaryKey] == $key;
						}
					});
					if($file){
						foreach($file as $row){
							$errorrow['Motivo'] = 'Fila '.$this->primaryKey.' duplicada.';
							foreach(array_keys($row) as $key) {
								$errorrow[$columns[$key]] = $row[$key];
							}
							$errordata[]=$errorrow;
						}
					}
				}
			}
		}
		// validando campos obligatorios
		$required = array_values(array_filter(array_map(function($field) {
			if (in_array('notnull',$field->attr) && !in_array('primary',$field->attr)) {
				return $field->column;
			}	
		}, $this->object->fields)));
		foreach($newdata as $row){
			$error=false;
			foreach(array_keys($row) as $key) {
				if (in_array($key,$required) && $row[$key]===null && strlen($row[$key])<=0) {
					$error=true;
					break;
				}
			}
			if($error) {
				$errorrow['Motivo'] = 'Fila con datos obligatorios vacios.';
				foreach(array_keys($row) as $key) {
					$errorrow[$columns[$key]] = $row[$key];
				}
				$errordata[]=$errorrow;
			}
		}
		if (!empty($errordata)){
			return $errordata;
		}

		//definiendo si tiene columna eliminado
		foreach($this->object->fields as $field) {
			if ($field->column == 'eliminado') $hardDelete =false;
		}
		$obj_grid_fields_id = [];
		//ID de los registros antiguos
		$olddata= array_column($this->getall(true), $this->primaryKey);
		//UPDATE
		$obj_grid_fields_update = array_values(array_filter($newdata, function($field) use($olddata){
			return array_key_exists($this->primaryKey, $field) && in_array($field[$this->primaryKey],$olddata);
		}));
		if ($obj_grid_fields_update) {
			foreach ($obj_grid_fields_update as $upd) {
				$id_upd = $upd[$this->primaryKey];
				unset($upd[$this->primaryKey]);
				if (!$hardDelete) $upd['eliminado'] = null;
				$_DB->queryToArray("UPDATE {$this->object->table} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $upd, 'where' => "WHERE {$this->primaryKey} = {$id_upd}", 'return' => $this->primaryKey]));
				$obj_grid_fields_id[] = [
					$this->primaryKey => $id_upd
				];
			}
		}
		// INSERT WITH ID
        $obj_grid_fields_insertedid = array_values(array_filter($newdata, function($field) use($olddata)  {
            return array_key_exists($this->primaryKey, $field) && !in_array($field[$this->primaryKey],$olddata);
		}));
		if ($obj_grid_fields_insertedid) {
			if ($config->database->type == "mssql") {
				$_DB->query("SET IDENTITY_INSERT {$this->object->table} ON");
			}
			$insert_res = [];
			if (isset($config->database->maxmassinsert) && count($obj_grid_fields_insertedid) > $config->database->maxmassinsert) {
				//Supera el límite de insert masivo - recortar e insertar
				foreach (array_chunk($obj_grid_fields_insertedid, $config->database->maxmassinsert) as $chunk) {
					$insert_res = array_merge($insert_res, $_DB->queryToArray("INSERT INTO {$this->object->table} ".$this->utils->multipleArrayToInsert($chunk, $this->primaryKey)));
				}
			} else {
				$insert_res = $_DB->queryToArray("INSERT INTO {$this->object->table} ".$this->utils->multipleArrayToInsert($obj_grid_fields_insertedid, $this->primaryKey));
			}
			if ($config->database->type == "mssql") {
				$_DB->query("SET IDENTITY_INSERT {$this->object->table} OFF");
			} else if ($config->database->type == "pgsql") {
				$_DB->update_sequence($this->object->table, $this->primaryKey);
			}
			foreach ($insert_res as $ins) {
				$obj_grid_fields_id[] = $ins;
			}
		}
		//INSERT WITHOUT ID
		$obj_grid_fields_insert = array_values(array_filter($newdata, function($field) use($olddata) {
			return !array_key_exists($this->primaryKey, $field);
		}));
		if ($obj_grid_fields_insert) {
			$obj_grid_fields_insert = array_map(function($field){
				if ($field){
					unset($field[$this->primaryKey]);
					return $field;
				}
			}, $obj_grid_fields_insert);
			$insert_res = [];
			if (isset($config->database->maxmassinsert) && count($obj_grid_fields_insert) > $config->database->maxmassinsert) {
				//Supera el límite de insert masivo - recortar e insertar
				foreach (array_chunk($obj_grid_fields_insert, $config->database->maxmassinsert) as $chunk) {
					$insert_res = array_merge($insert_res, $_DB->queryToArray("INSERT INTO {$this->object->table} ".$this->utils->multipleArrayToInsert($chunk, $this->primaryKey)));
				}
			} else {
				$insert_res = $_DB->queryToArray("INSERT INTO {$this->object->table} ".$this->utils->multipleArrayToInsert($obj_grid_fields_insert, $this->primaryKey));
			}
			// $insert_res = $_DB->queryToArray("INSERT INTO {$this->object->table} ".$this->utils->multipleArrayToInsert($obj_grid_fields_insert, $this->primaryKey));
			foreach ($insert_res as $ins) {
				$obj_grid_fields_id[] = $ins;
			}
		}

		//DELETE
		if ($hardDelete) {
			$_DB->queryToArray("DELETE FROM {$this->object->table} WHERE {$this->primaryKey} NOT IN ".$this->utils->arrayToQuery('in', array_column($obj_grid_fields_id, $this->primaryKey)));
		} else {
			$_DB->queryToSingleVal("UPDATE {$this->object->table} SET eliminado = 1 WHERE {$this->primaryKey} NOT IN".$this->utils->arrayToQuery('in', array_column($obj_grid_fields_id, $this->primaryKey)));
		}
		
		return [
			'type' => 'success',
			'text' => "Carga masiva completada con exito."
		];
	}
}