<?php

class cont_plan_general_view{

    function __construct() {

        $this->model = new cont_plan_general_model();

    }

    function html($data = null){
        ob_start();?>

        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <button class="btn btn-primary" id="pg-new"><span class="fa fa-plus"></span> Nuevo</button>
                <button title="Eliminar archivo(s) seleccionado(s)" class="btn btn-danger" id="pg-delete"><span class="fa fa-trash"></span> Eliminar</button>				
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="plan_general"></table>					
            </div>				
        </div>
        <div class="modal animated fade" id="modal-plangeneral" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom-blue-grey border-bottom-lighten-4">
                        <h4 class="modal-title"><?=$data['title']?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body "></div>
                    <div class="modal-footer  border-top-blue-grey border-top-lighten-4">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                        <button id="save" type="button" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php return ob_get_clean();
    }

    function form($data = null){ 
        $codigo = isset($data["codigo_cuenta"]) ? $this->model->get($data["codigo_cuenta"])['codigo_cuenta'] : '';
        $descripcion = isset($data["codigo_cuenta"]) ? $this->model->get($data["codigo_cuenta"])['descripcion'] : '';
        $readonly = isset($data["codigo_cuenta"]) ? 'readonly' : '';
        $subcuentas = isset($data["codigo_cuenta"]) ? $this->model->getChild($data["codigo_cuenta"]) : null;
         ob_start();?>

        <form class="row" id="form_grid">   
            <div class="col-md-5 form-group required">
                <label class="control-label">Código</label>
                <input type="text" class="form-control" placeholder="Código" id="codigo_cuenta" name="codigo_cuenta" <?= $readonly; ?>
                value="<?= $codigo; ?>" required>
                <?= isset($data["codigo_cuenta"]) ? "<input type=\"hidden\" id=\"type\" value=".$codigo.">"  : ''; ?>
            </div>
            <div class="col-md-7 form-group required">
                <label class="control-label">Detalle</label>
                <input type="text" class="form-control" placeholder="Detalle" id="descripcion" name="descripcion" 
                value="<?= $descripcion; ?>" required>
            </div>
            <div class="col-md-12 form-group">
                <h5 >Subcuentas</h5>
                <div id="listdiv">
                <?php if(!is_null($subcuentas)){
                    foreach($subcuentas as $sc){
                    ?>
                    <div class="row col-md-12" id="subcuentas">
                        <div class="col-md-5 form-group required">
                            <label class="control-label">Código</label>
                            <input type="text" class="form-control cod_cuentas" name="cod_cuentas[]" placeholder="Código" 
                                value="<?= $sc["codigo_cuenta"]; ?>" readonly required >
                            <input type="hidden" class="type" value="<?= $sc["codigo_cuenta"]; ?>">
                        </div>
                        <div class="col-md-7 form-group required">
                            <label class="control-label">Detalle</label>
                            <div class="input-group">
                                <input type="text" class="form-control detalle_cuentas" name="detalle_cuentas[]" placeholder="Detalle"
                                value="<?= $sc["descripcion"]; ?>" required>
                                <span class="input-group-btn" style="display:none;">
                                    <button class="btn btn-danger" type="button" onclick="subcuentas_delete(this);"  ><i class="fa fa-times"></i></button>
                                </span>
                            </div>                        
                        </div>
                    </div>
                    <?php
                    }
                } else { ?>
                    <div class="row col-md-12" id="subcuentas">
                        <div class="col-md-5 form-group required">
                            <label class="control-label">Código</label>
                            <input type="text" class="form-control cod_cuentas" name="cod_cuentas[]" placeholder="Código" required>
                        </div>
                        <div class="col-md-7 form-group required">
                            <label class="control-label">Detalle</label>
                            <div class="input-group">
                                <input type="text" class="form-control detalle_cuentas" name="detalle_cuentas[]" placeholder="Detalle" required>
                                <span class="input-group-btn" style="display:none;">
                                    <button class="btn btn-danger" type="button" onclick="subcuentas_delete(this);"  ><i class="fa fa-times"></i></button>
                                </span>
                            </div>                        
                        </div>
                    </div>
                <?php } ?>
                    <div id="subcuentas_clone">
                    </div>
                </div>
            </div>            
            <div class="col-md-12 text-center">
                <button type="button" class="btn btn-sm btn-primary" onclick="subcuentas_new();"><i class="fa fa-plus"></i> Agregar Subcuenta</button>
            </div>
        </form>

        <?php return ob_get_clean();
    }



}


?>