<?php

class rrhh_trabajadores_model {

    function __construct($resource) {
        $this->sys_clients_model = new sys_clients_model();
		$this->resource = $resource;
		$this->utils = new utils();
		$this->frame_model = new frame_model();
    }
	//Function Map
	/*
	RemoveLog(id, month)
		Eliminar registros de rrhh_trabajadores_datalog según ID y Mes.
	FormTrabajadorData(id, month = null)
		array: Devuelve diversos datos correspondientes al trabajador, según mes.
	LogList(id) [fecha_log,sueldo_base,centro_costo,cargo,afp,isapre]
		Devuelve un resumen de todos los registros en la tabla "rrhh_trabajadores_datalog" del trabajador.
	InsertTrabajador(form) [stat,desc]
		Recibe array formulario creación. Inserta trabajador y primer log. Setea bonos mes.
	UpdateTrabajador(id, form) [stat,desc]
		Recibe formulario. Actualiza datos cabecera. Actualiza o inserta log según datelog. Setea bonos mes.
	fnDelete(list) [stat,desc]
		Recibe listado ID trabajadores. Setea campo "eliminado=1".
	ToggleTrabajador(tid,date) [stat,desc,val,btns]
		Activar o desactivar trabajador. Actualiza rrhh_trabajadores. Inserta registro en rrhh_trabajadores_habilitacion según cambio de estado.
	ComboPaises() HTML
	ComboProvincias(idregion) HTML
	ComboComunas(idprovincia) HTML
	ComboBancos() HTML
	ComboCargos() HTML
	DataCargo(id) [nombre,descripcion]
	UpdateCargo(id,name,description) [title,stat,desc]
	AddCargo(name,description) [title,stat,desc]
	DeleteCargo(id) [title,stat,desc]
	SetBonos(id,jsonBonos,month)
	InsertBono(bono,month) bool
	WipeBonos(id,month)	
	*/
	
	function fnDTData($id = null, $viewdisabled = false, $returnconfig = false, $date = null) {
		global $actionbuttons;
        global $_DB;
        global $config;
        global $client;
        
		$remunModel = new rrhh_remun_model();

		if ($id) {
			$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores WHERE id = '".$id."'";
			$res = $_DB->query($sql);
			$arreturn = array();
			if(!$_DB->num_rows($res) == 0) {
				$reg = $_DB->to_object($res);
				$arreturn[] = array($reg->id, $reg->nombre." ".$reg->apellido_paterno." ".$reg->apellido_materno, self::formatRUT($reg->rut), $reg->activo, $reg->activo == "1" ? $actionbuttons : str_replace("Deshabilitar","Habilitar",str_replace("glyphicon-remove","glyphicon-ok",$actionbuttons)));
				return $arreturn[0];
			}
		}
        if ($client) {
            $table = $client->db_name.'.rrhh_trabajadores';
        } else {
            $table = 'rrhh_trabajadores';
        }
		
		$primaryKey = 'id';
		$dtNum = 0;
		$columns = [
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'id',
				//DT
				'title' => 'ID',
				'visible' => false,
				'searchable' => false
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "rut",
				'formatter' => function( $d, $row ) {
					return self::formatRUT($d);
				},
				//DT
				'title' => 'RUT'
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "nombre",
				'formatter' => function( $d, $row ) {
					return $row['nombre'].' '.$row['apellido_paterno'].' '.$row['apellido_materno'];
				},
				//DT
				'title' => 'Nombre'
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "apellido_paterno",
				//DT
				'title' => 'A.Paterno',
				'visible' => false
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "apellido_materno",
				//DT
				'title' => 'A.Materno',
				'visible' => false
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "''",
				'alias' => 'resumen',
				'formatter' => function( $d, $row ) use ($date, $remunModel) {
					$thisDate = explode('-', $date);
					$rData = $remunModel->TimePeriodTotalHours($row['id'], $thisDate[0], $thisDate[1]);
					$rlData = $remunModel->DataLabeller($rData);
					ob_start(); ?>
					<div style="overflow: auto; height: 46px;">
						<i class="fa fa-money" aria-hidden="true"></i> <small>Líquido: <?php echo $rlData['D133']; ?></small><br>
						<?php if ($rData['D95'] != 0): ?><i class="fa fa-hourglass-start" aria-hidden="true"></i> <small>Atrasos: <?php echo $rlData['D95']; ?> (<?php echo $rlData['D125']; ?>)</small><br><?php endif; ?>
						<?php if ($rData['N95'] != 0): ?><i class="fa fa-hourglass-end" aria-hidden="true"></i> <small>Extras: <?php echo $rlData['N95']; ?> (<?php echo $rlData['P95']; ?>)</small><br><?php endif; ?>
					</div>
					<?php return ob_get_clean();
				},
				//DT
				'title' => 'Resumen Mes',
				"class" => "tdpad2px",
				'searchable' => false,
				'orderable' => false
			],
			[
				//DB
				'dt' => $dtNum++,
				'db' => "activo",
				//DT
				'title' => 'Activo',
				'visible' => false,
				'searchable' => false
			],
			[
				//DT
				'dt' => $dtNum++,
				'db' => "''",
				'alias' => 'acciones',
				'formatter' => function( $d, $row ) {
					ob_start(); ?>
					<div class="btn-group btn-group" role="group">
						<button class="btn btn-default" id="element-worker-assist" title="Control de asistencia" type="button"><span aria-hidden="true" class="glyphicon glyphicon-time"></span></button>
						<button class="btn btn-default" id="element-worker-remun" title="Remuneración" type="button"><span aria-hidden="true" class="glyphicon glyphicon-usd"></span></button>
						<button class="btn btn-default" id="element-worker-files" title="Documentación" type="button"><span aria-hidden="true" class="glyphicon glyphicon-file"></span></button>
						<button class="btn btn-default" id="element-worker-events" title="Gestión de fechas" type="button"><span aria-hidden="true" class="glyphicon glyphicon-calendar"></span></button>
					</div>
					<div class="btn-group btn-group" role="group">
						<button class="btn btn-success" id="element-worker-edit" title="Editar trabajador" type="button"><span aria-hidden="true" class="glyphicon glyphicon-pencil"></span></button> 
					</div>
					<?php return ob_get_clean();
				},
				'title' => 'Acciones',
				"responsivePriority" => 2,
				"orderable" => false,
				"width" => "205px",
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
			],
		];

        $filtro = "";
		//Return DataTables Configuration
		if (!isset($_POST["config"])) {
			$failed = false;
			if (!$_POST["client"] || !$this->sys_clients_model->get($_POST["client"]) || !in_array('read', $this->resource['permisos_user_obj'])) {
                $failed = true;
            } else {
                $filtro = $this->monthlyWhere($date, $viewdisabled);
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

		return SSP::simple($_POST, $config->database, $table, $primaryKey, $columns, $filtro);
    }
    
    
	
	function getTrabajadoresRemunByMonth($date, $viewdisabled = false) {
		global $_DB;
		global $client;
		$remunModel = new r_remun_model();
		$objDate = DateTime::createFromFormat('!Y-m-d', "{$date}-01");
		$workers = [
			'ListaRemuneraciones' => []
		];

		//Obtener listado de trabajadores en el mes
		$filtro = $this->monthlyWhere($date, $viewdisabled);
		$sql = "SELECT id FROM {$client->db_name}.rrhh_trabajadores WHERE ". $filtro;

		$res = $_DB->queryToArray($sql);
		foreach ($res as $reg) {
			$reg = (object)$reg;
			$id = $reg->id;
			$wData = $this->getTrabajadorHead($id);
			$rData = $remunModel->TimePeriodTotalHours($id, $objDate->format('Y'), $objDate->format('m'));

			$worker = [
				'nombre' => utf8_encode($wData['nombre']),
				'apellido_paterno' => utf8_encode($wData['apellido_paterno']),
				'apellido_materno' => utf8_encode($wData['apellido_materno']),
				'correo' => $wData['email'],
				'rut' => $wData['rut'],
				'telefono' => $wData['fono'],
				'id_comuna' => $wData['comuna_id'],
				'direccion' => utf8_encode($wData['direccion']),
				'id_usuario_bc' => $wData['id'],
				'liquidacion' => [
					'fecha_tributaria' => $date,
					'total_ganado' => $rData['D123'],
					'horas_extras' => $rData['D124'],
					'atrasos' => $rData['D125'],
					'imponibles' => $rData['D126'],
					'total_imponibles' => $rData['D127'],
					'no_imponible' => $rData['D128'],
					'total_haberes' => $rData['D129'],
					'desc_legales' => $rData['D130'],
					'otros_desc' => $rData['D131'],
					'total_tributable' => $rData['D132'],
					'saldo_liquido' => $rData['D133']
				]
			];

			$workers['ListaRemuneraciones'][] = $worker;
		}

		//Traer data por cada trabajador


		//Cargar remuneración a cada data


		return $workers;
	}
	
	function DateManagerSave($id, $events) {
		return $this->setTrabajadorEvents($id, json_decode($events, true));
	}

	function formatRUT($rut) {
		return number_format(explode("-", $rut)[0], 0, ',', '.')."-".explode("-", $rut)[1];
	}

	function removeWS($string) {
		return trim(preg_replace('/\s\s+/', ' ', $string));
	}
	
	//POSTFORM: Select Trabajador
	function getTrabajadorHead($id) {
		global $_DB;
		global $client;
		
		$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL) and id = '{$id}'";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$data = (array)$_DB->to_object($res);
			//Comunas
			$data = array_merge($data, (array)self::getRegionProvinciaByComuna($data["comuna_id"]));
			return $data;
		} else {
			return [];
		}
	}
	
	function getTrabajadorBody($id, $date) {
		global $_DB;
		global $client;
		$arreturn = [];
		
		//User
		$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL) and id = '{$id}';";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			//User - Datalog//Datalog Trabajador
			$res = $_DB->query("SELECT fecha_log from {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' ORDER BY fecha_log ASC LIMIT 1");
			$reg = $_DB->to_object($res);
			$oldestLog = DateTime::createFromFormat('!Y-m-d', $reg->fecha_log);
			$requestedLog = DateTime::createFromFormat('!Y-m-d', "{$date}-01");
			if ($requestedLog <= $oldestLog) {
				//Si la fecha solicitada es menor o igual a la fecha más antigua
				$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' ORDER BY fecha_log ASC LIMIT 1;";
			} else {
				//Fecha más antigua
				$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' AND fecha_log <= '{$date}-01' ORDER BY fecha_log DESC LIMIT 1;";
			}
			$res = $_DB->query($sql);
			if(!$_DB->num_rows($res) == 0) {
				$arreturn = (array)$_DB->to_object($res);
				
				//Formas de Pago
				if($arreturn["medio_pago"] != "" || $reg["medio_pago"] != null) {
					$fpago = json_decode($arreturn["medio_pago"]);
					foreach($fpago as $fpid => $fpval) {
						$arreturn[$fpid] = $fpval;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
		return $arreturn;
	}
	
	function setTrabajadorHead($id = null, $data, $date) {
		$headFormMap = [
			"nombre" => "nombre",
			"apellido_paterno" => "apaterno",
			"apellido_materno" => "amaterno",
			"rut" => "rut",
			"fecha_nacimiento" => "datenacimiento",
			"fono" => "phone",
			"fecha_ingreso" => "dateingreso",
			"direccion" => "direccion",
			"comuna_id" => "comuna",
			"nivelestudios_id" => "estudios",
			"email" => "email",
			"estadocivil" => "estadocivil",
			"sexo" => "sexo",
			"nacionalidad_id" => "nacionalidad",
			"ciudad_origen" => "ciudad_origen",
			"usuario_id" => null,
			"fecha_creacion" => null,
			"cliente_id" => null,
			"activo" => null
		];
	}
	
	function setTrabajadorBody($id = null, $data, $date) {
		$bodyFormMap = [
			"trabajador_id" => null,
			"fecha_log" => null,
			"contrato_id" => "contrato",
			"contrato_fin" => "",
			"centro_costo_id" => "centrocostos",
			"cargo" => "cargo",
			"sueldo_base" => "sueldobase",
			"gratificacion_legal" => "gratificacionlegal",
			"gratificacion_legal_mode" => "gratificacion-mode",
			"bono_asistencia" => "bonoasistencia",
			"bono_movilizacion" => "bonomovilizacion",
			"bono_colacion" => "bonocolacion",
			"afp_id" => "afp",
			"isapre_id" => "isapre",
			"horas_lun_vie" => "horaslunvie",
			"horas_sab_dom" => "horassabdom",
			"sueldo_quincena" => "sueldoquincena",
			"isapre_adicional" => "isapreadicional",
			"apv_uf" => "apvuf",
			"apv_porcentaje" => "apvporcentaje",
			"apv_pactado" => "apvpactado",
			"ccaf" => "ccaf",
			"medio_pago" => "medio_pago",
			"usuario_id" => null,
			"fecha_creacion" => null,
		];
	}
	
	/**
	 * Genera la cláusula WHERE para hacer una consulta a la tabla rrhh_trabajadores,
	 * filtrando los trabajadores retornados dependiendo del mes tributario.
	 * @param type $date 
	 * @param type $viewdisabled
	 * @return string
	 */
	function monthlyWhere($date, $viewdisabled) {
		//Filtro: Contenido de cláusula WHERE, también puede contener JOIN
		if (is_string($viewdisabled)) {
			$viewdisabled = filter_var($viewdisabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		}
		//Filtro de fechas
		$jsonChars = ['"', '[', ']'];
		$sqlChars  = ["'", '(', ')'];
		$DisabledIDs = self::activeTrabajadorIDs($date, true);
		$EnabledIDs = self::activeTrabajadorIDs($date, false);

		$filtro = "(NOT eliminado = '1' OR eliminado IS NULL)";
		if (!count($DisabledIDs) && !count($EnabledIDs)) {
			$filtro .= " AND id IN (-1)";
		} else {
			if ($viewdisabled) {
				if (count($DisabledIDs)) {
					$filtro .= " AND id IN ".str_replace($jsonChars, $sqlChars, json_encode($DisabledIDs));
				} else {
					$filtro .= " AND id NOT IN ".str_replace($jsonChars, $sqlChars, json_encode($EnabledIDs));
				}
			} else {
				if (count($EnabledIDs)) {
					$filtro .= " AND id IN ".str_replace($jsonChars, $sqlChars, json_encode($EnabledIDs));
				} else {
					$filtro .= " AND id NOT IN ".str_replace($jsonChars, $sqlChars, json_encode($DisabledIDs));
				}
			}
		}
		return $filtro;
	}
	
	function SaveTrabajador($id = null, $form) {
		global $_DB;
		$return_res = Array();
		//Parse
		$trabajador = Array();
		foreach($form as $input) {
			//Set null if empty
			$trabajador[$input["name"]] = $input["value"]?:null;
		}
		
		//Header Data Object for Query
		$wHeaderData = [
			'nombre' => ucwords(self::removeWS($trabajador["nombre"])),
			'apellido_paterno' => ucwords(self::removeWS($trabajador["apaterno"])),
			'apellido_materno' => ucwords(self::removeWS($trabajador["amaterno"])),
			'rut' => $trabajador["rut"],
			'fecha_nacimiento' => $trabajador["datenacimiento"],
			'fono' => $trabajador["phone"],
			
			'direccion' => self::removeWS($trabajador["direccion"]),
			'comuna_id' => $trabajador["comuna"],
			'nivelestudios_id' => $trabajador["estudios"],
			'email' => $trabajador["email"],
			'estadocivil' => $trabajador["estadocivil"],
			'sexo' => $trabajador["sexo"],
			
			'nacionalidad_id' => $trabajador["nacionalidad"],
			'ciudad_origen' => $trabajador["ciudad_origen"]
		];
		
		//Body Data Object for Query
		$wBodyData = [
			'contrato_id' => $trabajador["contrato"],
			'centro_costo_id' => $trabajador["centrocostos"],
			'cargo' => $trabajador["cargo"],
			'sueldo_base' => $trabajador["sueldobase"],
			'gratificacion_legal' => $trabajador["gratificacionlegal"]?:0,
			'gratificacion_legal_mode' => $trabajador["gratificacion-mode"],
			'bono_asistencia' => $trabajador["bonoasistencia"],
			'bono_movilizacion' => $trabajador["bonomovilizacion"],
			'bono_colacion' => $trabajador["bonocolacion"],
			'afp_id' => $trabajador["afp"],
			'isapre_id' => $trabajador["isapre"],
			'horas_lun_vie' => $trabajador["horaslunvie"],
			'horas_sab_dom' => $trabajador["horassabdom"],

			'sueldo_quincena' => $trabajador["sueldoquincena"],
			'isapre_adicional' => $trabajador["isapreadicional"],
			'apv_uf' => $trabajador["apvuf"],
			'apv_porcentaje' => $trabajador["apvporcentaje"],
			'apv_pactado' => $trabajador["apvpactado"],
			'ccaf' => $trabajador["ccaf"],

			'medio_pago' => $trabajador["medio_pago"],

			'usuario_modificacion' => $_SESSION["us_id"],
			'fecha_modificacion' => 'now()'
		];
		
		//Pre operation validation
		$rutExists = self::checkIfTrabajadorExists($trabajador["rut"]);
		if (!$id && !$rutExists) {
			//Insert
			$wHeaderData = array_replace_recursive($wHeaderData, [
				'usuario_id' => $_SESSION["us_id"],
				'fecha_creacion' => 'now()',
				'cliente_id' => $_SESSION["ss_cliente"],
				'activo' => 1
			]);
		} else if ($id && $rutExists) {
			//Update
			$wHeaderData = array_replace_recursive($wHeaderData, [
				'usuario_modificacion' => $_SESSION["us_id"],
				'fecha_modificacion' => 'now()'
			]);
		} else {
			//Error
		}
		
	}
	
	function checkIfTrabajadorExists($rut) {
		global $_DB;
		global $client;
		$sql = "SELECT id FROM {$client->db_name}.rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL) AND rut = '{$rut}'";
		$res = $_DB->query($sql);
		return $_DB->num_rows($res) != 0;
	}
	
	function checkIfTrabajadorLogExists($id, $date) {
		global $_DB;
		global $client;
		$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' AND fecha_log = '{$date}-01'";
		$res = $_DB->query($sql);
		return $_DB->num_rows($res) != 0;
	}
	
	//POSTFORM: Insert Trabajador
	function InsertTrabajador($form) {
		global $_DB;
		global $client;

		$return_res = Array();
		
		//Parse
		$trabajador = Array();
		foreach($form as $input) {$trabajador[$input["name"]] = $input["value"]?:null;}
		
		//Save
		$sql = "SELECT id FROM {$client->db_name}.rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL) AND rut = '".$trabajador["rut"]."'";
		$res = $_DB->query($sql);

		//Data
		$formArray = [
			'nombre' => $trabajador["nombre"],
			'apellido_paterno' => $trabajador["apaterno"],
			'apellido_materno' => $trabajador["amaterno"],
			'rut' => $trabajador["rut"],
			'fecha_nacimiento' => $trabajador["datenacimiento"],
			'fono' => $trabajador["phone"],
			
			'direccion' => self::removeWS($trabajador["direccion"]),
			'comuna_id' => $trabajador["comuna"],
			'nivelestudios_id' => $trabajador["estudios"],
			'email' => $trabajador["email"],
			'estadocivil' => $trabajador["estadocivil"],
			'sexo' => $trabajador["sexo"],
			
			'nacionalidad_id' => $trabajador["nacionalidad"],
			'ciudad_origen' => $trabajador["ciudad_origen"],
			
			'activo' => 1
		];
		
		if($_DB->num_rows($res) == 0) {
			$sql = "INSERT INTO {$client->db_name}.rrhh_trabajadores ".$this->utils->arrayToQuery(['action' => "insert", 'array' => $formArray, 'return' => 'id']);
			$res = $_DB->query_to_array($sql);
            if(!$res) {
				//Select created worker
				$reg = $res[0];
				
				//Datalog
				$logResult = self::setTrabajadorLog($reg['id'], $trabajador);

				//History
				$this->frame_model->setHistory('rrhh_trabajadores', $reg['id'], 1);

				//Centros de costo
				$this->setCC($reg['id'], $trabajador["centroscostos"], $trabajador["datelog"]);
				
				//Bonos
				$this->setBonos($reg['id'], $trabajador["bonos"], $trabajador["datelog"]);

				//Cargas
				$this->setCargas($reg['id'], $trabajador["cargas"]);
				
				$return_res["val"] = json_encode(self::fnDTData($reg['id']));
				
				//Result
				$return_res["stat"] = "success";
				$return_res["desc"] = "Trabajador ingresado correctamente.";
			}
		} else {
			$return_res["stat"] = "warning";
			$return_res["desc"] = "Ya existe un trabajador con el mismo RUT (".$trabajador["rut"].").";
		}
		return $return_res;
	}
	//POSTFORM: Update Trabajador
	function UpdateTrabajador($id, $form) {
		global $_DB;
		global $actionbuttons;
		$return_res = [];
		
		//Parse form
		$trabajador = [];
		foreach($form as $input) {
			$trabajador[$input["name"]] = $input["value"]?:null;
		}
		
		//Data
		$formArray = [
			'nombre' => ucwords(strtolower(self::removeWS($trabajador["nombre"]))),
			'apellido_paterno' => ucwords(strtolower(self::removeWS($trabajador["apaterno"]))),
			'apellido_materno' => ucwords(strtolower(self::removeWS($trabajador["amaterno"]))),
			'rut' => $trabajador["rut"],
			'fecha_nacimiento' => $trabajador["datenacimiento"],
			'fono' => $trabajador["phone"],
			
			'direccion' => self::removeWS($trabajador["direccion"]),
			'comuna_id' => $trabajador["comuna"],
			'nivelestudios_id' => $trabajador["estudios"],
			'email' => $trabajador["email"],
			'estadocivil' => $trabajador["estadocivil"],
			'sexo' => $trabajador["sexo"],
			
			'nacionalidad_id' => $trabajador["nacionalidad"],
			'ciudad_origen' => $trabajador["ciudad_origen"],
			
			'usuario_modificacion' => $_SESSION["us_id"],
			'fecha_modificacion' => 'now()'
		];
		//Update
		//:Check
		$sql = "SELECT * FROM rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL) AND rut = '".$trabajador["rut"]."' AND (NOT id = '".$id."')";
		$res = $_DB->query($sql);
		if($_DB->num_rows($res) == 0){
			
			$sqlUpdate = "UPDATE rrhh_trabajadores SET ".$this->utils->arrayToQuery("update", $formArray)." WHERE id = {$id}";
			$res = $_DB->query($sqlUpdate);
			if($res != false) {
				//Update Datalog
				$logResult = self::setTrabajadorLog($id, $trabajador);
				
				//Centros de costo
				$this->setCC($id, $trabajador["centroscostos"], $trabajador["datelog"]);

				//Bonos
				$this->setBonos($id, $trabajador["bonos"], $trabajador["datelog"]);

				//Cargas
				$this->setCargas($id, $trabajador["cargas"]);
				
				if ($logResult === true) {
					//Select recently edited worker
					$return_res = [
						'swal' => [
							// 'confirmButtonText' => "Salir",
							// 'cancelButtonText' => "Continuar" ,
							'title' => 'Trabajador actualizado correctamente.',
							'type' => 'success',
							// 'showCancelButton' => true,
							// 'confirmButtonColor' => "#DD6B55"
						],
						'data' => [
							'row' => self::fnDTData($id),
							'loglist' => self::LogList($id),
							'rawdata' => $trabajador,
							'query' => $sqlUpdate
						]
					];
				} else {
					$return_res = [
						'swal' => [
							'type' => 'warning',
							'title' => 'Error en consulta SQL',
							'html' => '<pre style="text-align: left;">'.$logResult.'</pre>'
						]
					];
				}
			} else {
				$buff_err = ob_get_clean();
				$return_res = [
					'swal' => [
						'type' => 'warning',
						'title' => 'Error en consulta SQL',
						'html' => '<pre style="text-align: left;">'.$buff_err.'</pre>'
					]
				];
			}
		} else {
			$return_res = [
				'swal' => [
					'type' => 'warning',
					'title' => "Ya existe un trabajador con el mismo RUT ({$trabajador["rut"]}).",
					'html' => '<pre style="text-align: left;">'.$buff_err.'</pre>'
				]
			];
		}
		return $return_res;
	}

	function setTrabajadorLog($id, $trabajador) {
		global $_DB;
		global $client;

		//Medio Pago (guardado como JSON)
		$fpago = [];
		$fpago["formapago"] = $trabajador["formapago"];
		if ($trabajador["formapago"] == 1) {
			$fpago["fp-efectivo-pago"] = $trabajador["fp-efectivo-pago"];
		}
		if ($trabajador["formapago"] == 3) {
			$fpago["fp-valevista-entrega"] = $trabajador["fp-valevista-entrega"];
			$fpago["fp-valevista-banco"] = $trabajador["fp-valevista-banco"];
		}
		if ($trabajador["formapago"] == 4) {
			$fpago["fp-deposito-tipo"] = $trabajador["fp-deposito-tipo"];
			$fpago["fp-deposito-banco"] = $trabajador["fp-deposito-banco"];
			$fpago["fp-deposito-ncuenta"] = $trabajador["fp-deposito-ncuenta"];
		}
		$trabajador["medio_pago"] = json_encode($fpago);

		//Query Array
		$arrQuery = [
			'contrato_id' => $trabajador["contrato"],
			'centro_costo_id' => $trabajador["centrocostos"],
			'cargo' => $trabajador["cargo"],
			'sueldo_base' => $trabajador["sueldobase"],
			'gratificacion_legal' => $trabajador["gratificacionlegal"]?:0,
			'gratificacion_legal_mode' => $trabajador["gratificacion-mode"],
			'bono_asistencia' => $trabajador["bonoasistencia"],
			'bono_movilizacion' => $trabajador["bonomovilizacion"],
			'bono_colacion' => $trabajador["bonocolacion"],
			'afp_id' => $trabajador["afp"],
			'isapre_id' => $trabajador["isapre"],
			'horas_lun_vie' => $trabajador["horaslunvie"],
			'horas_sab_dom' => $trabajador["horassabdom"],
			
			'sueldo_quincena' => $trabajador["sueldoquincena"],
			'isapre_adicional' => $trabajador["isapreadicional"],
			'apv_uf' => $trabajador["apvuf"],
			'apv_porcentaje' => $trabajador["apvporcentaje"],
			'apv_pactado' => $trabajador["apvpactado"],
			'ccaf' => $trabajador["ccaf"],
			
			'medio_pago' => $trabajador["medio_pago"]
		];

		

		//Verificar si registro existe
		$sql = "SELECT COUNT(*) FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' AND fecha_log = '{$trabajador["datelog"]}-01'";
		$res = $_DB->query($sql);
		$reg = $_DB->to_object($res)->count;
		if($reg) {
			//Update
			$arrQuery = array_replace($arrQuery, [
				'usuario_modificacion' => $_SESSION["us_id"],
				'fecha_modificacion' => 'now()'
			]);
			//echo json_encode($arrQuery, JSON_PRETTY_PRINT);
			$sql = "UPDATE {$client->db_name}.rrhh_trabajadores_datalog SET ".$this->utils->arrayToQuery([
				'action' => "update", 
				'array' => $arrQuery,
				'where' => "WHERE trabajador_id = {$id} AND fecha_log = '{$trabajador["datelog"]}-01'"
			]);
		} else {
			//Insert
			$arrQuery = array_replace($arrQuery, [
				'trabajador_id' => $id,
				'fecha_log' => $trabajador["datelog"].'-01',
				'usuario_id' => $_SESSION["us_id"],
				'fecha_creacion' => 'now()'
			]);
			$sql = "INSERT INTO {$client->db_name}.rrhh_trabajadores_datalog ".$this->utils->arrayToQuery("insert", $arrQuery);
		}

		if ($_DB->query($sql)) {
			return true;
		} else {
			return ob_get_clean();
		}
	}

	//POSTFORM: Remove Trabajador
	function fnDelete($list) {
		global $_DB;
		$list = json_decode($list);
		
		$errors = false;
		$return_res = array();
		foreach($list as $id) {
			$sql = "UPDATE rrhh_trabajadores SET
						eliminado = 1
						,usuario_modificacion = {$_SESSION['us_id']}
						,fecha_modificacion = now()
					WHERE id = {$id}";
			if ($_DB->query($sql) == false) {
				$errors = true;
			}
		}
		
		if ($errors) {
			$return_res["stat"] = "error";
			$return_res["desc"] = "Hubieron problemas al realizar la eliminación.";
		} else {
			$return_res["stat"] = "success";
			$return_res["desc"] = "Se eliminaron los registros seleccionados correctamente.";
		}
		return $return_res;
	}
	//POSTFORM: Trabajador Events
	function getTrabajadorEvents($id) {
		global $_DB;
		global $client;
		$events = [];
		
		$dbDates = $_DB->queryToArray("SELECT fecha_accion, accion FROM {$client->db_name}.rrhh_trabajadores_habilitacion WHERE trabajador_id = '{$id}' ORDER BY fecha_accion ASC;");
		if(!count($dbDates)) {
			$dbDateCreacion = $_DB->queryToSingleVal("SELECT fecha_accion FROM sys_historial WHERE registro_id = '{$id}' AND accion_id = '1'");

			$_DB->query("INSERT INTO {$client->db_name}.rrhh_trabajadores_habilitacion (trabajador_id, fecha_accion, accion) VALUES ('{$id}', '{$dbDateCreacion}', '1')");
			$dbDates = $_DB->queryToArray("SELECT fecha_accion, accion FROM {$client->db_name}.rrhh_trabajadores_habilitacion WHERE trabajador_id = '{$id}' ORDER BY fecha_accion ASC;");
		}
		
		// foreach ($dbDates as $dbDate) {
		// 	$dbDate = (object)$dbDate;
		// 	$events[] = ['accion' => $dbDate->accion, 'fecha_accion' => $dbDate->fecha_accion];
		// }
		
		return $dbDates;
	}
	function setTrabajadorEvents($id, $oevents) {
		global $_DB;
		global $client;
		$arreturn = [
			'text' => '',
			'type' => 'warning'
		];
		$events = [];
		$cevents = [];
		$jsonChars = ['"', '[', ']'];
		$sqlChars  = ["'", '(', ')'];
		
		//"raw" events array to standard array
		foreach ($oevents as $oevent) {
			$event = ['accion' => $oevent['event-type'], 'fecha_accion' => $oevent['event-date']];
			$events[] = $event;
		}
		//standard array to array of inserts
		foreach ($events as $event) {
			$rows[] = str_replace($jsonChars, $sqlChars, json_encode([$id, $event['accion'], $event['fecha_accion']]));
		}
		//Array of inserts to SQL standard multi insert syntax
		$iEvents = str_replace(['"', '[', ']'], "", json_encode($rows, JSON_PRETTY_PRINT));
		
		if ($rows) {
			//Delete
			$_DB->query("DELETE FROM {$client->db_name}.rrhh_trabajadores_habilitacion WHERE trabajador_id = {$id}");
			//Insert
			$insertStatements = "INSERT INTO {$client->db_name}.rrhh_trabajadores_habilitacion (trabajador_id, accion, fecha_accion) VALUES" . str_replace(['"', '[', ']'], "", json_encode($rows, JSON_PRETTY_PRINT));
			$res = $_DB->query($insertStatements);
			if ($res) {
				$arreturn['text'] = "Los eventos han sido guardados";
				$arreturn['type'] = "success";
			} else {
				$arreturn['text'] = "No ha sido posible guardar";
				$arreturn['type'] = "danger";
			}
		}
		
		return $arreturn;
	}
	
	function activeTrabajadorIDs($date, $inactive = false) {
		global $_DB;
		global $client;
		//array de todos los IDs de trabajadores
		$IDs = [];
		$sql = "SELECT id FROM {$client->db_name}.rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL)";
		$res = $_DB->queryToArray($sql);
		if(count($res)) {
			foreach ($res as $reg) {
				$reg = (object)$reg;
				$IDs[] = $reg->id;
			}
		}
		if (count($IDs)) {
			foreach ($IDs as $key => $id) {
				if ($inactive) {
					//Ver inactivos (quitar activos)
					if (self::checkTrabajadorActive($id, $date)) {
						unset($IDs[$key]);
					}
				} else {
					//Ver activos (quitar inactivos)
					if (!self::checkTrabajadorActive($id, $date)) {
						unset($IDs[$key]);
					}
				}
			}
		}
		return array_values($IDs);
	}
	
	function checkTrabajadorActive($id, $date) {
		$events = self::getTrabajadorEvents($id);
		$oFecha = DateTime::createFromFormat('!Y-m-d', $date.'-01');
		$switch = false;
		foreach ($events as $event) {
			$oFechaEvent = DateTime::createFromFormat('!Y-m-d', substr($event['fecha_accion'], 0, 10));
			//Ingreso
			if ($event['accion'] == 1 && ($oFechaEvent->format('Y-m') == $oFecha->format('Y-m') || $oFechaEvent <= $oFecha) && $switch == false) {
				if (isset($oFechaLastEvent)) {
					if ($oFechaLastEvent < $oFechaEvent) {
						$oFechaLastEvent = DateTime::createFromFormat('!Y-m-d', $event['fecha_accion']);
						$switch = true;
					}
				} else {
					$oFechaLastEvent = DateTime::createFromFormat('!Y-m-d', $event['fecha_accion']);
					$switch = true;
				}
			}
			//Retiro
			if ($event['accion'] == 2 && ($oFecha > $oFechaEvent) && $switch == true && $oFechaLastEvent < $oFechaEvent) {
				$oFechaLastEvent = DateTime::createFromFormat('!Y-m-d', $event['fecha_accion']);
				$switch = false;
			}
		}
		return $switch;
	}
	
	function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' ) {
		$datetime1 = date_create($date_1);
		$datetime2 = date_create($date_2);
		
		$interval = date_diff($datetime1, $datetime2);
		
		return $interval->format($differenceFormat);
	}
	
	function getRegionProvinciaByComuna($ComunaID) {
		global $_DB;
		
		if(is_numeric($ComunaID)) {
			$sql = "SELECT 
						utl_comunas.comuna_id, 
						utl_provincias.provincia_id,
						utl_regiones.region_id
					FROM utl_comunas
					INNER JOIN utl_provincias ON 
						utl_comunas.fk_provincia_id = utl_provincias.provincia_id
					INNER JOIN utl_regiones ON
						utl_provincias.fk_region_id = utl_regiones.region_id
					WHERE utl_comunas.comuna_id = '{$ComunaID}';";
			$res = $_DB->query($sql);
			if(!$_DB->num_rows($res) == 0) {
				return (array)$_DB->to_object($res);
			} else {
				return [];
			}
		}
	}
	
	//Log list
	function LogList($id) {
        global $_DB;
        global $client;
		//Listado de logs
		$logs = [];
		$lres = $_DB->query("SELECT 
								fecha_log,
								sueldo_base,
								(SELECT descripcion FROM {$client->db_name}.cont_centros_costos WHERE codigo = rrhh_trabajadores_datalog.centro_costo_id::text ) centro_costo,
								(SELECT nombre FROM {$client->db_name}.rrhh_cargos WHERE id = rrhh_trabajadores_datalog.cargo) cargo,
								(SELECT nombre FROM rrhh_afps WHERE id = rrhh_trabajadores_datalog.afp_id) afp,
								(SELECT nombre FROM rrhh_isapres WHERE id = rrhh_trabajadores_datalog.isapre_id) isapre
							FROM {$client->db_name}.rrhh_trabajadores_datalog 
							WHERE trabajador_id = '{$id}' 
							ORDER BY fecha_log DESC");
		while ($lreg = $_DB->to_object($lres)) {
			$log = [];
			$log["fecha_log"] = $lreg->fecha_log;
			$log["sueldo_base"] = $lreg->sueldo_base;
			$log["centro_costo"] = $lreg->centro_costo;
			$log["cargo"] = $lreg->cargo;
			$log["afp"] = $lreg->afp;
			$log["isapre"] = $lreg->isapre;
			$logs[] = $log;
		}
		return $logs;
	}
	
	
	//Combos Datos Personales
	function cboPaises() {
		$fi = new FormItem;
		$fi->setBasic("País de nacionalidad", "nacionalidad", null);
		$fi->setType("select", array(
			"table" => "utl_paises",
			"id" => "id",
			"text" => "nombre"
		));
		$fi->horizontal = true;
		return $fi;
	}
	function cboRegiones() {
		$fi = new FormItem([
            'label' => 'Región',
            'name' => 'region',
            'horizontal' => true,
            'type' => 'select',
            'type-params' => [
                'table' => 'utl_regiones',
                'id' => 'region_id',
                'text' => "(region_ordinal + ' - ' + region_nombre)",
                'text-alias' => 'nombre'
            ]
        ]);
		return $fi;
	}
	function cboProvincias($idregion) {
        $fi = new FormItem;
        $fi = new FormItem([
            'label' => 'Provincia',
            'name' => 'provincia',
            'horizontal' => true,
            'type' => 'select',
            'type-params' => [
                'table' => 'utl_provincias',
                'id' => 'provincia_id',
                'text' => 'provincia_nombre',
               // 'where' => "WHERE region_id = '{$idregion}'",
                'data' => [
                    ['fk_region_id', 'region_id']
                ]
            ]
        ]);
		return $fi;
	}
	function cboComunas($idprovincia) {
        $fi = new FormItem;
        $fi = new FormItem([
            'label' => 'Comuna',
            'name' => 'comuna',
            'horizontal' => true,
            'type' => 'select',
            'type-params' => [
                'table' => 'utl_comunas',
                'id' => 'comuna_id',
                'text' => 'comuna_nombre',
                //'where' => "WHERE provincia_id = '{$idprovincia}'",
                'data' => [
                    ['fk_provincia_id', 'provincia_id']
                ]
            ]
        ]);
		return $fi;
	}
	function cboNivelEstudios() {
		$fi = new FormItem;
		$fi->setBasic("Nivel de estudios", "estudios", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Educación Básica - Incompleta'],
				[2, 'Educación Básica - Completa'],
				[3, 'Educación Media - Incompleta'],
				[4, 'Educación Media - Completa'],
				[5, 'Educación Superior - Técnica - Incompleta'],
				[6, 'Educación Superior - Técnica - Completa'],
				[7, 'Educación Superior - Universitaria - Incompleta'],
				[8, 'Educación Superior - Universitaria - Completa'],
				[9, 'Educación Superior - Postgrado'],
				[10, 'Educación Superior - Magister'],
				[11, 'Educación Superior - Doctorado'],
				[12, 'Sin estudios']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboEstadoCivil() {
		$fi = new FormItem;
		$fi->setBasic("Estado Civil", "estadocivil", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Soltero'],
				[2, 'Casado'],
				[3, 'Viudo'],
				[4, 'Divorciado']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboSexo() {
		$fi = new FormItem;
		$fi->setBasic("Sexo", "sexo", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Masculino'],
				[2, 'Femenino']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	
	//Combos Datos Laborales
	function cboCC() {
		global $client;
		$fi = new FormItem;
		$fi->setBasic("Centro de Costos", "centrocostos", null);
		$fi->setType("select", array(
			"table" => "{$client->db_name}.cont_centros_costos",
			"id" => "id",
			"text" => "descripcion",
            "where" => "WHERE (NOT eliminado = '1' OR eliminado IS NULL)",
            "data" => [
                ["id", "subtext"]
            ]
		));
		$fi->horizontal = true;
		return $fi;
	}
	function cboTipoContrato() {
		$fi = new FormItem;
		$fi->setBasic("Tipo de contrato", "contrato", null);
		$fi->setType("select", [
			"table" => [
				[1, 'A Plazo Fijo'],
				[2, 'Indefinido'],
				[3, 'Boleta'],
				[4, 'Jubilado'],
				[5, 'Por Obra']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboCargos() {
        global $client;
		$fi = new FormItem;
		$fi->setBasic("Cargo", "cargo", null);
		$fi->setType("select", array(
			"table" => "{$client->db_name}.rrhh_cargos",
			"id" => "id",
			"text" => "nombre",
			"where" => "WHERE (NOT eliminado = '1' OR eliminado IS NULL) ORDER BY id"
		));
		$fi->setAddon('r', '<button id="cargo-edit" class="btn btn-default" type="button"><span class="glyphicon glyphicon-list-alt"></span></button>', 'btn');
		$fi->horizontal = true;
		return $fi;
	}
	function cboAFPs() {
		$fi = new FormItem;
		$fi->setBasic("Previsión", "afp", null);
		$fi->setType("select", array(
			"table" => "rrhh_afps",
			"id" => "id",
			"text" => "nombre",
			"where" => "WHERE (NOT eliminado = '1' OR eliminado IS NULL) ORDER BY id"
		));
		$fi->horizontal = true;
		return $fi;
	}
	function cboIsapres() {
		$fi = new FormItem;
		$fi->setBasic("Salud", "isapre", null);
		$fi->setType("select", array(
			"table" => "rrhh_isapres",
			"id" => "id",
			"text" => "nombre",
			"where" => "WHERE (NOT eliminado = '1' OR eliminado IS NULL) ORDER BY id"
		));
		$fi->horizontal = true;
		return $fi;
	}
	
	//Combos Datos de Pago
	function cboFormaPago() {
		$fi = new FormItem;
		$fi->setBasic("Forma de pago", "formapago", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Efectivo'],
				[2, 'Cheque'],
				[3, 'Vale Vista'],
				[4, 'Depósito']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboFPEfectivo() {
		$fi = new FormItem;
		$fi->setBasic("Entrega", "fp-efectivo-pago", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Entregado en empresa'],
				[2, 'Entregado en Servipag'],
				[3, 'Orden de pago']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboFPValeVista() {
		$fi = new FormItem;
		$fi->setBasic("Entrega", "fp-valevista-entrega", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Entregado en Mesón/Virtual'],
				[2, 'Enviado por Correo'],
				[3, 'Entregado en Empresa/Impreso']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboTipoCuenta() {
		$fi = new FormItem;
		$fi->setBasic("Tipo de cuenta", "fp-deposito-tipo", null);
		$fi->setType("select", [
			"table" => [
				[1, 'Cuenta Corriente'],
				[2, 'CrediChile'],
				[3, 'Chequera Electrónica BECH'],
				[4, 'Cuenta VISTA'],
				[5, 'Cuenta RUT'],
				[6, 'Cuenta de Ahorro'],
				[7, 'Cuenta de Intereses']
			]
		]);
		$fi->horizontal = true;
		return $fi;
	}
	function cboBancos() {
		$fi = new FormItem;
		$fi->setBasic("Banco", "fp-deposito-banco", null);
		$fi->setType("select", array(
			"table" => "utl_bancos",
			"id" => "id",
			"text" => "nombre",
			"where" => "WHERE (NOT eliminado = '1' OR eliminado IS NULL) ORDER BY id"
		));
		$fi->horizontal = true;
		return $fi;
	}

	//CARGO CRUD
	function DataCargo($id) {
        global $_DB;
        global $client;
		
		$sql = "SELECT * FROM {$client->db_name}.rrhh_cargos WHERE (NOT eliminado = '1' OR eliminado IS NULL) AND id = '".$id."';";
		$res = $_DB->query($sql);
		$arreturn = array();
		if(!$_DB->num_rows($res) == 0) {
			while($reg = $_DB->to_object($res)) {
				$arreturn["nombre"] = $reg->nombre;
				$arreturn["descripcion"] = $reg->descripcion;
			}
		}
		return $arreturn;
	}
	function UpdateCargo($id, $name, $description) {
		global $_DB;
		$return_res = Array();
		
		$sql = "UPDATE r_cargos
			SET
				nombre = '".$name."',
				descripcion = '".$description."',
				
				usuario_modificacion = '".$_SESSION["us_id"]."',
				fecha_modificacion = now()
			WHERE
				id = '".$id."'
			;
			";
		$res = $_DB->query($sql);
			if($res != false) {
				$return_res["title"] = "¡Éxito!";
				$return_res["stat"] = "success";
				$return_res["desc"] = "Cargo actualizado.";
			} else {
				$return_res["title"] = "Whoops...";
				$return_res["stat"] = "danger";
				$return_res["desc"] = "No ha sido posible actualizar el cargo.";
			}
			
		return $return_res;
	}
	function AddCargo($name, $description) {
		global $_DB;
		$return_res = Array();
		
		$sql = "INSERT INTO r_cargos(
					nombre
					,descripcion
					
					,usuario_id
					,fecha_creacion
				)VALUES(
					'".$name."'
					,'".$description."'
					
					,'".$_SESSION["us_id"]."'
					,now()
				)";
		$res = $_DB->query($sql);
			if($res != false) {
				$return_res["title"] = "¡Éxito!";
				$return_res["stat"] = "success";
				$return_res["desc"] = "Cargo ingresado correctamente.";
			} else {
				$return_res["title"] = "Whoops...";
				$return_res["stat"] = "danger";
				$return_res["desc"] = "No ha sido posible ingresar el cargo.";
			}
			
		return $return_res;
	}
	function DeleteCargo($id) {
		global $_DB;
		$return_res = Array();
		
		//Remove
		$sql = "UPDATE r_cargos SET eliminado = '1' WHERE id = '".$id."'";
		$res = $_DB->query($sql);
		if($res != false) {
			$return_res["title"] = "¡Éxito!";
			$return_res["stat"] = "success";
			$return_res["desc"] = "Cargo eliminado correctamente.";
		} else {
			$return_res["title"] = "Whoops...";
			$return_res["stat"] = "danger";
			$return_res["desc"] = "No ha sido posible eliminar el cargo.";
		}
		
		return $return_res;
    }
    
    function fnGetBonosDTConfig() {
        $dtNum = 0;
        return [
            [
				'targets' => $dtNum++,
				'title' => "ID",
                'data' => 'bono_id',
                'visible' => false,
                'searchable' => false,
                'editType' => 'id'
			],
			[
				'targets' => $dtNum++,
				'title' => "Nombre",
				'data' => 'bono_nombre',
				'editType' => 'string'
			],
			[
				'targets' => $dtNum++,
				'title' => "Valor",
				'data' => 'bono_valor',
				'width' => "200px",
				'editType' => 'anumeric',
				'editConfig' => [
                    'mDec' => 0,
                    'aSep' => '.',
                    'aDec' => ',',
                    'aPad' => false,
                    'lZero' => 'deny',
                    'wEmpty' => 'zero'
                ]
			],
			[
				'targets' => $dtNum++,
				'title' => "Tipo",
				'data' => 'bono_tipo',
				'width' => "150px",
				'editType' => 'select',
				'editData' => [
                    ['id' => 1, 'text' => 'Haber imponible'],
                    ['id' => 2, 'text' => 'Haber no imponible'],
                    ['id' => 3, 'text' => 'Descuento']
                ]
			],
			[
				'targets' => $dtNum++,
				'title' => "Acciones",
				'name' => 'actions',
				'data' => null,
				'width' => "105px",
				'defaultContent' => '',
				'editConfig' => [
					'deleteExisting' => false,
					'editExisting' => false
				]
			]
		];
    }
    function fnGetBonosDTEmptyRow() {
        return [
            'bono_id' => null,
            'bono_nombre' => null,
            'bono_valor' => null,
            'bono_tipo' => null,
            'estado' => 'edit'
        ];
    }

    function getBonos($where) {
		global $_DB;
		global $client;
		//Sin where no hay data amigo
		if (!$where) return [];
        $sql = "SELECT null as bono_id, bono_nombre, bono_valor, bono_tipo from {$client->db_name}.rrhh_trabajadores_bonos WHERE ".$this->utils->arrayToQuery("and", $where);
		return $_DB->query_to_array($sql);
    }
	
	//Bonos
	function setBonos($id, $jsonBonos, $month) {
		global $_DB;
		global $client;
        $formBonos = json_decode($jsonBonos, true);

        if($_DB->num_rows($_DB->query("SELECT bono_nombre, bono_valor, bono_tipo from {$client->db_name}.rrhh_trabajadores_bonos WHERE trabajador_id = '".$id."' AND fecha_log = '".$month."-01';")) != false) {
            //Remove old bonos
            $this->WipeBonos($id, $month);
        }
        
		if (count($formBonos)) {
			//Add new bonos
			foreach ($formBonos as $bono) {
				if ($bono["bono_nombre"] != "" && $bono["bono_valor"] != "" && $bono["bono_tipo"] != "")
				$this->InsertBono(array("id" => $id, "name" => $bono["bono_nombre"], "amount" => $bono["bono_valor"], "type" => $bono["bono_tipo"]), $month);
			}
		}
	}

	function InsertBono($bono, $month) {
		global $_DB;
		global $client;
		$sql = 
			"INSERT INTO {$client->db_name}.rrhh_trabajadores_bonos(
				trabajador_id,
				bono_nombre,
				bono_valor,
				bono_tipo,
				fecha_log
				) VALUES (
				'".$bono["id"]."',
				'".$bono["name"]."',
				'".$bono["amount"]."',
				'".$bono["type"]."',
				'".$month."-01'
            );";
        
		$res = $_DB->query($sql);
		if($res != false) {
			return true;
		} else {
			return false;
		}
	}

	function WipeBonos($id, $month) {
		global $_DB;
		global $client;
		$sql = "DELETE FROM {$client->db_name}.rrhh_trabajadores_bonos WHERE trabajador_id = '".$id."' AND fecha_log = '".$month."-01';";
		$res = $_DB->query($sql);
	}
	
	//Remove log
	function RemoveLog($id, $month) {
		global $_DB;
		global $client;
		//Remove
		$sql = "DELETE from {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' AND fecha_log = '{$month}'";
		$res = $_DB->query($sql);
		if($res != false) {
			$return_res["title"] = "¡Éxito!";
			$return_res["stat"] = "success";
			$return_res["desc"] = "Se ha eliminado el registro.";
			$return_res["loglist"] = json_encode(self::LogList($id));
		} else {
			$return_res["title"] = "Whoops...";
			$return_res["stat"] = "danger";
			$return_res["desc"] = "No ha sido posible eliminar el registro.";
		}
		
		return $return_res;
    }

    function fnGetCCsDTConfig() {
        $fiCC = $this->cboCC();
        $fiCC = $fiCC->selectOptionsBuilder(true);
        $dtNum = 0;
        return [
            [
                'targets' => $dtNum++,
                'title' => 'ID',
                'data' => 'trabajador_cc_id',
                'visible' => false,
                'searchable' => false,
                'editType' => 'id'
            ],
            [
                'targets' => $dtNum++,
                'title' => 'Centro de Costos',
                'data' => 'cc_id',
                'editType' => 'bselect',
				'editConfig' => [
					'liveSearch' => true
				],
				'editData' => $fiCC
            ],
            [
                'targets' => $dtNum++,
                'title' => 'Porcentaje',
                'data' => 'porcentaje',
                'width' => "200px",
				'editType' => 'anumeric',
				'editConfig' => [
                    'vMin' => 0,
                    'vMax' => 100,
                    'mDec' => 2,
                    'aSign' => '%',
                    'pSign' => 's',
                    'aDec' => ',',
                    'aPad' => false,
                    'lZero' => 'deny',
                    'wEmpty' => 'zero'
                ]
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

    function fnGetCCsDTEmptyRow() {
        return [
            'trabajador_cc_id' => null,
            'cc_id' => null,
            'porcentaje' => null,
            'estado' => 'edit'
        ];
	}
	
	function getCC($where) {
		global $_DB;
		global $client;
		//Sin where no hay data amigo
		if (!$where) return [];
		
		$sql = "SELECT
					trabajador_cc_id,
					cc_id,
					porcentaje,
					null as estado
				FROM {$client->db_name}.rrhh_trabajadores_cc
				WHERE ".$this->utils->arrayToQuery("and", $where)."";
		return $_DB->query_to_array($sql);
	}

	function setCC($id, $json, $date) {
		global $_DB;
		global $client;
		if (strlen($date) == 7) {
			$date = $date."-01";
		}
		$existingCC = $this->getCC([
			'trabajador_id' => $id,
			'fecha_log' => $date
		]);
		$ccs = json_decode($json, true);

		//Acciones para las cargas que vienen en el objeto
		foreach (array_keys($ccs) as $key) {
			/* id, rut, nombre, fecha_nacimiento, fecha_vencimiento, fecha_ingreso, tipo, parentesco, activo, estado */
			$data = [
				'cc_id' => $ccs[$key]['cc_id'],
				'porcentaje' => $ccs[$key]['porcentaje']
			];
			if ($ccs[$key]['trabajador_cc_id']) {
				//Update
				$_DB->query("UPDATE {$client->db_name}.rrhh_trabajadores_cc SET ".$this->utils->arrayToQuery("update", $data)." WHERE trabajador_cc_id = {$ccs[$key]['trabajador_cc_id']}");
			} else {
				//Insert
				$data = array_replace($data, [
					'trabajador_id' => $id,
					'fecha_log' => $date
				]);
				$_DB->query("INSERT INTO {$client->db_name}.rrhh_trabajadores_cc ".$this->utils->arrayToQuery("insert", $data));
			}
		}

		//Acciones para las cargas que no vienen en el objeto (eliminar)
		$idFinales = array_column($ccs, 'trabajador_cc_id');
		$idExistentes = array_column($existingCC, 'trabajador_cc_id');
		foreach ($idExistentes as $oldId) {
			if (!in_array($oldId, $idFinales)) {
				//Update
				$_DB->query("DELETE FROM {$client->db_name}.rrhh_trabajadores_cc WHERE trabajador_cc_id = {$oldId}");
			}
		}
	}
    
    function fnGetCargasFamiliaresDTConfig() {
        $dtNum = 0;
        return [
            [
				'targets' => $dtNum++,
				'title' => "ID",
                'data' => 'carga_id',
                'visible' => false,
                'searchable' => false,
                'editType' => 'id'
			],
			[
				'targets' => $dtNum++,
				'title' => "RUT",
				'data' => 'carga_rut',
				'editType' => 'rut'
			],
			[
				'targets' => $dtNum++,
				'title' => "Nombre",
				'data' => 'carga_nombre',
				'editType' => 'string'
			],
			[
				'targets' => $dtNum++,
				'title' => "Fecha Nacimiento",
				'data' => 'carga_fecha_nac',
				'editType' => 'dtpicker',
				'editConfig' => [
					'locale' => 'es',
					'format' => 'YYYY-MM-DD',
					'viewMode' => 'years'
				]
			],
			[
				'targets' => $dtNum++,
				'title' => "Fecha Vencimiento",
				'data' => 'carga_fecha_ven',
				'editType' => 'dtpicker',
				'editConfig' => [
					'locale' => 'es',
					'format' => 'YYYY-MM-DD',
					'viewMode' => 'years'
				]
			],
			[
				'targets' => $dtNum++,
				'title' => "Fecha Ingreso",
				'data' => 'carga_fecha_ing',
				'editType' => 'dtpicker',
				'editConfig' => [
					'locale' => 'es',
					'format' => 'YYYY-MM-DD',
					'viewMode' => 'years'
				]
			],
			[
				'targets' => $dtNum++,
				'title' => "Tipo",
				'data' => 'carga_tipo',
				'width' => "60px",
				'editType' => 'select',
				//editConfig => [liveSearch => true],
				'editData' => [["id" =>1,"text" =>"Sim."],["id" =>2,"text" =>"Mat."],["id" =>3,"text" =>"Inv."]]
			],
			[
				'targets' => $dtNum++,
				'title' => "Parentesco",
				'data' => 'carga_parentesco',
				'width' => "105px",
				'editType' => 'bselect',
				'editConfig' => [
					'liveSearch' => true
				],
				'editData' => [["id" =>1,"text" =>"Hijo"],["id" =>2,"text" =>"Cónyugue"],["id" =>3,"text" =>"Progenitor"],["id" =>4,"text" =>"Hermano"]]
			],
			[
				'targets' => $dtNum++,
				'title' => "Activo",
				'data' => 'activo',
				'editType' => 'checkbox'
			],
			[
				'targets' => $dtNum++,
				'title' => "Acciones",
				'name' => 'actions',
				'data' => null,
				'width' => "105px",
				'defaultContent' => '',
				'editConfig' => [
					'deleteExisting' => false,
					'editExisting' => false
				]
			]
		];
    }
    function fnGetCargasFamiliaresDTEmptyRow() {
        return [
            'carga_id' => null,
            'carga_rut' => null,
            'carga_nombre' => null,
            'carga_fecha_nac' => null,
            'carga_fecha_ven' => null,
            'carga_fecha_ing' => null,
            'carga_tipo' => null,
            'carga_parentesco' => null,
            'activo' => null,
            'estado' => 'edit'
        ];
    }
	
	function getCargas($where) {
		global $_DB;
		global $client;
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
				FROM {$client->db_name}.rrhh_trabajadores_cargas 
				WHERE ".$this->utils->arrayToQuery("and", $where)." AND (NOT eliminado = '1' OR eliminado IS NULL)
				ORDER BY carga_id";
		return $_DB->query_to_array($sql);
	}
	
	function setCargas($id, $cargasJson) {
		global $_DB;
		global $client;
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
				$_DB->query("UPDATE {$client->db_name}.rrhh_trabajadores_cargas SET ".$this->utils->arrayToQuery("update", $data)." WHERE carga_id = {$cargas[$key]['carga_id']}");
			} else {
				//Insert
				$data = array_replace($data, [
					'trabajador_id' => $id
				]);
				$_DB->query("INSERT INTO {$client->db_name}.rrhh_trabajadores_cargas ".$this->utils->arrayToQuery("insert", $data));
			}
		}

		//Acciones para las cargas que no vienen en el objeto (eliminar)
		$idFinales = array_column($cargas, 'carga_id');
		$idExistentes = array_column($existingCargas, 'carga_id');
		foreach ($idExistentes as $oldId) {
			if (!in_array($oldId, $idFinales)) {
				//Update
				$_DB->query("UPDATE {$client->db_name}.rrhh_trabajadores_cargas SET eliminado = 1 WHERE carga_id = {$oldId}");
			}
		}
	}
}

class rrhh_trabajador {
	public $arrHead;
	public $arrBody;
	private $arrBodyLib;
	
	public function __construct($id = null, $date = null) {
		//Los nombres de las llaves han sido ajustados para ser idénticos a los de la base de datos.
		//Una mejor manera de administrar los registros sería guardar el "body" en una variable diferente, la cual almacene los meses que se vayan cargando.
		
		//Head
		$this->arrHead = [
			"id" => null,
			"nombre" => null,
			"apellido_paterno" => null,
			"apellido_materno" => null,
			"rut" => null,
			"fecha_nacimiento" => null,
			"fono" => null,
			"fecha_ingreso" => null,
			"direccion" => null,
			"nivelestudios_id" => null,
			"email" => null,
			"estadocivil" => null,
			"sexo" => null,
			"nacionalidad_id" => null,
			"ciudad_origen" => null,
			"comuna_id" => null,
			"region_id" => null,
			"provincia_id" => null, 
		];
		
		//Body
		$this->arrBody = [
			"fecha_log" => null,
			"contrato_id" => null,
			"contrato_fin" => null,
			"contrato_hito" => null,
			"cargo" => null,
			"sueldo_base" => null,
			"gratificacion_legal" => null,
			"gratificacion_legal_mode" => null,
			"afp_id" => null,
			"isapre_id" => null,
			"bono_asistencia" => null,
			"bono_movilizacion" => null,
			"bono_colacion" => null,
			"centro_costo_id" => null,
			"horas_lun_vie" => null,
			"horas_sab_dom" => null,
			"sueldo_quincena" => null,
			"isapre_adicional" => null,
			"apv_uf" => null,
			"apv_porcentaje" => null,
			"apv_pactado" => null,
			"ccaf" => null,

			"formapago" => null,
			"fp-efectivo-pago" => null,
			"fp-valevista-entrega" => null,
			"fp-valevista-banco" => null,
			"fp-deposito-tipo" => null,
			"fp-deposito-banco" => null,
			"fp-deposito-ncuenta" => null
		];
		
		//Sobrecarga
		if (!is_null($id)) {
			$this->setHead($this->getTrabajadorHead($id));
		}
		if (!is_null($id) && !is_null($date)) {
			$this->setBody($this->getTrabajadorBody($id, $date));
		}
	}
	
	public function getHead($key = null) {
		if (is_null($key)) {
			return $this->arrHead;
		} elseif (array_key_exists($key, $this->arrHead)) {
			return $this->arrHead[$key];
		}
		return false;
	}
	
	public function getBody($date, $key = null) {
		if (array_key_exists($date, $this->arrBodyLib)) {
			if (is_null($key)) {
				return $this->arrBodyLib[$date];
			} elseif (array_key_exists($key, $this->arrBodyLib[$date])) {
				return $this->arrBodyLib[$date][$key];
			}
		}
		return false;
	}
	
	public function setHead($arrHead) {
		if (is_array($arrHead)) {
			foreach($arrHead as $key => $value) {
				if (array_key_exists($key, $this->arrHead)) {
					$this->arrHead[$key] = $value;
				}
			}
		}
	}
	
	public function setBody($arrBody) {
		if (is_array($arrBody)) {
			if (array_key_exists("fecha_log", $arrBody)) {
				$this->arrBodyLib[$arrBody["fecha_log"]] = $this->arrBody;
				foreach($arrBody as $key => $value) {
					if (array_key_exists($key, $this->arrBody)) {
						$this->arrBodyLib[$arrBody["fecha_log"]][$key] = $value;
					}
				}
			}
		}
	}
	
	public function getLastBody($key = null) {
		if (isset($this->arrBodyLib) && is_null($key)) {
			return end($this->arrBodyLib);
		} elseif (isset($this->arrBodyLib) && array_key_exists($key, $this->arrBody)) {
			return end($this->arrBodyLib)[$key];
		}
	}
}