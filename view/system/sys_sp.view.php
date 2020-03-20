<?php

class sys_sp_view {

    function __construct() {

    }

    function html($data = null) {
        ob_start(); ?>

        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 " style="display: none;">
                <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>				
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="mantenedores"></table>					
            </div>				
        </div>	

        <div class="modal animated fade" id="modal-default" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-bottom-blue-grey border-bottom-lighten-4">
                        <h4 class="modal-title"><?=$data['modalTitle']?></h4>
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
}