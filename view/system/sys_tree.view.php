<?php

class sys_tree_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->utils = new utils();
        $this->model = new sys_tree_model();
        
        $this->FormItem = new FormItem();
    }

    function html($data = null) {
        $fdata = $this->model->load_father();

        $fiFather = new FormItem([
            'label' => 'Padre',
            'name' => 'dt-padre',
            'type' => 'select',
            // 'wrap' => false,
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
                        $row['texto'],
                        [$row['icono']]
                    ];
                }, $fdata['resources']),
                'data' => [
                    ['icon']
                ],
                "includeNone" => true
            ]
        ]);

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
                <?=$fiFather->build()?>
                </form>
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="arbol"></table>					
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
        $iconos = $this->model->getIconList();
        $fdata = $this->model->load_father();
        $campos = $this->model->getCampos();

        if (!$data) {
            $campos = array_map(function($row) {
                $row['activo'] = true;
                return $row;
            }, $campos);
            $data = (object)[
                'texto' => null,
                'parent_id' => null,
                'icono' => null,
                'funcion' => null,
                'orden' => null,
                'grid_id' => null,
                'permisos_obj' => $campos
            ];
        }

        $form = [
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
                    'value' => $data->texto,
                    'prop' => [
                        'required' => true,
                    ]
                ], 
                [
                    'label' => 'Padre',
                    'name' => 'padre',
                    'type' => 'select',
                    'value' => $data->parent_id,
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
                                $row['texto'],
                                [$row['icono']]
                            ];
                        }, $fdata['resources']),
                        'data' => [
                            ['icon']
                        ],
                        "includeNone" => true
                    ]
                ], 
                [
                    'label' => 'Icono',
                    'name' => 'icono',
                    'type' => 'select',
                    'value' => $data->icono,
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
                    'type' => 'text',
                    'value' => $data->funcion
                ],
                [
                    'label' => 'Orden',
                    'name' => 'orden',
                    'type' => 'text',
                    'value' => $data->orden
                ],
                [
                    'label' => 'Grilla',
                    'name' => 'grilla',
                    'type' => 'select',
                    'value' => $data->grid_id,
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
                    }, $fdata['grids']),
                    "includeNone" => true
                    ]
                ], 
                [
                    'label' => 'Permisos del recurso',
                    'name' => 'permisos_obj',
                    'type' => 'table',
                    'value' => $data->permisos_obj,
                    'type-params' => [
                        'config' => $this->model->getCamposDTConfig(),
                        'empty' => $this->model->getCamposDTEmptyRow(),
                        'btn-new' => true
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

        
        ob_start(); ?>
        <?=$this->FormItem->buildArray($form)?>
        <?php return ob_get_clean();
    }
}