<?php


class cont_plan_individual_model{

    var $c_table = 'cont_cuentas_generales cg';
    var $c_primaryKey = 'cg.codigo_cuenta';
    var $pi_table = 'cont_plan_individual pi';
    var $table = 'cont_plan_individual';
    var $rubros_pcga = 'cont_rubros_pcga';
    var $rubros_fip = 'cont_rubros_fip';
    var $rubros_ifrs = 'cont_rubros_ifrs';
    var $clasificacion_pcga = 'cont_clasificacion_pcga';
    var $clasificacion_fip = 'cont_clasificacion_fip';
    var $clasificacion_ifrs = 'cont_clasificacion_ifrs';

    function __construct($resource = null) {
        global $client;
        $this->resource = $resource;
        $this->c_table = 'dbo.'.$this->c_table;
        if ($client) $this->pi_table = $client->db_name.'.'.$this->pi_table;
        if ($client) $this->table = $client->db_name.'.'.$this->table;
        if ($client) $this->rubros_pcga = $client->db_name.'.'.$this->rubros_pcga;
        if ($client) $this->rubros_fip = $client->db_name.'.'.$this->rubros_fip;
        if ($client) $this->rubros_ifrs = $client->db_name.'.'.$this->rubros_ifrs;
        if ($client) $this->clasificacion_pcga = $client->db_name.'.'.$this->clasificacion_pcga;
        if ($client) $this->clasificacion_fip = $client->db_name.'.'.$this->clasificacion_fip;
        if ($client) $this->clasificacion_ifrs = $client->db_name.'.'.$this->clasificacion_ifrs;
        $this->utils = new utils();
        
    }
    
    function listcuentas(){
        $dtNum = 0;
        global $config;
        $this->check = '<div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="check"  checked>
                <label class="custom-control-label" for="check"></label>
            </div>';
        $this->uncheck = '<div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="check">
                <label class="custom-control-label" for="check"></label>
            </div>';
        $this->actionbuttons = '<div><button id="paso2" type="button" class="btn btn-success btn-sm" > Paso 2 <span class="fas fa-angle-double-right"></span></button></div>';

        $columns = [
            [
                //DB
				'dt' => $dtNum++,
                'db' => $this->c_primaryKey,
                'alias' => 'codigo_cuenta',
				//DT
				'title' => 'Cuenta',
                'searchable' => true,
                "orderable" => true
            ],
            [
                //DB
				'dt' => $dtNum++,
				'db' => 'descripcion',
				//DT
				'title' => 'Descripción',
                'searchable' => true,
                "orderable" => true
            ],
            [
                //DB
                'dt' => $dtNum++,
                'db' => "case when pi.codigo_cuenta <> '' then '{$this->check}' else '{$this->uncheck}' end",
				'alias' => 'codigo',
				//DT
                'title' => 'Seleccionar',
                "className" => 'text-center',
                'searchable' => false,
                "orderable" => false
            ],
            [
                //DB
                'dt' => $dtNum++,
                'db' => "case when pi.codigo_cuenta <> '' then '{$this->actionbuttons}' else '' end", 
				'alias' => 'actions',
                
				//DT
                'title' => 'Acción',
                'searchable' => false,
                "orderable" => false
            ],
            [
                //DB   
                'dt' => $dtNum++,   
                'db' => "cg.parent",   
                'alias' => 'parent', 
                //DT   
                'title' => 'parent',
                'visible' => false,
				'searchable' => false
            ]
        ];

        $filtro = ["LEFT JOIN {$this->pi_table} ON cg.codigo_cuenta=pi.codigo_cuenta","cg.parent = '0' AND (NOT eliminado = '1' OR eliminado IS NULL)"];

        if (!isset($_POST["config"])) {
			if (!isset($this->resource['permisos_user_obj']) || !in_array('read', $this->resource['permisos_user_obj'])) {
				return [
					"draw" => intval( $_POST['draw'] ),
					"recordsTotal" => 0,
					"recordsFiltered" => 0,
					"swal" => [
						"type" => "error",
						"text" => "No tiene permisos para ver en este módulo."
					],
					"data" => []
				];
			}
		}
        
        return SSP::simple( $_POST, $config->database, $this->c_table, $this->c_primaryKey, $columns, $filtro);

    }

    function listsubcuentas($parent){
        $dtNum = 0;
        global $config;
        $this->check = '<div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="check2"  checked>
                <label class="custom-control-label" for="check2"></label>
            </div>';
        $this->uncheck = '<div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="check2">
                <label class="custom-control-label" for="check2"></label>
            </div>';
        $this->actionbuttons = '<div><button id="paso3" type="button" class="btn btn-success btn-sm" > Paso 3 <span class="fas fa-angle-double-right"></span></button></div>';

        $columns = [
            [
                //DB
				'dt' => $dtNum++,
                'db' => $this->c_primaryKey,
                'alias' => 'codigo_cuenta',
				//DT
				'title' => 'Cuenta',
                'searchable' => true,
                "orderable" => true
            ],
            [
                //DB
				'dt' => $dtNum++,
				'db' => 'descripcion',
				//DT
				'title' => 'Descripción',
                'searchable' => true,
                "orderable" => true
            ],
            [
                //DB
                'dt' => $dtNum++,
                'db' => "case when pi.codigo_cuenta <> '' then '{$this->check}' else '{$this->uncheck}' end",
				'alias' => 'codigo',
				//DT
                'title' => 'Seleccionar',
                "className" => 'text-center',
                'searchable' => false,
                "orderable" => false
            ],
            [
                //DB
                'dt' => $dtNum++,
                'db' => "case when pi.codigo_cuenta <> '' then '{$this->actionbuttons}' else '' end", 
				'alias' => 'actions',
                
				//DT
                'title' => 'Acción',
                'searchable' => false,
                "orderable" => false
            ],
            [
                //DB   
                'dt' => $dtNum++,   
                'db' => "cg.parent",   
                'alias' => 'parent', 
                //DT   
                'title' => 'parent',
                'visible' => false,
				'searchable' => false
            ]
        ];

        $filtro = ["LEFT JOIN {$this->pi_table} ON cg.codigo_cuenta=pi.codigo_cuenta","cg.parent = '{$parent}' AND (NOT eliminado = '1' OR eliminado IS NULL)"];

		return SSP::simple( $_POST, $config->database, $this->c_table, $this->c_primaryKey, $columns, $filtro);
    }


    function set($data){
        global $_DB;
        $status = 0;
        if($data["fn"] == "account-parent"){
            $pi["codigo_cuenta"] = $data["account"];
            $pi["parent"] = $data["parent"];
            $pi["pcga"] = 0;
            $pi["fk_pcga_rubro_id"] = '';
            $pi["fk_pcga_clasificacion_id"] = '';
            $pi["fip"] = 0;
            $pi["fk_fip_rubro_id"] = '';
            $pi["fk_fip_clasificacion_id"] = '';
            $pi["ifrs"] = 0;
            $pi["fk_ifrs_rubro_id"] = '';
            $pi["fk_ifrs_clasificacion_id"] = '';
            if($data["status"] == 1){
                $_DB->query("INSERT INTO {$this->table} ".$this->utils->arrayToQuery('insert',$pi));
                $status = 1;
            } else if($data["status"] == 0){
                $_DB->query("DELETE FROM {$this->table} WHERE codigo_cuenta = '{$pi["codigo_cuenta"]}' AND parent = '{$pi["parent"]}' ");
            }
        }

        return $status;
    }

    function set_plan($data){
        global $_DB;

        if (!in_array('update', $this->resource['permisos_user_obj'])) {
            return [
                'type' => 'error',
                'text' => 'No tiene permisos para actualizar en este módulo.'
            ];
        }    

        if($data["fn"] == "update-plan"){
            $d = $data["datos"];
            $caracteristicas = [
                "analitica" => 0,  "historica" => 0,  "vencimiento" => 0,  "centro_costo" => 0,  "categoria" => 0, 
                "valor" => 0,  "inversiones" => 0,  "afijo" => 0
            ];

            if(isset($d["caract"])){
                foreach($d["caract"] as $char) {
                    if(array_key_exists($char, $caracteristicas)){
                        $caracteristicas[$char] = 1;
                    } else {
                        $caracteristicas[$char] = 0;
                    }
                }
            }

            $update["pcga"] = $d["pcga"]["activo"]; 
            $update["fk_pcga_rubro_id"] = ($d["pcga"]["activo"] == 1) ? $d["pcga"]["rubro"] : '0'; 
            $update["fk_pcga_clasificacion_id"] = ($d["pcga"]["activo"] == 1) ? $d["pcga"]["clasificacion"] : '0'; 
            $update["fip"] = $d["fip"]["activo"]; 
            $update["fk_fip_rubro_id"] = ($d["fip"]["activo"] == 1) ? $d["fip"]["rubro"] : '0'; 
            $update["fk_fip_clasificacion_id"] = ($d["fip"]["activo"] == 1) ? $d["fip"]["clasificacion"] : '0';  
            $update["ifrs"] = $d["ifrs"]["activo"]; 
            $update["fk_ifrs_rubro_id"] = ($d["ifrs"]["activo"] == 1) ? $d["ifrs"]["rubro"] : '0';  
            $update["fk_ifrs_clasificacion_id"] = ($d["ifrs"]["activo"] == 1) ? $d["ifrs"]["clasificacion"] : '0'; 
            $update["analitica"] = $caracteristicas["analitica"];
            $update["historica"] = $caracteristicas["historica"];
            $update["vencimiento"] = $caracteristicas["vencimiento"];
            $update["centro_costo"] = $caracteristicas["centro_costo"];
            $update["categoria"] = $caracteristicas["categoria"];
            $update["centro_negocio"] = $caracteristicas["valor"];
            $update["inversiones"] = $caracteristicas["inversiones"];
            $update["afijo"] = $caracteristicas["afijo"];
            $update["cuenta_partida_doble"] = $d["dp"]["doble"]; 
            $update["cuenta_neteo"] = $d["dp"]["neteo"]; 

            $_DB->query("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $update)." WHERE codigo_cuenta = '".$d["codigo"]."' AND parent = '".$d["parent"]."'");

            return [ 'type' =>'success', 'title' => 'Plan Actualizado!', 'text' => 'El plan individual se ha actualizado'];
        }

    }

    function listselect(){
        global $_DB;
        global $client;
        $select["rubros_pcga"] = $_DB->queryToArray("SELECT id as value, nombre as name FROM {$this->rubros_pcga}  WHERE eliminado = 0");
        $select["rubros_fip"] = $_DB->queryToArray("SELECT id as value, nombre as name FROM {$this->rubros_fip} WHERE eliminado = 0");
        $select["rubros_ifrs"] = $_DB->queryToArray("SELECT id as value, nombre as name FROM {$this->rubros_ifrs} WHERE eliminado = 0");
        $select["clasificacion_pcga"] = $_DB->queryToArray("SELECT id as value, nombre as name FROM {$this->clasificacion_pcga} WHERE eliminado = 0");
        $select["clasificacion_fip"] = $_DB->queryToArray("SELECT id as value, nombre as name FROM {$this->clasificacion_fip} WHERE eliminado = 0");
        $select["clasificacion_ifrs"] = $_DB->queryToArray("SELECT id as value, nombre as name FROM {$this->clasificacion_ifrs} WHERE eliminado = 0");
        
        return $select;
    }

    function listcaracteristicas($data){
        global $_DB;

        $sql = "SELECT CASE WHEN pcga = 1 THEN 'checked' ELSE '' END AS pcga,
                fk_pcga_rubro_id as pcga_rubro_id,
                fk_pcga_clasificacion_id as pcga_clasificacion_id,
                CASE WHEN fip = 1 THEN 'checked' ELSE '' END AS fip,
                fk_fip_rubro_id as fip_rubro_id,
                fk_fip_clasificacion_id as fip_clasificacion_id,
                CASE WHEN ifrs = 1 THEN 'checked' ELSE '' END AS ifrs,
                fk_ifrs_rubro_id as ifrs_rubro_id,
                fk_ifrs_clasificacion_id as ifrs_clasificacion_id,
                cuenta_partida_doble,
                cuenta_neteo,
                CASE WHEN analitica = 1 THEN 'selected' ELSE '' END AS analitica_ck,
                CASE WHEN historica = 1 THEN 'selected' ELSE '' END AS historica_ck,
                CASE WHEN vencimiento = 1 THEN 'selected' ELSE '' END AS vencimiento_ck,
                CASE WHEN centro_costo = 1 THEN 'selected' ELSE '' END AS centro_costo_ck,
                CASE WHEN categoria = 1 THEN 'selected' ELSE '' END AS categoria_ck,
                CASE WHEN centro_negocio = '1' THEN 'selected' ELSE '' END AS valor_ck,
                CASE WHEN inversiones = '1' THEN 'selected' ELSE '' END AS inversiones_ck,
                CASE WHEN afijo = '1' THEN 'selected' ELSE '' END AS afijo_ck
            FROM {$this->table}
            WHERE codigo_cuenta = '{$data["codigo"]}'
            AND parent = '{$data["parent"]}' ";

        $caract = $_DB->queryToArray($sql)[0];
        
        return $caract;
    }

    function listpartida(){
        global $_DB;

        $sql = "SELECT pi.codigo_cuenta,
                cg.descripcion
            FROM {$this->pi_table}  
                LEFT JOIN {$this->c_table}
                ON cg.codigo_cuenta = pi.codigo_cuenta
            WHERE pi.parent <> '0' AND cg.descripcion <> ''
                ORDER BY cg.descripcion";

        return $_DB->query_to_array($sql);
        
    }

    function export_plan(){
        global $_DB;

        $sql = " SELECT pi.parent, case when pi.parent = '0' then pi.codigo_cuenta else '' end as codigo_parent, pi.codigo_cuenta,
                cg.descripcion, case when pi.pcga = 1 then 'X' else '' end AS pcga ,case when pi.fip = 1 then 'X' else '' end AS fip,
                case when pi.ifrs = 1 then 'X' else '' end AS ifrs, case when pi.fk_pcga_rubro_id is null then 0 else pi.fk_pcga_rubro_id end fk_pcga_rubro_id ,
                rp.nombre AS pcga_rubro,case when pi.fk_pcga_clasificacion_id is null then 0 else pi.fk_pcga_clasificacion_id end fk_pcga_clasificacion_id, 
                cp.nombre AS pcga_clasificacion,case when pi.fk_fip_rubro_id is null then 0 else pi.fk_fip_rubro_id end fk_fip_rubro_id, 
                rf.nombre AS fip_rubro, case when pi.fk_fip_clasificacion_id is null then 0 else pi.fk_fip_clasificacion_id end fk_fip_clasificacion_id, 
                cf.nombre AS fip_clasificacion, case when pi.fk_ifrs_rubro_id is null then 0 else pi.fk_ifrs_rubro_id end fk_ifrs_rubro_id,
                ri.nombre AS ifrs_rubro, case when pi.fk_ifrs_clasificacion_id is null then 0 else pi.fk_ifrs_clasificacion_id end fk_ifrs_clasificacion_id, 
                ci.nombre AS ifrs_clasificacion, case when pi.analitica = 1 then 'X' else '' end AS analiticas,
                case when pi.historica = 1 then 'X' else '' end AS historicas, case when pi.categoria = 1 then 'X' else '' end AS categorias,
                case when pi.vencimiento = 1 then 'X' else '' end AS vencimientos, 
                case when pi.centro_negocio = '1' then 'X' else '' end AS centro_negocio,
                case when pi.centro_costo = 1 then 'X' else '' end AS centro_costos,
                case when pi.inversiones = '1' then 'X' else '' end AS inversiones
            FROM {$this->pi_table} 
            LEFT JOIN {$this->c_table} ON pi.codigo_cuenta=cg.codigo_cuenta
            LEFT JOIN {$this->rubros_pcga} AS rp ON rp.id = pi.fk_pcga_rubro_id
            LEFT JOIN {$this->clasificacion_pcga} AS cp ON cp.id = pi.fk_pcga_clasificacion_id
            LEFT JOIN {$this->rubros_fip} AS rf ON rf.id = pi.fk_fip_rubro_id
            LEFT JOIN {$this->clasificacion_fip} AS cf ON cf.id = pi.fk_fip_clasificacion_id
            LEFT JOIN {$this->rubros_ifrs} AS ri ON ri.id = pi.fk_ifrs_rubro_id
            LEFT JOIN {$this->clasificacion_ifrs} AS ci ON ci.id = pi.fk_ifrs_clasificacion_id";
        
        $excel["name"] = "Plan Individual";
        $excel["includetimestamp"] = false;
        $excel["sheetname"] = "Plan Individual";
        $excel["columns"] = [
            ["title" => "PARENT", "data" => "parent", "width" => 15], 
            ["title" => "", "data" => "codigo_parent", "width" => 10], 
            ["title" => "CUENTA", "data" => "codigo_cuenta", "width" => 15],
            ["title" => "DETALLE", "data" => "descripcion", "width" => 15], 
            ["title" => "PCGA", "data" => "pcga","width" => 15], 
            ["title" => "FIP", "data" => "fip", "width" => 15], 
            ["title" => "IFRS", "data" => "ifrs", "width" => 15],
            ["title" => "", "data" => "fk_pcga_rubro_id", "width" => 10],
            ["title" => "RUBRO PCGA", "data" => "pcga_rubro", "width" => 15],
            ["title" => "", "data" => "fk_pcga_clasificacion_id", "width" => 10], 
            ["title" => "CLASIFICACION PCGA", "data" => "pcga_clasificacion", "width" => 20],
            ["title" => "", "data" => "fk_fip_rubro_id", "width" => 10], 
            ["title" => "RUBRO FIP", "data" => "fip_rubro", "width" => 15], 
            ["title" => "", "data" => "fk_fip_clasificacion_id", "width" => 10], 
            ["title" => "CLASIFICACION FIP", "data" => "fip_clasificacion", "width" => 20], 
            ["title" => "", "data" => "fk_ifrs_rubro_id", "width" => 10], 
            ["title" => "RUBRO IFRS", "data" => "ifrs_rubro", "width" => 15], 
            ["title" => "", "data" => "fk_ifrs_clasificacion_id", "width" => 10], 
            ["title" => "CLASIFICACION IFRS", "data" => "ifrs_clasificacion", "width" => 20], 
            ["title" => "ANALITICA", "data" => "analiticas", "width" => 15], 
            ["title" => "HISTORICA", "data" => "historicas", "width" => 15], 
            ["title" => "VENCIMIENTO", "data" => "vencimientos", "width" => 15], 
            ["title" => "CENTRO COSTO", "data" => "centro_costos", "width" => 15], 
            ["title" => "CATEGORIA", "data" => "categorias", "width" => 15], 
            ["title" => "CENTRO NEGOCIO", "data" => "centro_negocio", "width" => 20],
            ["title" => "INVERSIONES", "data" => "inversiones", "width" => 15]
        ];
        $excel["data"] = $_DB->query_to_array($sql);
        return $excel;
    }


    function export_masivo(){
        global $_DB;

        $sql = " SELECT cg.parent, case when cg.parent = '0' then cg.codigo_cuenta else '' end as codigo_parent, cg.codigo_cuenta,
                cg.descripcion, 
                case when pi.fk_pcga_rubro_id is null then 0 else pi.fk_pcga_rubro_id end fk_pcga_rubro_id ,
                case when pi.fk_pcga_clasificacion_id is null then 0 else pi.fk_pcga_clasificacion_id end fk_pcga_clasificacion_id, 
                case when pi.fk_fip_rubro_id is null then 0 else pi.fk_fip_rubro_id end fk_fip_rubro_id, 
                case when pi.fk_fip_clasificacion_id is null then 0 else pi.fk_fip_clasificacion_id end fk_fip_clasificacion_id, 
                case when pi.fk_ifrs_rubro_id is null then 0 else pi.fk_ifrs_rubro_id end fk_ifrs_rubro_id,
                case when pi.fk_ifrs_clasificacion_id is null then 0 else pi.fk_ifrs_clasificacion_id end fk_ifrs_clasificacion_id, 
                case when pi.analitica = 1 then 'X' else '' end AS analiticas,
                case when pi.historica = 1 then 'X' else '' end AS historicas, 
                case when pi.categoria = 1 then 'X' else '' end AS categorias,
                case when pi.vencimiento = 1 then 'X' else '' end AS vencimientos, 
                case when pi.centro_negocio = '1' then 'X' else '' end AS centro_negocio,
                case when pi.centro_costo = 1 then 'X' else '' end AS centro_costos,
                case when pi.inversiones = '1' then 'X' else '' end AS inversiones
            FROM {$this->pi_table}
            LEFT JOIN {$this->c_table} ON pi.codigo_cuenta=cg.codigo_cuenta
            ORDER BY cg.codigo_cuenta";

        $combos = $this->listselect();

        $excel["name"] = "Carga Masiva Plan Individual";
        $excel["includetimestamp"] = false;
        $excel["sheetname"] = "Plan General";
        $excel["columns"] = [
            ["title" => "CUENTA PADRE", "data" => "parent", "width" => 15], 
            ["title" => "", "data" => "codigo_parent", "width" => 10], 
            ["title" => "CUENTA", "data" => "codigo_cuenta", "width" => 15],
            ["title" => "DETALLE", "data" => "descripcion", "width" => 30],
            ["title" => "RUBRO PCGA", "data" => "fk_pcga_rubro_id","width" => 15, 
                "combo" => $combos["rubros_pcga"]],
            ["title" => "CLASIFICACION PCGA", "data" => "fk_pcga_clasificacion_id","width" => 25, 
                "combo" => $combos["clasificacion_pcga"]],
            ["title" => "RUBRO FIP", "data" => "fk_fip_rubro_id","width" => 15, 
                "combo" => $combos["rubros_fip"]],
            ["title" => "CLASIFICACION FIP", "data" => "fk_fip_clasificacion_id","width" => 25, 
                "combo" => $combos["clasificacion_fip"]],
            ["title" => "RUBRO IFRS", "data" => "fk_ifrs_rubro_id","width" => 15, 
                "combo" => $combos["rubros_ifrs"]],
            ["title" => "CLASIFICACION IFRS", "data" => "fk_ifrs_clasificacion_id","width" => 25, 
                "combo" => $combos["clasificacion_ifrs"]],
            ["title" => "ANALITICA", "data" => "analiticas", "width" => 15, "required" => ["X",""]], 
            ["title" => "HISTORICA", "data" => "historicas", "width" => 15, "required" => ["X",""]], 
            ["title" => "VENCIMIENTO", "data" => "vencimientos", "width" => 15, "required" => ["X",""]], 
            ["title" => "CENTRO COSTO", "data" => "centro_costos", "width" => 15, "required" => ["X",""]], 
            ["title" => "CATEGORIA", "data" => "categorias", "width" => 15, "required" => ["X",""]], 
            ["title" => "CENTRO NEGOCIO", "data" => "centro_negocio", "width" => 20, "required" => ["X",""]],
            ["title" => "INVERSIONES", "data" => "inversiones", "width" => 15, "required" => ["X",""]]
        ];
        $excel["data"] = $_DB->query_to_array($sql);
        return $excel;
    }


    

}




?>