<?php
class rrhh_remun_model {
	public $months = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");

	function __construnct() {

	}

	//Main Module
	function MainModule() {
		$months = $this->months;
		
		$labels = array();
		$content = array();
		
		$content_html = mb_convert_encoding( file_get_contents( pathSite . 'view/apps/html/trabajadores/remun.html') , 'HTML-ENTITIES', "ISO-8859-1");
		$labels[] = "@MONTHSTRYEAR@";
		$content[] = $months[(string)date('m')]." ".date('Y');
		$labels[] = "@YEAR@";
		$content[] = (string)date('Y');
		$labels[] = "@MONTH@";
		$content[] = (string)date('m');
		
		$content_html = str_replace($labels, $content, $content_html);
		return $content_html;
	}

	function IM($year,$month) {
		global $_DB;
		global $client;
		$IM = array();
		//Datos Empresa
		$sql = "SELECT razon_social, rut, direccion, telefono, nombre_representante, rut_representante FROM sys_clients_plus WHERE id_cliente = {$client->client_id} AND (NOT eliminado = 1 OR eliminado IS NULL)";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["X45"] = $reg->razon_social;
			$IM["X46"] = $reg->rut;
			$IM["X50"] = $reg->direccion;
			$IM["X49"] = $reg->telefono;
			$IM["X47"] = $reg->nombre_representante;
			$IM["X48"] = $reg->rut_representante;
		}
		//Indicadores Mensuales
		$sql = "SELECT TOP 1 uf, utm, uta, ipc, ipc12, dolar FROM rrhh_indicadores_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["R47"] = $reg->uf;
			$IM["R48"] = $reg->utm;
			$IM["R49"] = $reg->uta;
			$IM["R50"] = $reg->ipc;
			$IM["R51"] = $reg->ipc12;
			$IM["R52"] = $reg->dolar;
		}
		//Asignación Familiar
		$sql = "SELECT TOP 1 af_a_monto, af_a_tope, af_b_monto, af_b_tope, af_c_monto, af_c_tope, af_d_monto FROM rrhh_af_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["B60"] = $reg->af_a_monto;
			$IM["E60"] = 0;
			$IM["G60"] = $reg->af_a_tope;
			$IM["B61"] = $reg->af_b_monto;
			$IM["E61"] = $reg->af_a_tope;
			$IM["G61"] = $reg->af_b_tope;
			$IM["B62"] = $reg->af_c_monto;
			$IM["E62"] = $reg->af_b_tope;
			$IM["G62"] = $reg->af_c_tope;
			$IM["B63"] = $reg->af_d_monto;
			$IM["E63"] = $reg->af_c_tope;
		}
		//Renta Mínima
		$sql = "SELECT TOP 1 rm_topegratificacion, rm_depindep, rm_18_65, rm_casaparticular FROM rrhh_rm_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC;";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["O59"] = $reg->rm_topegratificacion;
			$IM["Q59"] = $reg->rm_depindep;
			$IM["Q60"] = $reg->rm_18_65;
			$IM["Q61"] = $reg->rm_casaparticular;
		}
		//APV
		$sql = "SELECT TOP 1 apv_topemensual, apv_topeanual, apv_topeanualdepcon FROM rrhh_apv_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC;";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["D65"] = $reg->apv_topemensual;
			$IM["E65"] = $IM["R47"] * $IM["D65"];
			$IM["D66"] = $reg->apv_topeanual;
			$IM["E66"] = $IM["R47"] * $IM["D66"];
			$IM["D68"] = $reg->apv_topeanualdepcon;
			$IM["E68"] = $IM["R47"] * $IM["D68"];
		}
		//Cotizacion Trabajos Pesados
		$sql = "SELECT TOP 1 ctp_p_cotizacion, ctp_p_finempleador, ctp_p_fintrabajador, ctp_mp_cotizacion, ctp_mp_finempleador, ctp_mp_fintrabajador FROM rrhh_ctp_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC;";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["M66"] = $reg->ctp_p_cotizacion;
			$IM["N66"] = $reg->ctp_p_finempleador;
			$IM["P66"] = $reg->ctp_p_fintrabajador;
			$IM["M67"] = $reg->ctp_mp_cotizacion;
			$IM["N67"] = $reg->ctp_mp_finempleador;
			$IM["P67"] = $reg->ctp_mp_fintrabajador;
		}
		//Seguro de Cesantía
		$sql = "SELECT TOP 1 sdc_indef_empleador, sdc_indef_trabajador, sdc_fijo_empleador, sdc_fijo_trabajador FROM rrhh_sdc_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC;";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["E72"] = $reg->sdc_indef_empleador;
			$IM["G72"] = $reg->sdc_indef_trabajador;
			$IM["E73"] = $reg->sdc_fijo_empleador;
			$IM["G73"] = $reg->sdc_fijo_trabajador;
		}
		//Tope Imponibles
		$sql = "SELECT TOP 1 ti_afp, ti_ips, ti_sdc, ti_fonasaisapre FROM rrhh_ti_datalog WHERE fecha_log <= '".$year."-".$month."-01' ORDER BY fecha_log DESC;";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$IM["N69"] = $reg->ti_afp;
			$IM["O69"] = $IM["R47"] * $IM["N69"];
			$IM["N70"] = $reg->ti_ips;
			$IM["O70"] = $IM["R47"] * $IM["N70"];
			$IM["N71"] = $reg->ti_sdc;
			$IM["O71"] = $IM["R47"] * $IM["N71"];
			$IM["O72"] = $reg->ti_fonasaisapre;
		}
		
		return $IM;
	}

	//HORAS
	function TimePeriodTotalHours($id, $year, $month) {
		global $_DB;
		global $client;
		$months = $this->months;
		
		$DC = array();
		$IM = $this->IM($year, $month);
		$workerdata = array();
		
		//Fecha
		$DC["T84"] = strtoupper($months[$month]) . " DEL " . $year;
		$DC["YEAR"] = $year;
		$DC["MONTH"] = $month;
		
		//::DB Extraction
		//DATA Trabajador
		$sql = "SELECT * FROM {$client->db_name}.rrhh_trabajadores WHERE (NOT eliminado = '1' OR eliminado IS NULL) and id = '".$id."';";
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$DC["T141"] = $reg->nombre." ".$reg->apellido_paterno." ".$reg->apellido_materno;
			$DC["T142"] = $reg->rut;
			$DC["T154"] = $reg->fecha_ingreso;
			$DC["Activo"] = $reg->activo;
		}
		//Datalog Trabajador
		$res = $_DB->query("SELECT TOP 1 fecha_log FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' ORDER BY fecha_log ASC");
		$reg = $_DB->to_object($res);

		var_dump($reg);
		
		$oldestLog = DateTime::createFromFormat('!Y-m-d', $reg->fecha_log);
		$requestedLog = DateTime::createFromFormat('!Y-m-d', $year."-".$month."-01");
		$ingresoLog = DateTime::createFromFormat('!Y-m-d', $DC["T154"]);
		
		//Fin contrato_id
		if ($DC["Activo"] == 0) {
			$res = $_DB->query("SELECT TOP 1 fecha_accion FROM {$client->db_name}.rrhh_trabajadores_habilitacion WHERE trabajador_id = '{$id}' ORDER BY fecha_accion DESC");
			$reg = $_DB->to_object($res);
			$fincontratoLog = DateTime::createFromFormat('!Y-m-d', $reg->fecha_accion);
			$DC["Fecha-Fin"] = $reg->fecha_accion;
		}
		
		
		if ($requestedLog >= $ingresoLog || $requestedLog->format('Y-m') == $ingresoLog->format('Y-m')) {
			if ($requestedLog <= $oldestLog) {
				$sql = "SELECT TOP 1 * FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' ORDER BY fecha_log ASC;";
			} else {
				$sql = "SELECT TOP 1 * FROM {$client->db_name}.rrhh_trabajadores_datalog WHERE trabajador_id = '{$id}' AND fecha_log <= '{$year}-{$month}-01' ORDER BY fecha_log DESC;";
			}
		} else {
			$DC["id"] = "-1";
			$DC["val"] = $ingresoLog->format('Y-m');
			return $DC;
		}
		
		$res = $_DB->query($sql);
		if(!$_DB->num_rows($res) == 0) {
			$reg = $_DB->to_object($res);
			$DC["G7"] = $reg->horas_lun_vie;
			$DC["P7"] = $reg->horas_sab_dom;
			$DC["T148"] = $reg->sueldo_base != null ? number_format($reg->sueldo_base, 0, ',', '') : 0;
			$DC["N94"] = $reg->gratificacion_legal != null ? number_format($reg->gratificacion_legal, 0, ',', '') : 0;
			$DC["N94-mode"] = $reg->gratificacion_legal_mode != null ? $reg->gratificacion_legal_mode : 1;
			$DC["T152"] = $reg->afp_id != null ? $reg->afp_id : 0;
			$DC["T153"] = $reg->isapre_id != null ? $reg->isapre_id : 0;
			
			$DC["T145"] = 
				($reg->contrato_id == 1
					? "1.- A PLAZO FIJO"
					: ($reg->contrato_id == 2
						? "2.- INDEFINIDO"
						: ($reg->contrato_id == 3
							? "3.- BOLETA"
							: ($reg->contrato_id == 4
								? "4.- JUBILADO"
								: ($reg->contrato_id == 5
									? "5.- POR OBRA"
									: ""
				)))));
			
			$DC["T147"] = $reg->centro_costo_id != null ? $_DB->to_object($_DB->query("SELECT descripcion FROM {$client->db_name}.cont_centros_costos WHERE codigo = '{$reg->centro_costo_id}' AND cliente_id = {$_SESSION["ss_cliente"]}"))->descripcion : "";
			$DC["T146"] = $reg->cargo != null ? $_DB->to_object($_DB->query("SELECT nombre FROM {$client->db_name}.rrhh_cargos WHERE id = '".$reg->cargo."'"))->nombre : "";
			$DC["T151"] = $reg->bono_asistencia != null ? number_format($reg->bono_asistencia, 0, '.', '') : 0;
			$DC["T150"] = $reg->bono_movilizacion != null ? number_format($reg->bono_movilizacion, 0, ',', '') : 0;
			$DC["T149"] = $reg->bono_colacion != null ? number_format($reg->bono_colacion, 0, '.', '') : 0;
			$DC["D100"] = $reg->sueldo_quincena != null ? number_format($reg->sueldo_quincena, 0, '.', '') : 0;
			$DC["P106"] = $reg->isapre_adicional != null ? number_format($reg->isapre_adicional, 3, '.', '') : 0;
			$DC["P107"] = $reg->apv_uf != null ? number_format($reg->apv_uf, 2, '.', '') : 0;
			$DC["P108"] = $reg->apv_porcentaje != null ? number_format($reg->apv_porcentaje, 2, '.', '') : 0;
			$DC["P109"] = $reg->apv_pactado != null ? number_format($reg->apv_pactado, 0, '.', '') : 0;
			$DC["P110"] = $reg->ccaf != null ? number_format($reg->ccaf, 2, '.', '') : 0;
			
			//Bonos personalizados
			$dbBonos = $_DB->query("SELECT bono_nombre, bono_valor, bono_tipo FROM {$client->db_name}.rrhh_trabajadores_bonos WHERE trabajador_id = '".$id."' AND fecha_log = '".$reg->fecha_log."';");
		}
		//Horas
		$sql = "SELECT DATEPART(DAY, dia) as dia, eman, sman, etar, star, bonos, descuentos, status FROM {$client->db_name}.rrhh_horas_trabajador WHERE trabajador_id = {$id} AND DATEPART(MONTH, dia) = {$month} AND DATEPART(YEAR, dia) = {$year} ORDER BY dia ASC;";
		$res = $_DB->query($sql);
		$dbDays = array();
		if(!$_DB->num_rows($res) == 0) {
			while($reg = $_DB->to_object($res)) {
				$dbDays[$reg->dia] = Array("eman" => $reg->eman, "sman" => $reg->sman, "etar" => $reg->etar, "star" => $reg->star, "status" => $reg->status, "bonos" => $reg->bonos, "descuentos" => $reg->descuentos);
			}
		} else {
			$dbDays = null;
		}
		//AFP
		if ($DC["T152"]) {
			$sql = "SELECT TOP 1 d.tasa_dependiente, d.sis_dependiente, h.nombre FROM rrhh_afps_datalog as d LEFT JOIN rrhh_afps as h ON d.afps_id = h.id WHERE d.afps_id = '".$DC["T152"]."' AND d.fecha_log <= '".$year."-".$month."-01' ORDER BY d.fecha_log DESC;";
			$res = $_DB->query($sql);
			if(!$_DB->num_rows($res) == 0) {
				$reg = $_DB->to_object($res);
				$DC["NombreAFP"] = $reg->nombre;
				$DC["P103"] = $reg->tasa_dependiente;
				$DC["AFP-SIS"] = $reg->sis_dependiente;
			}
		} else {
			$DC["NombreAFP"] = "Sin definir";
			$DC["P103"] = 0;
			$DC["AFP-SIS"] = 0;
		}
		
		//Isapre
		if ($DC["T153"]) {
			$sql = "SELECT TOP 1 d.tasa_dependiente, h.nombre FROM rrhh_isapres_datalog as d LEFT JOIN rrhh_isapres as h ON d.isapres_id = h.id WHERE d.isapres_id = '".$DC["T153"]."' AND d.fecha_log <= '".$year."-".$month."-01' ORDER BY d.fecha_log DESC;";
			$res = $_DB->query($sql);
			if(!$_DB->num_rows($res) == 0) {
				$reg = $_DB->to_object($res);
				$DC["NombreIsapre"] = $reg->nombre;
				$DC["P104"] = $reg->tasa_dependiente;
			}
		} else {
			$DC["NombreIsapre"] = "Sin definir";
			$DC["P104"] = 0;
		}
		
		//Build assist status tree
		$sql = "SELECT * FROM rrhh_horas_status;";
		$res = $_DB->query($sql);
		$dbDayStatus = array();
		if(!$_DB->num_rows($res) == 0) {
			while($reg = $_DB->to_object($res)) {
				$dbDayStatus[$reg->status] = Array("text" => $reg->text, "style" => $reg->style, "locked" => $reg->locked);
			}
		}
		
		$tope_lunvie = new DateInterval(sprintf('PT%dH', $DC["G7"]));
		$tope_sabdom = new DateInterval(sprintf('PT%dH', $DC["P7"]));
		
		$totalMonth = new DateTime('00:00');
		$totalMonthExtra = new DateTime('00:00');
		$totalMonthAtraso = new DateTime('00:00');
		$floorTime = new DateTime('00:00');
		
		$totalmdays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$iday = 1;
		
		$DC["D94"] = 0;
		$DC["E94"] = 0;
		$DC["D96"] = 0;
		$DC["D97"] = 0;
		$DC["N96"] = 0;
		$DC["P96"] = 0; //Bonos
		$DC["D110"] = 0; //Anticipos
		
		//Starting month threshold
		if ($ingresoLog && $requestedLog->format('Y-m') == $ingresoLog->format('Y-m') && $ingresoLog->format('j') != "1") {
			$DC["D94"] = $DC["D94"] + ($ingresoLog->format('j')-1);
		}
		
		//End date threshold
		if ($DC["Activo"] == 0 && $fincontratoLog && $requestedLog->format('Y-m') == $fincontratoLog->format('Y-m') && $requestedLog->format('t') != $fincontratoLog->format('j')) {
			$DC["D94"] = $DC["D94"] + ($requestedLog->format('t') - $fincontratoLog->format('j'));
		}
		
		$DC["StatusAsistencia"] = [];
		
		while($iday <= $totalmdays) { 
			$dayofweek = date('w', strtotime($year.'-'.$month.'-'.$iday));
			if (is_array($dbDays) && array_key_exists($iday,$dbDays)) {
				//HORAS EXTRA Y ATRASOS
				if ($dbDays[$iday]["status"] == "A" || $dbDays[$iday]["status"] == "F2") {
					//Día de la semana
					//6= Sábado, 0= Domingo
					
					$eman = new DateTime($dbDays[$iday]["eman"]);
					$sman = new DateTime($dbDays[$iday]["sman"]);
					$interval1 = $eman->diff($sman);
					
					$etar = new DateTime($dbDays[$iday]["etar"]);
					$star = new DateTime($dbDays[$iday]["star"]);
					$interval2 = $etar->diff($star);
					
					$totalDay = new DateTime('00:00');
					$totalDay->add($interval1);
					$totalDay->add($interval2);
					
					if($dayofweek == 6 || $dayofweek == 0) {
						$totalDay->sub($tope_sabdom);
					} else {
						$totalDay->sub($tope_lunvie);
					}
					
					if($floorTime->diff($totalDay)->format("%r") == "-") {
						$totalMonthAtraso->add($floorTime->diff($totalDay));
					} elseif($floorTime->diff($totalDay)->format("%H:%I") != "00:00") {
						$totalMonthExtra->add($floorTime->diff($totalDay));
					}
					
					//Bonos y anticipos
					if ((int)$dbDays[$iday]["bonos"] > 0 || (int)$dbDays[$iday]["bonos"] != null) {
						$DC["P96"] += (int)$dbDays[$iday]["bonos"];
						$DC["N96"] += 1;
					}
					if ((int)$dbDays[$iday]["descuentos"] > 0 || (int)$dbDays[$iday]["descuentos"] != null) {
						$DC["D110"] += (int)$dbDays[$iday]["descuentos"];
					}
					
					$totalMonth->add($interval1);
					$totalMonth->add($interval2);
				}
				//
				if ($dbDays[$iday]["status"]) {
					$DC["StatusAsistencia"][$iday] = $dbDays[$iday]["status"];
				}
				//FALTAS
				if ($dbDays[$iday]["status"] == "F") { //Día entero
					$DC["D94"] += 1;
				} elseif ($dbDays[$iday]["status"] == "F2") { //Medio día
					$DC["E94"] += 0.5;
				}
				//PERMISOS
				if ($dbDays[$iday]["status"] == "P") {
					$DC["D96"] += 1;
				}
				//LICENCIAS
				if ($dbDays[$iday]["status"] == "L") {
					$DC["D97"] += 1;
				}
			} else {
				//$DC["D94"] += 1;
			}
			$iday++;
		}
		
		//Días contables
		$DC["F123"] = 30-($DC["D94"]+$DC["E94"]+$DC["D96"]+$DC["D97"]);
		//Total Horas Mes
		$difftotal = $floorTime->diff($totalMonth);
		$DC["H-TotalMes"] = ($difftotal->h + $difftotal->days*24) + ((float)$difftotal->i / 60);
		//Total Horas Extra
		$diffextra = $floorTime->diff($totalMonthExtra);
		$DC["N95"] = ($diffextra->h + $diffextra->days*24) + ((float)$diffextra->i / 60);
		//Total Atrasos
		$diffatraso = $floorTime->diff($totalMonthAtraso);
		$DC["D95"] = $diffatraso->h > 0 ? ($diffatraso->h + $diffatraso->days*24) + ((float)$diffatraso->i / 60) : 0;
		//Balance Horas Extra
		$DC["H-BalanceExtra"] = $DC["H-TotalExtra"] > $DC["H-TotalAtraso"] ? $DC["H-TotalExtra"] - $DC["H-TotalAtraso"] : 0;
		//Balance Atrasos
		$DC["H-BalanceAtraso"] = $DC["H-TotalAtraso"] > $DC["H-TotalExtra"] ? $DC["H-TotalAtraso"] - $DC["H-TotalExtra"] : 0;
		
		
		
		//Montos
		//Fallas o atrasos
		$DC["F94"] = round($DC["D94"] * ($DC["T148"] / 30)); //Fallas sin motivo
		$DC["F95"] = round(($DC["D95"] * 24) * ((($DC["T148"] / 30) * 28) / (180*24))); //Atrasos o retiros
		$DC["F96"] = round($DC["D96"] * ($DC["T148"] / 30)); //Fallas justificadas
		$DC["F97"] = round($DC["D97"] * ($DC["T148"] / 30)); //Licencias médicas
		//Detalles Liquidación
		$DC["D123"] = round(($DC["T148"] - ($DC["F94"]+$DC["F97"]+$DC["F96"])) + ($DC["M81"]*($DC["T148"]/30)));
		$DC["D125"] = $DC["F95"];
		
		//.:Bonos Imponibles:.
		//Bono de asistencia
		$DC["N98"] = $DC["T151"];
		
		$DC["P98"]=round(
			((((($DC["D94"]+$DC["E94"])<0.1
				? $DC["N98"]
				: ($DC["D94"]+$DC["E94"])<0.6)
					?($DC["N98"]/4)*3
					:($DC["D94"]+$DC["E94"])<1.1)
						?$DC["N98"]/2
						:($DC["D94"]+$DC["E94"])<1.6)
							?$DC["N98"]/4
							:0)
		);
		//Bonos imponibles personalizados
		$DC["HaberesImponibles"] = array();
		$DC["HaberesImponiblesSum"] = 0;
		$DC["HaberesNoImponibles"] = array();
		$DC["HaberesNoImponiblesSum"] = 0;
		$DC["Descuentos"] = array();
		$DC["DescuentosSum"] = 0;
		if($_DB->num_rows($dbBonos) != false) { 
			while($dbBono = $_DB->to_object($dbBonos)) {
				if ($dbBono->bono_tipo == "1") {
					$DC["HaberesImponiblesSum"] += $dbBono->bono_valor;
					$DC["HaberesImponibles"][] = array($dbBono->bono_nombre, $dbBono->bono_valor);
				} elseif ($dbBono->bono_tipo == "2") {
					$DC["HaberesNoImponiblesSum"] += $dbBono->bono_valor;
					$DC["HaberesNoImponibles"][] = array($dbBono->bono_nombre, $dbBono->bono_valor);
				} elseif ($dbBono->bono_tipo == "3") {
					$DC["DescuentosSum"] += $dbBono->bono_valor;
					$DC["Descuentos"][] = array($dbBono->bono_nombre, $dbBono->bono_valor);
				}
			}
		}
		
		//Gratificacion y otros
		$DC["P95"] = round($DC["N95"] > 0 ? ($DC["T148"]*0.0077777) * $DC["N95"] : 0); //Horas extra
		//Gratificacion legal
		if ($DC["D97"] > 0) {
			$DC["P94"] = 
					round(((($IM["Q59"]*$IM["O59"])/12)/30)*$DC["F123"],0,PHP_ROUND_HALF_DOWN)
				;
			$DC["N94"] = "Gratificación licencia";
		} else {
			if ($DC["N94-mode"] == 1) { //25%
				$DC["P94"] = round(
					(($DC["D123"]+$DC["P95"]+$DC["P96"]+$DC["P97"]+$DC["P98"]+$DC["P99"]+$DC["HaberesImponiblesSum"]-$DC["F95"])*0.25) > (($IM["Q59"]*$IM["O59"])/12)
						? (($IM["Q59"]*$IM["O59"])/12)
						: (($DC["D123"]+$DC["P95"]+$DC["P96"]+$DC["P97"]+$DC["P98"]+$DC["P99"]+$DC["HaberesImponiblesSum"]-$DC["F95"])*0.25)
					);
				$DC["N94"] = "25% sueldo Imponible Tope Gratificación";
			} elseif ($DC["N94-mode"] == 2) {
				$DC["P94"] = round( (($IM["Q59"]*$IM["O59"])/12) );
				$DC["N94"] = "Máxima gratificación";
			} elseif ($DC["N94-mode"] == 3) { //Nada -- 0
				$DC["P94"] = $DC["N94"];
				$DC["N94"] = "Monto a elección";
			}
		}
		
		//Detalles Liquidación
		$DC["D124"] = $DC["P95"]; //Horas extras
		$DC["D126"] = round($DC["P94"] + $DC["P97"] + $DC["P98"] + $DC["P99"] + $DC["P96"] + $DC["HaberesImponiblesSum"]); //Imponibles
		$DC["D127"] = round(($DC["D123"] + $DC["D124"] + $DC["D126"]) - $DC["D125"]); //Total imponible
		
		//Descuentos legales
		$DC["Q103"] = round(
			$DC["D127"] > $IM["O69"] 
				? $IM["O69"] * ($DC["P103"]/100) 
				: $DC["D127"] * ($DC["P103"]/100)
			); //AFP
		$DC["Q104"] = round(
			$DC["D127"] > $IM["O69"] 
				? $IM["O69"] * ($DC["P104"]/100) 
				: $DC["D127"] * ($DC["P104"]/100)
			); //Isapre
		$DC["P105"] = $DC["T145"] == 2 ? 0.6 : 0; //% Seguro Cesantia
		$DC["Q105"] = round(
			(($DC["T145"] == 3 
				? 0 
				: $DC["D127"] > $IM["O71"]) 
					? $IM["O71"] * ($DC["P105"]/100)
					: $DC["D127"] * ($DC["P105"]/100))
			);//$ Seguro Cesantia
		$DC["Q106"] = 
			((($DC["T145"] == 3 //Si es boleta
				? 0
				: $DC["P106"] == 0)
					? 0
					: ($DC["P106"] * $IM["R47"]) > $DC["Q104"]) 
						? ($DC["P106"] * $IM["R47"]) - $DC["Q104"] 
						: 0)
		; //Adicional salud
		$DC["Q107"] = round(
			$DC["P107"] * $IM["R47"] < $IM["E65"]
				? $DC["P107"] * $IM["R47"]
				: $IM["E65"]
		); //APV UF
		$DC["Q108"] = round(
			($DC["P108"]/100) * $DC["D127"] < $IM["E65"]
				?  $DC["D127"] * ($DC["P108"]/100)
				: $IM["E65"]
		); //APV %
		$DC["Q109"] = round (
			$DC["P109"] < $IM["E65"]
				? $DC["P109"]
				: $IM["E65"]
		); //APV $
		$DC["Q110"] = round(
			$DC["T145"] == 3
				? 0
				: $DC["D129"] * $DC["P110"]
		); //CCAF
		$DC["P111"] = round($DC["Q103"]+$DC["Q104"]+$DC["Q105"]+$DC["Q106"]+$DC["Q107"]+$DC["Q108"]+$DC["Q109"]+$DC["Q110"]); //Total descuentos legales
		
		$IM["P74"] = "SI";
		
		//Bonos no imponibles
		$DC["D114"] = ($DC["T149"]/30)*(30-($DC["D94"]+$DC["E94"]+$DC["D96"]+$DC["D97"]));//Bono Colacion
		$DC["D115"] = ($DC["T150"]/30)*(30-($DC["D94"]+$DC["E94"]+$DC["D96"]+$DC["D97"]));//Bono Movilizacion
		//Total bonos
		$DC["D120"] = round($DC["D114"]+$DC["D115"]+$DC["D116"]+$DC["D117"]+$DC["D118"]+$DC["D119"]+$DC["HaberesNoImponiblesSum"]);
		
		//Detalles liquidacion
		$DC["D128"] = round($DC["D120"]); //No imponible
		$DC["D129"] = round($DC["D127"]+$DC["D128"]); //Total haberes
		
		//Impuesto a la renta
		$DC["N113"] = round(
			$DC["T145"] == 3 //Si trabaja con boleta
				? 0 
				: ($IM["P74"] == "SI" 
					? (($DC["Q104"]+$DC["Q106"]) <= ($IM["R47"]*$IM["O72"]) 
						? $DC["D127"]-($DC["Q103"]+$DC["Q104"]+$DC["Q105"]+$DC["Q106"]+$DC["Q107"]+$DC["Q108"]+$DC["Q109"]) 
						: $DC["D127"]-(($IM["R47"]*$IM["O72"])+$DC["Q103"]+$DC["Q105"]+$DC["Q107"]+$DC["Q108"]+$DC["Q109"]))
					:(($DC["Q104"])<=($IM["R47"]*$IM["O72"])
						? $DC["D127"]-($DC["Q103"]+$DC["Q104"]+$DC["Q105"])
						: $DC["D127"]-(($IM["R47"]*$IM["O72"])+$DC["Q103"]+$DC["Q105"])))
		); //Total tributable
		$DC["Q113"] = $IM["N69"]; //Tope UF
		$DC["M114"] = 
			(($DC["T145"] == 3 //Si trabaja con boleta
				? 0 
				: ($DC["D94"]+$DC["E94"]+$DC["D97"]+$DC["D96"]) >= 30)
					? 0
					: $this->CalcImp2da($IM["R48"], "factor", $DC["N113"]))
		; //Factor tramo
		$DC["P114"] = round(
			(($DC["T145"] == 3 //Si trabaja con boleta
				? 0 
				: ($DC["D94"]+$DC["E94"]+$DC["D97"]+$DC["D96"]) >= 30)
					? 0
					: $this->CalcImp2da($IM["R48"], "rebaja", $DC["N113"]))
		); //Rebaja
		$DC["M115"] = round(
			$DC["T145"] == 3 //Si trabaja con boleta
				? 0 
				: $IM["R47"] * $DC["Q113"]
		); //Tope UF
		$DC["Q115"] = round(
			$DC["T145"] == 3 //Si trabaja con boleta
				? 0 
				: round(($DC["N113"]*$DC["M114"])-($DC["P114"]))
		); //Total Descuentos legales
		
		$DC["D111"] = round($DC["D100"]+$DC["D101"]+$DC["D102"]+$DC["D103"]+$DC["D104"]+$DC["D105"]+$DC["D106"]+$DC["D107"]+$DC["D108"]+$DC["D109"]+$DC["D110"]+$DC["DescuentosSum"]); //Total descuentos no imponibles
		
		//Detalles liquidación
		$DC["D130"] = round($DC["Q115"]+$DC["P117"]+$DC["P118"]+$DC["P119"]+$DC["P120"]+$DC["P111"]);
		$DC["D131"] = round($DC["D111"]);
		$DC["D132"] = round($DC["N113"]);
		$DC["D133"] = round($DC["D129"]-$DC["D130"]-$DC["D131"]);
		
		//[Gastos adicionales empleador]
		//Seguro de invalidez y sobrevivencia
		$DC["P123"] =
			$DC["T145"] != 4
				? $DC["AFP-SIS"]
				: 0;
		$DC["Q123"] = round(
			$DC["D127"] < $IM["O69"]
				? $DC["D127"] * ($DC["P123"]/100)
				: $IM["O69"] * ($DC["P123"]/100)
		);
		//Seguro de cesantia
		$DC["P126"] =
			(($DC["T145"] == 1 || $DC["T145"] == 5
				? 3
				: $DC["T145"] == 2)
					? 2.4
					: 0);
		$DC["Q126"] = round(
			$DC["D127"] > $IM["O71"]
				? $IM["O71"] * ($DC["P126"]/100)
				: $DC["D127"] * ($DC["P126"]/100)
		);
		//Mutual
		$DC["P127"] = 0.95;
		$DC["Q127"] = round(
			(($DC["T145"] == 3
				? 0
				: $DC["D127"] > $IM["O69"])
					? $IM["O69"] * ($DC["P127"]/100)
					: $DC["D127"] * ($DC["P127"]/100))
		);
		
		return $DC;
	}

	function CalcImp2da($R48, $func, $val) {
		$DC = array();
		
		//Calculo impuesto unico 2da categoria
		//Desde / Hasta
		$DC["D142"] = 0;
		$DC["F142"] = 13.5;
		$DC["D143"] = 13.5;
		$DC["F143"] = 30;
		$DC["D144"] = 30;
		$DC["F144"] = 50;
		$DC["D145"] = 50;
		$DC["F145"] = 70;
		$DC["D146"] = 70;
		$DC["F146"] = 90;
		$DC["D147"] = 90;
		$DC["F147"] = 120;
		$DC["D148"] = 120;
		$DC["F148"] = 150;
		$DC["D149"] = 150;
		//Factor
		$DC["G142"] = 0;
		$DC["G143"] = 0.04;
		$DC["G144"] = 0.08;
		$DC["G145"] = 0.135;
		$DC["G146"] = 0.230;
		$DC["G147"] = 0.304;
		$DC["G148"] = 0.355;
		$DC["G149"] = 0.4;
		//Desde / Hasta
		$DC["J142"] = $DC["D143"] * $R48;
		$DC["H143"] = $DC["D143"] * $R48 + 0.01;
		$DC["J143"] = $DC["D144"] * $R48;
		$DC["H144"] = $DC["D144"] * $R48 + 0.01;
		$DC["J144"] = $DC["D145"] * $R48;
		$DC["H145"] = $DC["D145"] * $R48 + 0.01;
		$DC["J145"] = $DC["D146"] * $R48;
		$DC["H146"] = $DC["D146"] * $R48 + 0.01;
		$DC["J146"] = $DC["D147"] * $R48;
		$DC["H147"] = $DC["D147"] * $R48 + 0.01;
		$DC["J147"] = $DC["D148"] * $R48;
		$DC["H148"] = $DC["D148"] * $R48 + 0.01;
		$DC["J148"] = $DC["D149"] * $R48;
		$DC["H149"] = $DC["D149"] * $R48 + 0.01;
		//Rebaja
		$DC["L142"] = 0;
		$DC["L143"] = 0.54;
		$DC["L144"] = 1.74;
		$DC["L145"] = 4.49;
		$DC["L146"] = 11.14;
		$DC["L147"] = 17.80;
		$DC["L148"] = 23.92;
		$DC["L149"] = 30.67;
		//$ Rebaja
		$DC["O142"] = $DC["L142"] * $R48;
		$DC["O143"] = $DC["L143"] * $R48;
		$DC["O144"] = $DC["L144"] * $R48;
		$DC["O145"] = $DC["L145"] * $R48;
		$DC["O146"] = $DC["L146"] * $R48;
		$DC["O147"] = $DC["L147"] * $R48;
		$DC["O148"] = $DC["L148"] * $R48;
		$DC["O149"] = $DC["L149"] * $R48;
		
		if ($func == "factor") {
			if ($val <= $DC["J142"]) {
				return $DC["G142"];
			} if ($val >= $DC["H143"] && $val <= $DC["J143"]) {
				return $DC["G143"];
			} if ($val >= $DC["H144"] && $val <= $DC["J144"]) {
				return $DC["G144"];
			} if ($val >= $DC["H145"] && $val <= $DC["J145"]) {
				return $DC["G145"];
			} if ($val >= $DC["H146"] && $val <= $DC["J146"]) {
				return $DC["G146"];
			} if ($val >= $DC["H147"] && $val <= $DC["J147"]) {
				return $DC["G147"];
			} if ($val >= $DC["H148"] && $val <= $DC["J148"]) {
				return $DC["G148"];
			} if ($val >= $DC["H149"]) {
				return $DC["G149"];
			}
		}
		
		if ($func == "rebaja") {
			if ($val <= $DC["J142"]) {
				return $DC["O142"];
			} elseif ($val >= $DC["H143"] && $val <= $DC["J143"]) {
				return $DC["O143"];
			} elseif ($val >= $DC["H144"] && $val <= $DC["J144"]) {
				return $DC["O144"];
			} elseif ($val >= $DC["H145"] && $val <= $DC["J145"]) {
				return $DC["O145"];
			} elseif ($val >= $DC["H146"] && $val <= $DC["J146"]) {
				return $DC["O146"];
			} elseif ($val >= $DC["H147"] && $val <= $DC["J147"]) {
				return $DC["O147"];
			} elseif ($val >= $DC["H148"] && $val <= $DC["J148"]) {
				return $DC["O148"];
			} elseif ($val >= $DC["H149"]) {
				return $DC["O149"];
			}
		}
		
		
	}

	function DataLabeller($DC) {
		//Fallas o atrasos
		$DC["D94"] .= $DC["D94"] >= 0 
			? $DC["D94"] == 1 
				? " Día" 
				: " Días"
			: "";		//Fallas sin motivo
		$DC["F94"] = "$".number_format($DC["F94"],0,",",".");
		$DC["D95"] .= $DC["D95"] >= 0 
			? $DC["D95"] == 1 
				? " Hora" 
				: " Horas"
			: "";	//Atrasos o retiros
		$DC["F95"] = "$".number_format($DC["F95"],0,",",".");
		$DC["D96"] .= $DC["D96"] >= 0
			? $DC["D96"] == 1 
				? " Día" 
				: " Días"
			: "";		//Fallas Justificadas
		$DC["F96"] = "$".number_format($DC["F96"],0,",",".");
		$DC["D97"] .= $DC["D97"] >= 0 
			? $DC["D97"] == 1 
				? " Día" 
				: " Días"
			: "";		//Licencias Médicas
		$DC["F97"] = "$".number_format($DC["F97"],0,",",".");
		//Gratificación y otros
		$DC["P94"] = "$".number_format($DC["P94"],0,",",".");
		$DC["N95"] = $DC["N95"] != "" ? round($DC["N95"], 3)." Horas" : "";	//Horas extra
		$DC["P95"] = "$".number_format($DC["P95"],0,",",".");
		$DC["P96"] = "$".number_format($DC["P96"],0,",",".");
		$DC["N96"] = "Acumulativo de ".$DC["N96"]. ($DC["N96"] == 1 ? " día" : " días") ;
		$DC["N98"] = "$".number_format($DC["N98"],0,",",".");
		$DC["P98"] = "$".number_format($DC["P98"],0,",",".");
		$DC["D110"] = "$".number_format($DC["D110"],0,",",".");
		
		
		//Descuentos Legales
		//AFP
		$DC["P103"] = $DC["P103"]."%";
		$DC["Q103"] = "$".number_format($DC["Q103"],0,",",".");
		//Isapre
		$DC["P104"] = $DC["P104"]."%";
		$DC["Q104"] = "$".number_format($DC["Q104"],0,",",".");
		//Seguro Cesantia
		$DC["P105"] = $DC["P105"]."%";
		$DC["Q105"] = "$".number_format($DC["Q105"],0,",",".");
		//Adicional salud
		$DC["P106"] = $DC["P106"]." UF";
		$DC["Q106"] = "$".number_format($DC["Q106"],0,",",".");
		//APV
		$DC["P107"] = $DC["P107"]." UF";
		$DC["Q107"] = "$".number_format($DC["Q107"],0,",",".");
		$DC["P108"] = $DC["P108"]."%";
		$DC["Q108"] = "$".number_format($DC["Q108"],0,",",".");
		$DC["P109"] = "$".number_format($DC["P109"],0,",",".");
		$DC["Q109"] = "$".number_format($DC["Q109"],0,",",".");
		$DC["P110"] = $DC["P110"]."%";
		$DC["Q110"] = "$".number_format($DC["Q110"],0,",",".");
		
		//Impuesto a la renta
		$DC["N113"] = "$".number_format($DC["N113"],0,",",".");
		$DC["Q113"] = $DC["Q113"] . " UF";
		$DC["P114"] = "$".number_format($DC["P114"],0,",",".");
		$DC["M115"] = "$".number_format($DC["M115"],0,",",".");
		$DC["Q115"] = "$".number_format($DC["Q115"],0,",",".");
		
		//Resumen
		$DC["D123"] = "$".number_format($DC["D123"],0,",",".");
		$DC["D124"] = "$".number_format($DC["D124"],0,",",".");
		$DC["D125"] = "$".number_format($DC["D125"],0,",",".");
		$DC["D126"] = "$".number_format($DC["D126"],0,",",".");
		$DC["D127"] = "$".number_format($DC["D127"],0,",",".");
		$DC["D128"] = "$".number_format($DC["D128"],0,",",".");
		$DC["D129"] = "$".number_format($DC["D129"],0,",",".");
		$DC["D130"] = "$".number_format($DC["D130"],0,",",".");
		$DC["D131"] = "$".number_format($DC["D131"],0,",",".");
		$DC["D132"] = "$".number_format($DC["D132"],0,",",".");
		$DC["D133"] = "$".number_format($DC["D133"],0,",",".");
		return $DC;
	}
}
