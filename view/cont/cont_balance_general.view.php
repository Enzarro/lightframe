<?php
class cont_balance_general_view{

    private $decimal, $model;

    function __construct($data = null){
        $this->model = new cont_balance_general_model();
        if($data){
            $this->decimal = $data;
        }
    }

    function html(){
        ob_start(); ?>
        <!-- pdfs -->
        <form id="pdfForm" name="pdfForm" action="" method="post" style="display:none">
            <input type="text" id="pdfKey" name="pdfKey">
            <input type="text" id="pdfFile" name="pdfFile">
            <input type="text" id="typeDoc" name="typeDoc">
            <textarea id="pdfHeader" name="pdfHeader"></textarea>
            <textarea id="pdfBody" name="pdfBody"></textarea>
        </form>
        <!-- pdfs -->

        <div id="main-modal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl" style="width: 95%">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close closemodal" data-dismiss="modal"> x </button>
                        <h4 class="modal-title"><span class="glyphicons glyphicons-list-alt"></span> Detalle</h4>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <a href="javascript:$('#main-modal').animate({ scrollTop: 0 }, 'slow');"><i class="fa fa-rocket" aria-hidden="true"></i> top del sitio</a>
                        <a style="color:#00BCD4;" class="btn btn-link closemodal" data-dismiss="modal"><i class="fa fa-times"></i> cerrar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- TITLE -->
	   <div class="card">        
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 "><a data-toggle="collapse" href="#collapse1">
                <i class="fa fa-plus-circle"></i> <b>FILTRO</b></a>
            </div>           
            <div id="collapse1" class="collapse show" >
                <div class="card-body">              
                    <div class="form-horizontal">          
                        <div id="cboBalance"></div>                     
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 control-label">Rangos de Fechas</label>
                            <div class="col-sm-10">                           
                                <div class="input-group">                               
                                    <span class="input-group-addon">                                      
                                        <i class="fa fa-calendar" aria-hidden="true"></i>  <small>desde</small>                                    
                                    </span>                                 
                                    <input type="text" class="form-control" id="calendar_from">
                                    <span class="input-group-addon">                                  
                                        <i class="fa fa-calendar" aria-hidden="true"></i>  <small>hasta</small>                                        
                                    </span>                                 
                                    <input type="text" class="form-control" id="calendar_to">
                                </div>                             
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="filtro_btn" class="col-sm-2 control-label"></label>
                            <div class="col-sm-10">
                                <button type="button"  id="filtro_btn" class="btn btn-primary"><span class="glyphicons glyphicons-search"></span> Buscar</button>                             
                                <input type="checkbox" class="" id="decimalesFrm">
                                <label class="checkDecimalesFrm" for="decimalesFrm" style="font-style: italic;font-weight:bold;"> Mostrar decimales</label>
                            </div>
                        </div>                  
                    </div>                   
                </div>
            </div>         
        </div>
		
        <div  id="area_filtro_cuerpo" style="display:none" class="card">       
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2">          
                <iframe id="documento_xls_area" src="" style="display:none"></iframe>
                <div class="btn-group">
                    <button type="button" class="btn btn-default" id="export_xls" name="export_xls"><i class="fa fa-file-excel" aria-hidden="true" style="color:green;"></i> Excel</button>
                    <button type="button" class="btn btn-default" id="export_pdfb" name="export_pdfb"><i class="fa fa-file-pdf" aria-hidden="true" style="color:red;"></i> Borrador</button>
                    <button type="button" class="btn btn-default" id="export_pdfl" name="export_pdfl"><i class="fa fa-file-pdf" aria-hidden="true" style="color:red;"></i> Legal</button>
                </div>
            </div>           
            <div class="card-body">       
                <div id="xls_area"></div>            
            </div>          
            <div class="card-footer" style="text-align:right">         
                <a href="javascript:$('html, body').animate({ scrollTop: 0 }, 'slow');"><i class="fa fa-rocket" aria-hidden="true"></i> top del sitio</a>         
            </div>         
        </div>

        <?php return ob_get_clean();
    }

    function showCbo($data){
        $balance = new FormItem([         
            'name' => 'tipo',
            'type' => 'select',
            'type-params' => [
                "table" => array_map(function($row){
                    return [
                        $row['valor'],
                        $row['descripcion']
                    ];
                },$data),
                "includeNone" => true
            ],		
            'wrap' => false
        ]);
        ob_start(); ?>
        <div class="form-group row">
            <label for="tipo" class="col-sm-2 control-label">Tipo de Balance</label>
            <div class="col-sm-10">
                <?=$balance->build()?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    function showTable($data, $from, $to){

        if(isset($data["data"])){
            if($this->decimal == 1){
                $dec = true;
            } else {
                $dec = false;
            }
            ob_start(); ?>
            
            <form id="xlsform" action="http://34.236.202.115/" target="_blank" method="post">
                <textarea name="JSONtoXLS" id="JSONtoXLS" style="display:none;">
                    <?=json_encode($this->model->convert_object($data,"excel"), JSON_PRETTY_PRINT)?>
                </textarea>
            </form>
            <form id="pdfbform" action="http://34.236.202.115/" target="_blank" method="post">
                <textarea name="JSONtoPDF" id="JSONtoPDF" style="display:none;">
                    <?=json_encode($this->model->convert_object($data,"pdfb"), JSON_PRETTY_PRINT)?>
                </textarea>
            </form>
            <form id="pdflform" action="http://34.236.202.115/" target="_blank" method="post">
                <textarea name="JSONtoPDF" id="JSONtoPDF" style="display:none;">
                    <?=json_encode($this->model->convert_object($data,"pdfl"), JSON_PRETTY_PRINT)?>
                </textarea>
            </form>
            
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-condensed" id="tabla_pdf">
                    <thead>
                        <tr>
                            <th>DESCRIPCION CUENTAS</th>
                            <th>DEBITOS</th>
                            <th>CREDITOS</th>
                            <th>ACTIVO</th>
                            <th>PASIVO</th>
                            <th>PERDIDAS</th>
                            <th>GANANCIAS</th>
                        </tr>
                    </thead>
                    <?php 
                    $cont = 0;
                    foreach(array_keys($data["data"]) as $tipo): 
                        if($tipo == 1): $type = $tipo.". ACTIVO"; endif;
                        if($tipo == 2): $type = $tipo.". PASIVO"; endif;
                        if($tipo == 4): $type = $tipo.". GASTOS"; endif;
                        if($tipo == 5): $type = $tipo.". INGRESOS"; endif;  
                    ?>
                    <tr class="active">
                        <td style="border:0px solid #ccc;" colspan="7"><b><?=$type;?></b>.</td>
                    </tr>
                        <?php foreach($data["data"][$tipo]["data"] as $keyC=>$clas):?>
                        <tr>
                            <td style="border:0px solid #ccc; padding-left:20px;" colspan="7"><?=$keyC?>.</td>
                        </tr>
                            <?php foreach($data["data"][$tipo]["data"][$keyC]["data"] as $keyR=>$rubro): ?>
                            <tr>
                                <td style="border:0px solid #ccc; padding-left:40px;" colspan="7"><?=$keyR?>.</td>
                            </tr>
                                <?php foreach($data["data"][$tipo]["data"][$keyC]["data"][$keyR]["data"] as $keyP=>$padre): ?>
                                <tr>
                                    <td style="border:0px solid #ccc; padding-left:60px;" colspan="7"><?=$keyP?></td>
                                </tr>
                                    <?php foreach($data["data"][$tipo]["data"][$keyC]["data"][$keyR]["data"][$keyP]["data"] as $keyH=>$hijo): ?>
                                    <tr>
                                        <td style="border:0px solid #ccc; padding-left:80px;"><a style="color:blue;" class="bg_detalle" ind_cuenta='<?=$keyH?>'><?=$hijo["cuenta"]?></a></td>
                                        <td style="border:0px solid #ccc; text-align:right;"><?=$this->fNum($hijo["debito"], $dec)?></td>
                                        <td style="border:0px solid #ccc; text-align:right;"><?=$this->fNum($hijo["credito"], $dec)?></td>
                                        <td style="border:0px solid #ccc; text-align:right;"><?=$this->fNum($hijo["activo"], $dec)?></td>
                                        <td style="border:0px solid #ccc; text-align:right;"><?=$this->fNum($hijo["pasivo"], $dec)?></td>
                                        <td style="border:0px solid #ccc; text-align:right;"><?=$this->fNum($hijo["perdida"], $dec)?></td>
                                        <td style="border:0px solid #ccc; text-align:right;"><?=$this->fNum($hijo["ganancia"], $dec)?></td>
                                    </tr>
                                    <?php endforeach;?>
                                <tr>
                                    <td style="border:0px solid #ccc; padding-left:60px;"><b>TOTAL (<?=$keyP?>)</b></td>
                                    <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($padre["debito"], $dec)?></b></td>
                                    <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($padre["credito"], $dec)?></b></td>
                                    <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($padre["activo"], $dec)?></b></td>
                                    <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($padre["pasivo"], $dec)?></b></td>
                                    <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($padre["perdida"], $dec)?></b></td>
                                    <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($padre["ganancia"], $dec)?></b></td>
                                </tr>
                                <?php endforeach; ?>
                            <tr>
                                <td style="border:0px solid #ccc; padding-left:40px;"><b>TOTAL (<?=$keyR?>)</b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($rubro["debito"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($rubro["credito"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($rubro["activo"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($rubro["pasivo"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($rubro["perdida"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($rubro["ganancia"], $dec)?></b></td>
                            </tr>
                            <?php endforeach; ?> 
                            <tr>
                                <td style="border:0px solid #ccc; padding-left:20px;"><b>TOTAL (<?=$keyC?>)</b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($clas["debito"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($clas["credito"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b>
                                <?php 
                                if($clas["debito"] > $clas["credito"]) {
                                    echo $this->fNum($clas["activo"], $dec);
                                } else {
                                    echo "-";
                                }
                                ?>
                                </b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b>
                                <?php
                                if($clas["debito"] < $clas["credito"]) {
                                    echo $this->fNum($clas["pasivo"], $dec);
                                } else {
                                    echo "-";
                                }
                                ?>
                                </b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($clas["perdida"], $dec)?></b></td>
                                <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($clas["ganancia"], $dec)?></b></td>
                            </tr>    
                        <?php endforeach; ?>
                        <tr>
                            <td style="border:0px solid #ccc; padding-left:0px;"><b>TOTAL (<?=$type?>)</b></td>
                            <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum(isset($data[$cont]["debito"])?$data[$cont]["debito"]:0, $dec)?></b></td>
                            <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum(isset($data[$cont]["credito"])?$data[$cont]["credito"]:0, $dec)?></b></td>
                            <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum(isset($data[$cont]["activo"])?$data[$cont]["activo"]:0, $dec)?></b></td>
                            <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum(isset($data[$cont]["pasivo"])?$data[$cont]["pasivo"]:0, $dec)?></b></td>
                            <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum(isset($data[$cont]["perdida"])?$data[$cont]["perdida"]:0, $dec)?></b></td>
                            <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum(isset($data[$cont]["ganancia"])?$data[$cont]["ganancia"]:0, $dec)?></b></td>
                        </tr>
                    <?php $cont++; endforeach; ?>
                    <tr class="active">
                        <td style="border:0px solid #ccc;"><b>SUMAS </b></td>      
                        <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($data['debito'], $dec)?></b></td>
                        <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($data["credito"], $dec)?></b></td>
                        <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($data["activo"], $dec)?></b></td>
                        <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($data["pasivo"], $dec)?></b></td>
                        <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($data["perdida"], $dec)?></b></td>
                        <td style="border:0px solid #ccc; text-align:right;"><b><?=$this->fNum($data["ganancia"], $dec)?></b></td>           
                    </tr>
                </table>
            </div>
            <?php return ob_get_clean();
        } else {
            ob_start(); ?>
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-8">
                    <div class="alert alert-warning" role="alert">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No hay datos disponibles para mostrar.
                    </div>                  
                </div>
            <?php return ob_get_clean();
        }    
    }

    function showTableByAccount($data){
        if(isset($data)){
            if($this->decimal == 1){
                $dec = true;
            } else {
                $dec = false;
            }
            ob_start();?>         
            <h3>LIBRO MAYOR</h3>
            <div class="table-responsive">
                <table class="table table-hover  table-bordered table-condensed" id="tabla_pdf_detalle">
                    <thead>
                        <tr class="active">								
                            <th >TM</th>
                            <th>FOLIO</th>
                            <th>RUT</th>
                            <th>FECHA</th>
                            <th>CUENTA</th>
                            <th>CC</th>
                            <th>CN</th>
                            <th>N° DOC</th>
                            <th>GLOSA</th>
                            <th>FECHA VCTO.</th>
                            <th>DEBE</th>
                            <th>HABER</th>
                            <th>SALDO</th>							
                        </tr>
                    <thead>
                    <tr>
                        <td colspan="13"><b>CUENTA: <?=$data["cuenta"]?></b></td>
                    </tr>
                    <tr>
                        <td colspan="13"><br><b><?=strtoupper($data["nombre_cuenta"])?></b></td>
                    </tr>
                    <?php foreach(array_keys($data["data"]) as $mes): ?>
                        
                        <?php foreach($data['data'][$mes]["data"] as $row):?>
                        <tr>
							<td><?=$row["tipo_movimiento"]?></td>
							<td><a style="color:blue;" class="com_detalle" ind_account='<?=$row["cuenta"]?>' ind_folio='<?=$row["id_comprobante"]?>'><?=$row["folio"]?></a></td>
							<td><?=$row["dv"]?></td>
							<td><?=$row["fecha_ingreso_format"]?></td>
							<td><?=$row["cuenta"]?></td>
							<td><?=$row["cc"]?></td>
							<td><?=$row["cn"]?></td>
							<td><?=$row["docto"]?></td>
                            <td><?=$row["glosa"]?></td>
                            <td></td>
							<td style="text-align:right"><?=$this->fNum($row["debe"], $dec)?></td>
							<td style="text-align:right"><?=$this->fNum($row["haber"], $dec)?></td>
							<td style="text-align:right"><?=$this->fNum($row["saldo"], $dec)?></td>
						</tr>
                        <?php endforeach; ?>
                            <tr class="active">
                                <td colspan="10" style="text-align:right"><b>TOTAL <?=strtoupper(strftime('%B',$mes))?></b></td>
                                <td style="text-align:right"><b><?=$this->fNum($data["data"][$mes]["debe"], $dec)?></b></td>
                                <td style="text-align:right"><b><?=$this->fNum($data["data"][$mes]["haber"], $dec)?></b></td>
                                <td style="text-align:right"><b><?=$this->fNum($data["data"][$mes]["saldo"], $dec)?></b></td>
                            </tr>
                    <?php endforeach; ?>
                    <tr class="active">
						<td colspan="10" style="text-align:right"><b>TOTAL FINAL</b></td>
						<td style="text-align:right"><b><?=$this->fNum($data['debe'], $dec)?></b></td>
						<td style="text-align:right"><b><?=$this->fNum($data['haber'], $dec)?></b></td>
						<td style="text-align:right"><b><?=$this->fNum($data['saldo'], $dec)?></b></td>
					</tr>
                </table>
            </div>
            <?php return ob_get_clean();
            
        }       
    }

    function showTableByComprobant($data){
        if(isset($data)){
            if($this->decimal == 1){
                $dec = true;
            } else {
                $dec = false;
            }
            ob_start(); ?>
            <button type="button" class="btn btn-link" id="backLm"><i class="fa fa-angle-left"></i> volver a libro mayor</button>
            <h3>COMPROBANTE</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed" id="tabla_pdf_comprobante_detalle">
                    <tr>
                        <td class="active" style="width:200px;"><b>TIPO MOVIMIENTO</b></td>
                        <td><?=$data["comprobante"][0]["tipo_movimiento"]?></td>
                    </tr>
                    <tr>
                        <td class="active"><b>FOLIO</b></td>
                        <td><?=$data["comprobante"][0]["folio"]?></td>
                    </tr>                    
                    <tr>
                        <td class="active"><b>FECHA INGRESO</b></td>
                        <td><?=$data["comprobante"][0]["fecha_ingreso"]?></td>
                    </tr>                   
                    <tr>
                        <td class="active"><b>ESTADO</b></td>
                        <td><?=strtoupper($data["comprobante"][0]["estado_name"])?></td>
                    </tr>  
                </table>
            </div>

            <h3>ITEMS</h3>
            <div class="table-responsive">
                <table class="table table-hover  table-bordered table-condensed" id="tabla_pdf">
                    <thead>
                        <tr class="active">								
                            <th>CUENTA</th>
                            <th>CAT.</th>
                            <th>CC</th>
                            <th>CN</th>
                            <th>RUT</th>
                            <th>DOCTO.</th>
                            <th>GLOSA</th>
                            <th>VALOR</th>
                            <th>CT</th>
                            <th>FECHA</th>						
                        </tr>
                    <thead>
                    <tbody>
                    <?php foreach($data["items"] as $items):?>
                        <tr>
                            <td><?=$items["cuenta"]?></td>
							<td><?=$items["cat"]?></td>
							<td><?=$items["cc"]?></td>
							<td><?=$items["cn"]?></td>
							<td><?=$items["dv"]?></td>
							<td><?=$items["docto"]?></td>
							<td><?=strtoupper($items["glosa"])?></td>
							<td align="right"><?=$this->fNum($items["valor"], $dec)?></td>
							<td><?=strtoupper($items["debe_haber"])?></td>
							<td><?=utils::formatDate($items["fecha"])?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>  
                </table>
            </div>
            <?php return ob_get_clean();
        }
    }

    public static function fNum($value, $decimals = true, $zerosymbol = '-') {
        if (is_null($value)) return 0;
        if (!is_numeric($value)) return $value;
        if (is_bool($decimals)) { 
            if ($decimals) {
                $decimals = 2;
            } else {
                $decimals = 0;
            }
        }
        if ($value < 0) return ''.number_format(abs($value), $decimals, ',', '.').'';
        elseif ($value > 0) return number_format($value, $decimals, ',', '.');
        elseif ($zerosymbol) return $zerosymbol;
        else return 0;
    }
}
?>