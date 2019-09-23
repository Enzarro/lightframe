<?php

class sys_grid_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->model = new sys_grid_model();
        $this->FormItem = new FormItem();
        $this->form = [
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
            ],
            [
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
                        "liveSearch" => true
                    ])."'" => true
                ]
            ]]
        ];
    }

    function html() {
        ob_start(); ?>
    
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                    <!-- <h3 class="box-title"></h3> -->
                        <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                        <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
                    </div>
                    <div class="box-body">
                        <table id="mantenedores" class="table table-bordered table-striped"></table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-default" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Grilla</h4>
                </div>
                <div class="modal-body">
                    <p>One fine body…</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button id="save" type="button" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    
        <?php return ob_get_clean();
    }

    function form($data = null) {
        $form = $this->form;
        if ($data) {
            //Llenar los "value" de los FI del form desde la data recibida
            foreach (array_keys($form['fields']) as $key) {
                if ($form['fields'][$key]['name'] == 'name') {
                    $form['fields'][$key]['value'] = $data->name;
                } else if ($form['fields'][$key]['name'] == 'table') {
                    $form['fields'][$key]['value'] = $data->table;
                } else if ($form['fields'][$key]['name'] == 'fields') {
                    $form['fields'][$key]['value'] = utils::dtBuildDataFromConfig($this->model->getCamposDTConfig(), $data->fields)['data'];
                    // $form['fields'][$key]['value'] = array_map(function($row) {
                    //     return [
                    //         'id' => $row->id,
                    //         'name' => $row->name,
                    //         'column' => $row->column,
                    //         'type' => $row->type,
                    //         'primary' => isset($row->primary)?$row->primary:null
                    //     ];
                    // }, $data->fields);
                }
            }
        }
        ob_start(); ?>
        <?=$this->FormItem->buildArray($form)?>
        <?php return ob_get_clean();
    }
}