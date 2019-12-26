<?php

class cont_plan_individual_view{

    function __construct() {
        $this->model = new cont_plan_individual_model();
    }

    function html($data = null){
        ob_start();?>
        <div class="card-content">
            <ul class="nav nav-tabs nav-justified nav-underline">
                <li class="nav-item">
                    <a class="nav-link active " id="tab1" data-toggle="tab" href="#admin_tab" aria-controls="admin_tab" aria-expanded="true">
                        <i class="fas fa-cog"></i>&nbsp;Administración
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="tab2" data-toggle="tab" href="#masiva_tab" aria-controls="masiva_tab">
                        <i class="fas fa-upload"></i> &nbsp;Carga Masiva
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="tab3" data-toggle="tab" href="#export_tab" aria-controls="export_tab">
                        <i class="fas fa-download"></i> &nbsp;Exportar
                    </a>
                </li>
            </ul>
            <div class="tab-content card">
                <div class="tab-pane active" id="admin_tab" role="tabpanel" aria-labelledby="tab1" aria-expanded="false" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body">
                                <form class="form form-horizontal" id="paso_1">
                                    <h4 class="form-section"> <i class="fas fa-tasks"></i>Paso 1 &nbsp;&nbsp;<small class="secondary" style="font-size:72%;">SELECCIÓN DE CUENTA PADRE</small></h4>
                                    <div class="form-body">
                                        <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="cuentas_generales"></table>
                                    </div>
                                </form>
                                <form class="form form-horizontal" id="paso_2" >
                                    <button type="button"  class="paso1 pull-right btn btn-sm btn-outline-info round"> <i class="fas fa-angle-double-left icon-left"></i> VOLVER AL PASO 1</button>
                                    <h4 class="form-section"><i class="fas fa-sitemap"></i>Paso 2 &nbsp;&nbsp;<small class="title2 secondary" style="font-size:72%;"></small> </h4>
                                    <div class="form-body">
                                        <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="subcuentas"></table>
                                    </div>
                                </form>
                                <form class="form form-horizontal" id="paso_3" >
                                    <button type="button" class="paso2 pull-right btn btn-sm btn-outline-info round"> <i class="fas fa-angle-double-left"></i> VOLVER AL PASO 2</button>
                                    <h4 class="form-section"> <i class="fas fa-cogs"></i>Paso 3 &nbsp;&nbsp;<small class="title3 secondary" style="font-size:72%;"></small> </h4>
                                    <div class="form-body"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane card-content" id="masiva_tab" role="tabpanel" aria-labelledby="tab2" aria-expanded="false" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body">
                                <form class="form form-horizontal " method="post" enctype="multipart/form-data" 
                                    action="" >                                    
                                     <div class="form-body">
                                        <h4 class="form-section"> <i class="fas fa-file-upload"></i>Adjuntar Plantilla</h4>
                                        <div class="form-group row">
                                            <div class="col-md-5 mx_auto text-center border-right-blue-grey border-right-lighten-4 pr-2">
                                                <label for="file_add" style="cursor: pointer;">
                                                    <div class="card card-body">
                                                        <div class="card card-body border-info bg-transparent">
                                                            <span class="fas fa-file font-large-3 info"></span>
                                                            <h5 class="text-uppercase info mt-1">Seleccionar Documento</h5>
                                                        </div>

                                                    </div>
                                                </label>
                                               <input type="file" id="file_add" name="file_add"  >
                                            </div>
                                            <div class="col-md-5 mx_auto">
                                                <div class="card bg-info">
                                                    <div class="card-header">
                                                        <h4 class="card-title  text-white"><i class="fas fa-info-circle" style="font-size:1.73rem;"></i> Información</h4>
                                                    </div>
                                                    <div class="card-body pt-0">
                                                        <ul class="list-group text-white">
                                                            <li class="list-group-item bg-info border-0 pt-1 pb-1 pl-0">
                                                                <i class="fas fa-file-excel" style="font-size:1.5rem;"></i> Solo adjuntar documentos .XLS o .XLSX
                                                            </li>
                                                            <li class="list-group-item bg-info border-0 pt-1 pb-1 pl-0">
                                                                <i class="fas fa-paste" style="font-size:1.5rem;"></i> Peso máximo 99999M</li>
                                                            <li class="list-group-item bg-info border-0 pt-1 pb-1 pl-0">
                                                                <a href="javascript:exportxls('masivo_individual');" class="text-white"><i class="fas fa-file-download" style="font-size:1.5rem;"></i> Descargar Plantilla</a></li>
                                                        </ul>
                                                    </div>                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                </form>
                            </div>
                        </div>
                    </div>                
                </div>
                <div class="tab-pane card-content" id="export_tab" role="tabpanel" aria-labelledby="tab3" aria-expanded="false" >
                    <div class="row">
                        <div class="col-md-12" style="min-height: 100% !important;">
                            <div class=" card-body ">
                                <form class="form form-horizontal" id="xlsform" action="http://34.236.202.115/" target="_blank" method="post">
                                    <div class="form-body">
                                        <h4 class="form-section"> <i class="fas fa-file-download"></i>Exportar Plan Individual</h4>
                                        <div class="form-group row">
                                            <textarea name="JSONtoXLS" id="JSONtoXLS" style="display:none;"></textarea>
                                            <div class="col-md-7 mx_auto pr-2">
                                                <div class="card pull-right">
                                                    <div class="card-content card-body">
                                                        <button type="button" class="card card-body border-success bg-success" onclick="exportxls('export_individual');">
                                                            <span class="fas fa-file-excel font-large-3 text-white" style="margin:auto;"></span>
                                                            <h5 class="text-uppercase text-white mt-1">Descargar Plan Individual</h5>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>                       
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php return ob_get_clean();
    }

    function get($data = null){
        $tipos = ["pcga","fip","ifrs"];
        $caracteristicas = [array("db" => "analitica_ck","value" => "analitica"), array("db" =>"historica_ck","value" => "historica"),  
                array("db" => "vencimiento_ck", "value" => "vencimiento"),array("db" => "centro_costo_ck", "value" => "centro_costo"), 
                array("db" => "categoria_ck", "value" => "categoria"),array("db" => "valor_ck", "value" => "valor"), 
                array("db" => "inversiones_ck", "value" => "inversiones"), array("db" => "afijo_ck", "value" => "afijo")];
        $caract = $this->model->listcaracteristicas($data);
        $select = $this->model->listselect();        
        $partida = $this->model->listpartida();
        ob_start();?>
        <div class="form-body">
            <div class="col-md-12 mb-1 table-responsive text-center">
                <table class="table table-sm mb-0 ">
                    <thead>
                        <tr class="primary">
                            <th>TIPO</th>
                            <th>ACTIVO</th>
                            <th width="40%">RUBRO</th>
                            <th width="40%">CLASIFICACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach($tipos as $t){
                        ?>
                            <tr>
                                <td><strong><?php echo strtoupper($t); ?></strong></td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="<?= $t ?>" id="<?=$t; ?>" <?= $caract[$t];?>
                                            onchange="activar_select(this)">
                                        <label class="custom-control-label" for="<?= $t ?>"></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select class="form-control" name="<?= "rubros_".$t ?>" id="<?= "rubros_".$t; ?>"
                                             <?= ($caract[$t]=='') ? "disabled" : "" ; ?>>
                                        <?php foreach ($select["rubros_".$t] as $rp){
                                            $selected = ($caract[$t."_rubro_id"] == $rp["id"]) ? $caract[$t."_rubro_id"] : "";
                                            echo "<option value='".$rp["value"]."'  ".$selected.">".$rp["name"]."</option>";
                                        }?>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select class="form-control" name="<?php echo "clasificacion_".$t ?>" id="<?php echo "clasificacion_".$t; ?>"
                                            <?= ($caract[$t]=='') ? "disabled" : "" ; ?>>
                                        <?php foreach ($select["clasificacion_".$t] as $rp){
                                            $selected = ($caract[$t."_clasificacion_id"] == $rp["id"]) ? $caract[$t."_clasificacion_id"] : "";
                                            echo "<option value='".$rp["value"]."' ".$selected.">".$rp["name"]."</option>";
                                        }?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12 border-top-blue-grey border-top-lighten-4">
                <div class="card-body row">
                    <div class="col-md-2"><h6 class="card-title text-uppercase"><i class="fas fa-search-dollar"></i> Características</h6></div>
                    <div class="col-md-10">
                        <div class="form-group">
                            <select class="form-control" name="caracteristicas" id="caracteristicas" multiple>
                                <?php 
                                    foreach($caracteristicas as $c){
                                        echo "<option value='".$c["value"]."'  ".$caract[$c["db"]]."> ".strtoupper(str_replace('_',' ',$c["value"]))."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="col-md-12 border-top-blue-grey border-top-lighten-4">
                <div class="card-body row">
                    <div class="col-md-2"><h6 class="card-title text-uppercase"><i class="fas fa-tags "></i> Partida Doble</h6></div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <select  class="form-control" name="doble" id="doble">
                                <?php
                                    foreach($partida as $p){
                                        echo "<option value='".$p["codigo_cuenta"]."' data-subtext=' - ".$p["descripcion"]."' >".$p["codigo_cuenta"]."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <select  class="form-control" name="neteo" id="neteo">
                                <?php
                                    foreach($partida as $p){
                                        echo "<option value='".$p["codigo_cuenta"]."' data-subtext=' - ".$p["descripcion"]."' >".$p["codigo_cuenta"]."</option>";
                                    }
                                ?> 
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <button type="button" id="save_form" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>

    <?php return ob_get_clean();
    }
}


?>