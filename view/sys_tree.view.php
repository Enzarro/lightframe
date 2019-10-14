<?php

class sys_tree_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $utils = new utils();
        $iconos = $utils->get('https://glyphsearch.com/data/batch.json', [], true);
        
        $iconos = array_filter($iconos, function($row) {
            return isset($row['_tags']) ? $row['_tags'][0] == 'font-awesome' || $row['_tags'][0] == 'glyphicons' : false;
        });
        $this->model = new sys_tree_model();
        $data = $this->model->load_father();
        $this->FormItem = new FormItem();
        $this->form = [
                'formid' => 'form-grid',
                'roweven' => true,
                'common' => [
                    'horizontal' => true,
                    'size' => 'sm',
                    'stack' => true
                ],
                'fields' => [
                    [
                    'label' => 'Nombre',
                    'name' => 'name',
                    'type' => 'text',
                    'value' => '',
                    'prop' => [
                        'required' => true,
                    ]
                ], 
                [
                    'label' => 'Padre',
                    'name' => 'padre',
                    'type' => 'select',
                    'prop' => [
                        'data-fitype="bselect"' => true,
                        "data-fisettings='".json_encode([
                            "liveSearch" => true
                        ])."'" => true
                    ],
                    'type-params' => [
                    'table' => array_map(function($row){
                        return [
                            $row['recurso_id'],
                            $row['texto']
                        ];
                    }, $data['resources']),
                    "includeNone" => true
                    ]
                ], 
                [
                    'label' => 'Icono',
                    'name' => 'icono',
                    'type' => 'select',
                    'prop' => [
                        'data-fitype="bselect"' => true,
                        "data-fisettings='".json_encode([
                            "liveSearch" => true
                        ])."'" => true
                    ],
                    'type-params' => [
                        'table' => array_map(function($row) {
                            return [
                                $row['class'],
                                $row['name'],
                                [$row['class'], $row['_tags'][0], str_replace(',', '', $row['tags'])]
                            ];
                        }, $iconos),
                        'data' => [
                            ['icon'],
                            ['group'],
                            ['tokens']
                        ],
                    ]
                ], 
                [
                'label' => 'Path',
                'name' => 'path',
                'type' => 'text'
                ],
                [
                    'label' => 'Grilla',
                    'name' => 'grilla',
                    'type' => 'select',
                    'prop' => [
                        'data-fitype="bselect"' => true,
                        "data-fisettings='".json_encode([
                            "liveSearch" => true
                        ])."'" => true
                    ],
                    'type-params' => [
                    'table' => array_map(function($row){
                        return [
                            $row['grid_id'],
                            $row['name']
                        ];
                    }, $data['grids']),
                    "includeNone" => true
                    ]
                ], 
                [
                    'label' => 'Permisos del recurso',
                    'name' => 'permisos_obj',
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
                ]
            ]
        ];
    }

    function html() {
        ob_start(); ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                        <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
                    </div>
                    <div class="box-body">
                        <table id="arbol" class="table table-bordered table-striped"></table>
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
                    <h4 class="modal-title">RECURSOS</h4>
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
            $form['fields'][0]["value"] = $data->texto;
            $form['fields'][1]["value"] = $data->parent_id;
            $form['fields'][2]["value"] = $data->icono;
            $form['fields'][3]["value"] = $data->funcion;
            $form['fields'][4]["value"] = $data->grid_id;
            $form['fields'][5]["value"] = $data->permisos_obj;
        } else {
            $form['fields'][5]["value"] = [
                [
                    'id' => 1,
                    'key' => 'create',
                    'permiso' => 'Crear'
                ],
                [
                    'id' => 2,
                    'key' => 'read',
                    'permiso' => 'Leer'
                ],
                [
                    'id' => 3,
                    'key' => 'update',
                    'permiso' => 'Editar'
                ],
                [
                    'id' => 4,
                    'key' => 'delete',
                    'permiso' => 'Eliminar'
                ],
            ];
        }
        ob_start(); ?>
        <?=$this->FormItem->buildArray($form)?>
        <?php return ob_get_clean();
    }
}