<?php
class cont_balance_general_model{

    function __construct(){

    }

    function loadBalanceType(){
        global $_DB;

        $c_type_client = $_DB->query_to_array(
            "SELECT tipo_contabilidad 
            FROM clientes 
            WHERE id = {$_SESSION["ss_cliente"]} AND eliminado = 0"
        );

        $c_type_client = explode(",", $c_type_client[0]['tipo_contabilidad']);
        $c_type_client = array_map(function ($row){
            return "'{$row}'";
        }, $c_type_client);

        $c_type_client = implode(",", $c_type_client);

        $data = $_DB->query_to_array(
            "SELECT 
                vf.valor,
                vf.descripcion
            FROM valores_flexibles AS vf
            WHERE vf.tipo = 'TIPO_CONTABILIDAD'
        AND vf.valor IN ({$c_type_client})");

        return $data;       
    }

    function loadTable($type, $from, $to){
        //FALTAN LOS TOTALES DE ACTIVOS PASIVOS GASTOS E INGRESOS, HACERLO EN EL MODELO COMO DATAO ESTATICO
        global $_DB;
        if($type == 1){
            $filter = "cp.nombre AS clasificacion,
                       rp.nombre AS rubro,";
            $where = "AND pin.pcga = 1";
        }
        if($type == 2){
            $filter = "cf.nombre AS clasificacion,
                       rf.nombre AS rubro,";              
            $where = "AND pin.fip = 1";
        }
        if($type == 3){
            $filter = "ci.nombre AS clasificacion,
                       ri.nombre AS rubro,";       
            $where = "AND pin.ifrs = 1";
        }

        $res = $_DB->query_to_array(
            "SELECT
                substring(pin.parent from 1 for 1) AS ind,
                {$filter}
                pin.parent AS padre,
                cg2.descripcion AS padre_nombre,
                pin.codigo_cuenta AS hijo,
                cg.descripcion AS hijo_nombre,
                coi.ct,
                coi.valor,
                CASE WHEN coi.ct='2' THEN coi.valor else 0 end as debe,
				CASE WHEN coi.ct='4' THEN coi.valor else 0 end as haber,
                CASE WHEN coi.ct='2' THEN coi.valor when coi.ct='4' then -coi.valor end as saldo
            FROM comprobantes AS com
                LEFT JOIN comprobantes_items AS coi
                ON coi.comprobante_id = com.id

                LEFT JOIN cuentas_generales AS cg
                ON cg.codigo_cuenta = coi.cuenta

                LEFT JOIN centros_costos AS cc
                ON cc.id::text = coi.cc

                LEFT JOIN valores_flexibles AS vf
                ON vf.valor = com.tipo_movimiento_id
                AND vf.tipo = 'TIPO_MOVIMIENTO'

                LEFT JOIN valores_flexibles AS vf2
                ON vf2.valor = com.tipo_movimiento_id
                AND vf2.tipo = 'CATEGORIAS'

                LEFT JOIN clientes AS cl
                ON cl.id = com.cliente_id

                LEFT JOIN plan_individual pin
                ON pin.codigo_cuenta = coi.cuenta
                AND pin.cliente_id = {$_SESSION["ss_cliente"]}

                LEFT JOIN cuentas_generales AS cg2
                ON cg2.codigo_cuenta = pin.parent

                LEFT JOIN rubros_fip AS rf
                ON rf.codigo = pin.fip_rubro_id

                LEFT JOIN clasificacion_fip AS cf
                ON cf.codigo = pin.fip_clasificacion_id

                LEFT JOIN rubros_ifrs AS ri
                ON ri.codigo = pin.ifrs_rubro_id

                LEFT JOIN clasificacion_ifrs AS ci
                ON ci.codigo = pin.ifrs_clasificacion_id

                LEFT JOIN rubros_pcga AS rp
                ON rp.codigo = pin.pcga_rubro_id

                LEFT JOIN clasificacion_pcga AS cp
                ON cp.codigo = pin.pcga_clasificacion_id

            WHERE com.fecha_ingreso BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:00'
                AND com.cliente_id = {$_SESSION["ss_cliente"]}
                AND com.estado = '2'
                {$where}
                AND com.eliminado = 0
            ORDER BY pin.parent, pin.codigo_cuenta");

            $data = [];
            $debito = 0;
            $credito = 0;
            $activo = 0;
            $pasivo = 0;
            $ganancia = 0;
            $perdida = 0;
            $data_ind_id = array_values(array_unique(array_column($res, "ind")));
            foreach ($data_ind_id as $id_ind) {
                $data_ind = array_filter($res, function ($row) use ($id_ind){
                    return $row["ind"] == $id_ind;
                });             
                $data_clas_id = array_unique(array_column($data_ind, 'clasificacion'));
                foreach ($data_clas_id as $id_clas) {
                    $data_clas = array_filter($res, function ($row) use ($id_clas){
                        return $row["clasificacion"] == $id_clas;
                    });
                    $data_rubro_id = array_unique(array_column($data_clas, 'rubro'));
                    foreach ($data_rubro_id as $id_rubro) {
                        $data_padre = array_filter($res, function ($row) use ($id_rubro){
                            return $row["rubro"] == $id_rubro;
                        });
                        $data_padre_id = array_unique(array_map(function ($row){
                            return "{$row['padre']} - {$row['padre_nombre']}";
                        }, $data_padre));
                        foreach ($data_padre_id as $id_padre) {
                            $data_hijo = array_filter($res, function ($row) use ($id_padre){
                                return "{$row['padre']} - {$row['padre_nombre']}" == $id_padre;
                            });
                            $data_hijo_arr = [];
                            foreach($data_hijo as $row) {
                                $data_hijo_arr[$row['hijo']] = "{$row['hijo']} - {$row['hijo_nombre']}";
                            }
                            $data_hijo_id = array_unique(array_map(function ($row){
                                return $row['hijo'];
                            }, $data_hijo));
                            foreach($data_hijo_id as $id_hijo){
                                $data_total = array_filter($res, function ($row) use ($id_hijo){
                                    return $row['hijo'] == $id_hijo;
                                });
                                $temp = [
                                    'cuenta' => $data_hijo_arr[$id_hijo],
                                    'debito' => array_sum(array_column($data_total, 'debe')),
                                    'credito' => array_sum(array_column($data_total, 'haber')),
                                    'activo' => 0,
                                    'pasivo' => 0,
                                    'perdida' => 0,
                                    'ganancia' => 0
                                ];
                                if(intval(substr($data_hijo_arr[$id_hijo], 0, 1)) < 4) {
                                    if($temp["debito"] > $temp["credito"]) {
                                        $temp["activo"] = $temp["debito"] - $temp["credito"];
                                    } else {
                                        $temp["pasivo"] = $temp["debito"] - $temp["credito"];
                                    }
                                }
                                if(intval(substr($data_hijo_arr[$id_hijo], 0, 1)) > 3) {
                                    if($temp["debito"] > $temp["credito"]) {
                                        $temp["perdida"] = $temp["debito"] - $temp["credito"];
                                    } else {
                                        $temp["ganancia"] = $temp["debito"] - $temp["credito"];
                                    }
                                }                            
                                $data['data'][$id_ind]['data'][$id_clas]['data'][$id_rubro]['data'][$id_padre]['data'][$id_hijo] = $temp;
                            }
                            $temp = $data['data'][$id_ind]['data'][$id_clas]['data'][$id_rubro]['data'][$id_padre];
                            $temp = array_replace_recursive($temp, [
                                'debito' => array_sum(array_column($temp['data'], "debito")),
                                'credito' => array_sum(array_column($temp['data'], "credito")),
                                'activo' => array_sum(array_column($temp['data'], "activo")),
                                'pasivo' => array_sum(array_column($temp['data'], "pasivo")),
                                'perdida' => array_sum(array_column($temp['data'], "perdida")),
                                'ganancia' => array_sum(array_column($temp['data'], "ganancia"))
                            ]);
                            
                            $data['data'][$id_ind]['data'][$id_clas]['data'][$id_rubro]['data'][$id_padre] = $temp;
                        }
                        $temp = $data['data'][$id_ind]['data'][$id_clas]['data'][$id_rubro];
                        $temp = array_replace_recursive($temp, [
                            'debito' => array_sum(array_column($temp['data'], "debito")),
                            'credito' => array_sum(array_column($temp['data'], "credito")),
                            'activo' => array_sum(array_column($temp['data'], "activo")),
                            'pasivo' => array_sum(array_column($temp['data'], "pasivo")),
                            'perdida' => array_sum(array_column($temp['data'], "perdida")),
                            'ganancia' => array_sum(array_column($temp['data'], "ganancia"))
                        ]);
                        $data['data'][$id_ind]['data'][$id_clas]['data'][$id_rubro] = $temp;
                    }
                    $temp = $data['data'][$id_ind]['data'][$id_clas];
                        $temp = array_replace_recursive($temp, [
                            'debito' => array_sum(array_column($temp['data'], "debito")),
                            'credito' => array_sum(array_column($temp['data'], "credito")),
                            'activo' => array_sum(array_column($temp['data'], "activo")),
                            'pasivo' => array_sum(array_column($temp['data'], "pasivo")),
                            'perdida' => array_sum(array_column($temp['data'], "perdida")),
                            'ganancia' => array_sum(array_column($temp['data'], "ganancia"))
                        ]);
                        
                        $data['data'][$id_ind]['data'][$id_clas] = $temp;
                }
                $temp = $data['data'][$id_ind];
                
                $temp = array_replace_recursive($temp, [
                    'debito' => array_sum(array_column($temp['data'], "debito")),
                    'credito' => array_sum(array_column($temp['data'], "credito")),
                    'activo' => array_sum(array_column($temp['data'], "activo")),
                    'pasivo' => array_sum(array_column($temp['data'], "pasivo")),
                    'perdida' => array_sum(array_column($temp['data'], "perdida")),
                    'ganancia' => array_sum(array_column($temp['data'], "ganancia"))
                ]);
                $data['data'][$id_ind] = $temp;
                $debito += array_sum(array_column($temp['data'], "debito"));
                $credito += array_sum(array_column($temp['data'], "credito"));
                $activo += array_sum(array_column($temp['data'], "activo"));
                $pasivo += array_sum(array_column($temp['data'], "pasivo"));
                $ganancia += array_sum(array_column($temp['data'], "ganancia"));
                $perdida += array_sum(array_column($temp['data'], "perdida"));
                $data[]["debito"] = $debito;
                $data[]["credito"] = $credito;
                $data[]["activo"] = $activo;
                $data[]["pasivo"] = $pasivo;
                $data[]["ganancia"] = $ganancia;
                $data[]["perdida"] = $perdida;
            }           
            $data["debito"] = $debito;  
            $data["credito"] = $credito;  
            $data["activo"] = $activo;  
            $data["pasivo"] = $pasivo;  
            $data["ganancia"] = $ganancia;  
            $data["perdida"] = $perdida;  
        return $data;
    }

    function loadTableByAccount($from, $to, $account){
        global $_DB;
        if(isset($account)){
            $res = $_DB->query_to_array(
                "SELECT
                    ci.cuenta,
                    ci.cat,
                    ci.cc, 
                    ci.dv,
                    ci.docto,
                    ci.glosa AS glosa,
                    ci.valor,
                    ci.ct,
                    to_char(ci.fecha, 'DD-MM-YYYY') AS fechas,
                    to_char(ci.fecha, 'MM-YYYY') AS mes_format,
                    ci.id,
                    ci.cn,
                    cg.descripcion,	
                    cm.folio,
                    to_char(cm.fecha_ingreso, 'DD-MM-YYYY') AS fecha_ingreso_format,
                    to_char(cm.fecha_ingreso, 'MM') AS mes,
                    vf.descripcion AS tipo_movimiento,
                    ci.dv,
                    cm.id AS id_comprobante,
                    CASE WHEN ci.ct='2' THEN ci.valor else 0 end as debe,
                    CASE WHEN ci.ct='4' THEN ci.valor else 0 end as haber,
                    CASE WHEN ci.ct='2' THEN ci.valor when ci.ct='4' then -ci.valor end as saldo
                FROM comprobantes_items AS ci
                    LEFT JOIN cuentas_generales AS cg 
                    ON cg.codigo_cuenta = ci.cuenta

                    LEFT JOIN comprobantes AS cm 
                    ON cm.id = ci.comprobante_id

                    LEFT JOIN valores_flexibles AS vf
                    ON cm.tipo_movimiento_id = vf.valor
                    AND vf.tipo= 'TIPO_MOVIMIENTO'
                WHERE cm.fecha_ingreso BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:00'
                AND cm.cliente_id = {$_SESSION["ss_cliente"]}
                AND cm.eliminado = 0
                AND cm.estado = '2'
                AND (ci.cuenta = '{$account}') 
                ORDER BY ci.cuenta, cm.fecha_ingreso, ci.cc"
            );
          
            if(isset($res[0]["cuenta"])){
                $data = [
                    'cuenta' => $res[0]["cuenta"],
                    'nombre_cuenta' => $res[0]["descripcion"],
                    'debe' => 0,
                    'haber' => 0,
                    'saldo' => 0
                ];

                $debe = 0;
                $haber = 0;
                $saldo = 0;

                $data_mes = array_unique(array_column($res, 'mes'));
                foreach ($data_mes as $mes) {
                    $data_fecha = array_filter($res, function ($row) use ($mes) {
                        return $row['mes'] == $mes;
                    });
                    $mes_temp = array_map(function ($row_sec) {
                        return $row_sec;
                    }, $data_fecha);
                    $data['data'][$mes] = [
                       'data' => $mes_temp,
                       'debe' => array_sum(array_column($mes_temp, "debe")),
                       'haber' => array_sum(array_column($mes_temp, "haber")),
                       'saldo' => array_sum(array_column($mes_temp, "saldo"))
                    ];
                    $debe += $data['data'][$mes]['debe'];
                    $haber += $data['data'][$mes]['haber'];
                    $saldo += $data['data'][$mes]['saldo'];
                    
                }
                $data['debe'] = $debe;
                $data['haber'] = $haber;
                $data['saldo'] = $saldo;
                return $data;
            }          
        }
    }

    function loadTableByComprobant($id_comp){
        global $_DB;
        if(isset($id_comp)){
            $comp = $_DB->query_to_array(
            "SELECT
                cp.folio,
                to_char(cp.fecha_ingreso, 'DD-MM-YYYY') AS fecha_ingreso,
                cp.tipo_movimiento_id,
                cp.estado,
                vf.descripcion AS tipo_movimiento,
                vf2.descripcion AS estado_name
            FROM comprobantes AS cp           
                LEFT JOIN valores_flexibles AS vf
                ON vf.valor = cp.tipo_movimiento_id
                AND vf.tipo = 'TIPO_MOVIMIENTO'
                
                LEFT JOIN valores_flexibles AS vf2
                ON vf2.valor = cp.estado
                AND vf2.tipo = 'COMPROBANTE_ESTADO'
            WHERE cp.id = '{$id_comp}'"
            );

            $data = [
                'comprobante' => $comp,
                'items' => []
            ];

            $res = $_DB->query_to_array(
                "SELECT 
                    ci.cuenta, 
                    ci.cat,
                    ci.cc, 
                    ci.dv, 
                    ci.docto, 
                    ci.glosa, 
                    ci.valor,
                    ci.ct, 
                    to_char(ci.fecha, 'DD-MM-YYYY') AS fecha, 
                    to_char(ci.fecha_adquisicion, 'DD-MM-YYYY') AS fecha_adquisicion, 
                    ci.id, 
                    ci.cn, 
                    pi.analitica,
                    pi.historica,
                    pi.vencimiento,
                    pi.centro_costo,
                    pi.categoria,
                    pi.centro_negocio,
                    CASE WHEN ci.ct = '4' THEN 'haber' 
                    WHEN ci.ct = '2' THEN 'debe' END AS debe_haber 
                FROM comprobantes_items AS ci
                    LEFT JOIN plan_individual AS pi
                    ON pi.codigo_cuenta = ci.cuenta
                    AND pi.cliente_id = {$_SESSION["ss_cliente"]}
                WHERE ci.comprobante_id = {$id_comp}
                ORDER BY ci.id"
            );
            $data["items"] = $res;
            return $data;
        }
    }

    function convert_object($data,$typeDoc=null){
        $return = [];
        foreach(array_keys($data["data"]) as $tipo){
            if($tipo == 1): $type = $tipo.". ACTIVO"; endif;
            if($tipo == 2): $type = $tipo.". PASIVO"; endif;
            if($tipo == 4): $type = $tipo.". GASTOS"; endif;
            if($tipo == 5): $type = $tipo.". INGRESOS"; endif; 
            $return[] = [
                'descripcion' => $type,
                'debitos' => null,
                'creditos' => null,
                'activo' => null,
                'pasivo' => null,
                'perdidas' => null,
                'ganancias' => null
            ]; 
            foreach($data["data"][$tipo]["data"] as $keyC=>$clas){
                $return[] = [
                    'descripcion' => '    '.$keyC,
                    'debitos' => null,
                    'creditos' => null,
                    'activo' => null,
                    'pasivo' => null,
                    'perdidas' => null,
                    'ganancias' => null
                ];
                foreach($data["data"][$tipo]["data"][$keyC]["data"] as $keyR=>$rubro){
                    $return[] = [
                        'descripcion' => '        '.$keyR,
                        'debitos' => null,
                        'creditos' => null,
                        'activo' => null,
                        'pasivo' => null,
                        'perdidas' => null,
                        'ganancias' => null
                    ];
                    foreach($data["data"][$tipo]["data"][$keyC]["data"][$keyR]["data"] as $keyP=>$padre){ 
                        $return[] = [
                            'descripcion' => '            '.$keyP,
                            'debitos' => null,
                            'creditos' => null,
                            'activo' => null,
                            'pasivo' => null,
                            'perdidas' => null,
                            'ganancias' => null
                        ];
                        foreach($data["data"][$tipo]["data"][$keyC]["data"][$keyR]["data"][$keyP]["data"] as $keyH=>$hijo){ 
                            $return[] = [
                                'descripcion' => '                '.$hijo["cuenta"],
                                'debitos' => $hijo["debito"],
                                'creditos' => $hijo["credito"],
                                'activo' => $hijo["activo"],
                                'pasivo' => $hijo["pasivo"],
                                'perdidas' => $hijo["perdida"],
                                'ganancias' => $hijo["ganancia"]
                            ];
                        }
                        $return[] = [
                            'descripcion' => "            TOTAL ({$keyP})",
                            'debitos' => $padre["debito"],
                            'creditos' => $padre["credito"],
                            'activo' => $padre["activo"],
                            'pasivo' => $padre["pasivo"],
                            'perdidas' => $padre["perdida"],
                            'ganancias' => $padre["ganancia"]
                        ];
                    } 
                    $return[] = [
                        'descripcion' => "        TOTAL ({$keyR})",
                        'debitos' => $rubro["debito"],
                        'creditos' => $rubro["credito"],
                        'activo' => $rubro["activo"],
                        'pasivo' => $rubro["pasivo"],
                        'perdidas' => $rubro["perdida"],
                        'ganancias' => $rubro["ganancia"]
                    ];
                }  
                $return[] = [
                    'descripcion' => "  TOTAL ({$keyC})",
                    'debitos' => $clas["debito"],
                    'creditos' => $clas["credito"],
                    'activo' => $clas["activo"],
                    'pasivo' => $clas["pasivo"],
                    'perdidas' => $clas["perdida"],
                    'ganancias' => $clas["ganancia"]
                ];                  
            } 
            //
        }
        $return[] = [
            'descripcion' => "SUMAS",
            'debitos' => $data["debito"],
            'creditos' => $data["credito"],
            'activo' => $data["activo"],
            'pasivo' => $data["pasivo"],
            'perdidas' => $data["perdida"],
            'ganancias' => $data["ganancia"]
        ];
        if ($typeDoc==='excel'){
            return [
                'name' => "Balance General",
                'data' => $return,
                'columns' => [
                    [
                        "title" => "Descripcion",
                        "data" => 'descripcion',
                        "width" => 60
                    ],
                    [
                        "title" => "Debitos",
                        "data" => "debitos",
                        "width" => 13
                    ],
                    [
                        "title" => "Créditos",
                        "data" => "creditos",
                        "width"=> 13
                    ],
                    [
                        "title" => "Activo",
                        "data" => "activo",
                        "width"=> 13
                    ],
                    [
                        "title" => "Pasivo",
                        "data" => "pasivo",
                        "width"=> 13
                    ],
                    [
                        "title" => "Perdidas",
                        "data" => "perdidas",
                        "width"=> 13
                    ],
                    [
                        "title" => "Ganancias",
                        "data" => "ganancias",
                        "width"=> 13
                    ]
                ]
            ];
        }elseif ($typeDoc==='pdfb' || $typeDoc==='pdfl'){
            ($typeDoc==='pdfb')?$header1=$_SESSION["header_report_client"]:$header1='';
            return [
                'name' => 'balance_general',
                'css' => 'balance_general',
                'title' => "BALANCE GENERAL",
                'subtitle' => "Entre el ".date("d-m-Y",strtotime($_POST["from"]))." y el ".date("d-m-Y",strtotime($_POST["to"])),
                'header1'=> $header1,
                'header2'=> '',
                'footer1'=> date("d-m-Y"),
                'footer2'=> 'balance_general',
                'asignature1'=> '',
                'asignature2'=> '',
                'includetimestamp'=> false,
                'pagecount'=> true,
                'pageType' => 'Legal',
                'data' => $return,
                'columns' => [
                    [
                        "title" => "DESCRIPCION CUENTAS",
                        "data" => 'descripcion',
                        "width"=> '45%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10
                    ],
                    [
                        "title" => "DEBITOS",
                        "data" => "debitos",
                        "width"=> '11%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10,
                        'fnumeric' => true,
                        'fdecimal' => ($_POST["decimal"]==1)?true:false,
                        'align' => 'right'
                    ],
                    [
                        "title" => "CREDITOS",
                        "data" => "creditos",
                        "width"=> '11%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10,
                        'fnumeric' => true,
                        'fdecimal' => ($_POST["decimal"]==1)?true:false,
                        'align' => 'right'
                    ],
                    [
                        "title" => "ACTIVO",
                        "data" => "activo",
                        "width"=> '11%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10,
                        'fnumeric' => true,
                        'fdecimal' => ($_POST["decimal"]==1)?true:false,
                        'align' => 'right'
                    ],
                    [
                        "title" => "PASIVO",
                        "data" => "pasivo",
                        "width"=> '11%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10,
                        'fnumeric' => true,
                        'fdecimal' => ($_POST["decimal"]==1)?true:false,
                        'align' => 'right'
                    ],
                    [
                        "title" => "PERDIDAS",
                        "data" => "perdidas",
                        "width"=> '11%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10,
                        'fnumeric' => true,
                        'fdecimal' => ($_POST["decimal"]==1)?true:false,
                        'align' => 'right'
                    ],
                    [
                        "title" => "GANANCIAS",
                        "data" => "ganancias",
                        "width"=> '11%',
                        "headfontsize"=>10,
                        "bodyfontsize"=>10,
                        'fnumeric' => true,
                        'fdecimal' => ($_POST["decimal"]==1)?true:false,
                        'align' => 'right'
                    ]
                ]
            ];
        }
    }
}
?>