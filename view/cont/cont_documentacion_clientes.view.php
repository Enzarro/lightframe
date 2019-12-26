<?php
class cont_documentacion_clientes_view{
    function html($data = null){
        ob_start();?>
        
        <style>
        .btn-file {
            position: relative;
            overflow: hidden;
        }
        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;   
            cursor: inherit;
            display: block;
        }
        </style>

        <!-- Grid -->
        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <button class="btn btn-primary" id="ccg-new"><span class="fa fa-plus"></span> Nuevo</button>
                <button title="Eliminar archivo(s) seleccionado(s)" class="btn btn-danger" id="ccg-delete"><span class="fa fa-trash"></span> Eliminar</button>				
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="mantenedores"></table>					
            </div>				
        </div>

        <!-- Modal -->
        <div class="modal animated fade" id="modal-default" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header border-bottom-blue-grey border-bottom-lighten-4">
                        <h4 class="modal-title"><?=$data['modalTitle']?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer  border-top-blue-grey border-top-lighten-4">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                        o 
                        <button id="modal-save" type="button" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    function throwForm($data = null) {
        if($data!=null){ $Nnombre=$data->nombre; }else{ $Nnombre=""; }
        $files = [];
        $cDocumentacionClientesModel = new cont_documentacion_clientes_model();
        $nombre = new FormItem;
        $nombre->setType("text");
        $nombre->setBasic("Nombre", "nombre",$Nnombre);
        
        // --- Form HTML Code ---
        ob_start(); ?>
        <form role="form-generic">
            <h3>Carpeta</h3>
            <div class="form-group col-xs-12 col-md-6 form-group-sm " style="margin-bottom: 0px;" >
                <?php echo $nombre->build(); ?>
            </div>
            <div class="form-group col-xs-12 col-md-6 form-group-sm " style="margin-bottom: 0px;" >
                <label>
                    <input class="control-label col-sm-33" name="activo" id="folder-active" type="checkbox" <?php if(!$data || $data->activo): ?>checked<?php endif; ?>> Activo
                </label>
            </div>
        </form>
        <?php if ($data):?>
            <h3>Archivos</h3>
            <div class="card">
                <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2">
                    <button type="button" title="Nuevo archivo" btnpermis="btn_nuevo" class="btn btn-primary" id="file-new">
                        <i class="fa fa-plus" aria-hidden="true"></i> Nuevo
                    </button>
                    <button type="button" title="Eliminar archivo(s) seleccionado(s)" btnpermis="btn_eliminar" class="btn btn-danger" id="file-delete">
                        <i class="fa fa-trash" aria-hidden="true"></i> Eliminar
                    </button>
                </div>
                <div class="card-body">
                    <table id="dt-archivos" width="100%" class="table table-striped table-bordered table-hover" cellspacing="0"></table>
                </div>
            </div>
        <?php endif; ?>
            
        <?php return ob_get_clean();
    }

    function uploadForm($id = null) {
        $data = null;
        $cDocumentacionClientesModel = new cont_documentacion_clientes_model();
        if ($id) {
            $data = $cDocumentacionClientesModel->fnGetAllFileData($_POST["client"],$id);
        }else{
            $data = (object)[
                'nombre'=>'',
                'descripcion'=>'',
                'file'=>'',
                'activo'=>''
            ];
        }
        
        $nombre = new FormItem;
        $nombre->setType("text");
        $nombre->setBasic("Nombre", "nombre", $data->nombre);
        
        $descripcion = new FormItem;
        $descripcion->setType("textarea");
        $descripcion->setBasic("Descripci&oacute;n", "descripcion", $data->descripcion);
        
        $archivo = new FormItem;
        $archivo->setType("text");
        $archivo->setBasic("Archivo", "archivo", $data->file);
        $archivo->setAddon('l', '<input id="upload-file" type="file" accept="filetype/.xls,.xlsx,.pdf,.doc,.docx"><span class="fa fa-upload" aria-hidden="true"></span>', 'addon btn btn-primary btn-file');
        $archivo->prop["readonly"] = true;
        
        
        
        // --- Form HTML Code ---
        ob_start(); ?>
            <button type="button" class="btn btn-outline-info" id="file-back">
                <i class="fa fa-chevron-left" aria-hidden="true"></i> Volver
            </button>
            <h3><br><b>Archivo:</b> <?php if($data->nombre): ?>Editar<?php else: ?>Nuevo<?php endif; ?></h3>
            <hr>
            <div class="form-group col-xs-12 col-md-6 form-group-sm " style="margin-bottom: 0px;" >
                <?php echo $nombre->build(); ?>
                <?php echo $descripcion->build(); ?>
            </div>
            
            <div class="form-group col-xs-12 col-md-6 form-group-sm " style="margin-bottom: 0px;" >
                <?php echo $archivo->build(); ?>
                <small>S&oacute;lo se admiten archivos Word, Excel y PDF</small>
            </div>
            <br>
            <div class="form-group" style="margin-top: 0px;">
                <label>
                    <input id="file-active" type="checkbox" <?php if($data->activo=='' || $data->activo): ?>checked<?php endif; ?>> Activo
                </label>
            </div>
        <?php return ob_get_clean();
    }
}

?>