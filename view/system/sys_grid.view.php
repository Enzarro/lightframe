<?php

class sys_grid_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->model = new sys_grid_model();
        $this->FormItem = new FormItem();
    }

    function html($data = null) {
        ob_start(); ?>

        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
                <button id="main-export" class="btn btn-success" title='Exportar'><span class="fa fa-download"></span></button>	
                <label for="main-import">
                    <span class="btn btn-primary btn-file" title='Importar'><span class="fa fa-upload"></span></span>
                </label>		
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

        <div class="col-md-12" style="min-height: 100% !important;">
            <form class="form form-horizontal" id="jsonxls" action="sys_grid/export" target="_blank"></form>
        </div> 

        <div class="col-md-12" style="min-height: 100% !important;">   
            <input type="file" id="main-import" name="main-import" accept="application/JSON" style="display:none;">    
        </div>

        <?php return ob_get_clean();
    }

    function form($data = null) {
        $form = [
            'formid' => 'form-grid',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Nombre',
                'name' => 'name',
                'type' => 'text'
            ], [
                'label' => 'Tabla',
                'name' => 'table',
                'type' => 'text'
            ],[
                'label' => 'Esquema objetivo',
                'name' => 'target_schema',
                'type' => 'select',
                'type-params' => [
                    'table' => [
                        ['2', 'Cliente [CLI]'],
                        ['1', 'Sistema [DBO]']
                    ],
                    'includeNone' => false
                ]
            ],[
                'label' => 'Campos',
                'name' => 'fields',
                'type' => 'table',
                'type-params' => [
                    'config' => $this->model->getCamposDTConfig(),
                    'empty' => $this->model->getCamposDTEmptyRow()
                ],
                'prop' => [
                    'data-fitype="dtable"' => true,
                    "data-fisettings='".json_encode([
                        "rowReorder" => [
                            "dataSrc" => 'orden',
                            "selector" => "tr td:first-child"
                        ],
                        "ordering" => true,
                        "order" => [
                            [ 1, "asc" ]
                        ]
                    ])."'" => true
                ]
            ]]
        ];
        //Data es el objeto con los datos del registro
        //fields es una propiedad que contiene un arreglo de objetos que llenan la subgrilla
        if ($data) {
            $data->fields = utils::dtBuildDataFromConfig($this->model->getCamposDTConfig(), $data->fields)['data'];
        }
        ob_start(); ?>
        <?=$this->FormItem->buildArray($form, $data)?>
        <?php return ob_get_clean();
    }
}