<?php

class sys_roles_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->model = new sys_roles_model();
        $this->FormItem = new FormItem();
        $this->frame_model = new frame_model();
        $this->frame_view = new frame_view();

        //$this->sys_client = new sys_clients_model();
    }

    function html($data = null) {
        ob_start(); ?>
    
        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <!-- Acciones básicas -->
                <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
                <!-- Exportación/Importación -->
                <button id="main-export" class="btn btn-success" title='descargar información'><span class="fa fa-download"></span></button>	
                <label for="main-import">
                    <span class="btn btn-primary btn-file" title='importar información'><span class="fa fa-upload"></span></span>
                </label>	
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="roles"></table>					
            </div>				
        </div>	

        <div class="modal animated fade" id="modal-default" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
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
        extract($data);
        $formRoles = [
            'formid' => 'form-roles',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [
                [
                    'label' => 'Nombre',
                    'name' => 'nombre',
                    'type' => 'text',
                    'value' => $data?$data->nombre:null,
                    'prop' => [
                        'autocomplete="nombre"' => true,
                        'required' => true
                    ]
                ], [
                    'label' => 'Padre',
                    'name' => 'parent_id',
                    'type' => 'select',
                    'type-params' => [
                        'table' => array_map(function($rol) {
                            return [$rol['id'], $rol['nombre']];
                        }, $roles)
                    ],
                    'value' => $data?$data->parent_id:null,
                    'prop' => [
                        'data-fitype="bselect"' => true,
                        "data-fisettings='".json_encode([
                            "liveSearch" => true
                        ])."'" => true,
                        'autocomplete="nombre"' => true,
                        'required' => false
                    ]
                ], [
                    'label' => 'Orden',
                    'name' => 'orden',
                    'type' => 'text',
                    'value' => $data?$data->orden:null,
                    'prop' => [
                        'data-fitype="anumeric"' => true,
                        "data-fisettings='".json_encode([
                            // 'vMin' => '0',
                            'aSep' => '',
                            'aPad' => false,
                            'lZero' => 'deny',
                            'wEmpty' => 'zero',
                            'mDec' => '0'
                        ])."'" => true,
                        'autocomplete="orden"' => true,
                        'required' => true
                    ]
                ], [
                    'label' => 'Sistema',
                    'name' => 'system',
                    'type' => 'checkbox',
                    'value' => $data?$data->system:null,
                ], [
                    'label' => 'Oculto',
                    'name' => 'oculto',
                    'type' => 'checkbox',
                    'value' => $data?$data->oculto:null,
                ]
            ]
        ];
        if ($data) {
            //check recursos
            $resources = array_map(function($res) use ($data) {
                $accesos = array_filter($data->permission, function($row) use ($res) {
                    return $row["recurso_id"] == $res["id"];
                });
                foreach ($accesos as $acceso) {
                    if(!empty($acceso)) {
                        $res["activo"] = 1;
                        $res["permisos_user_obj"] = json_decode($acceso["permisos_obj"]);
                    }
                }
                return $res;
            }, $resources);   
        }
        $resources = $this->frame_model->buildTree($resources);
        ob_start(); ?>

        <ul class="nav nav-tabs nav-underline nav-justified">
            <li class="nav-item">
                <a class="nav-link active" id="roles_tab-tab1" data-toggle="tab" href="#roles_tab" aria-controls="roles_tab" aria-expanded="true">
                    <i class="fa fa-user"></i> Rol
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="resources_tab-tab1" data-toggle="tab" href="#resources_tab" aria-controls="resources_tab">
                    <i class="fas fa-cubes"></i> Recursos
                </a>
            </li>
        </ul>
        
        <div class="tab-content">

            <div class="tab-pane active" id="roles_tab" role="tabpanel" aria-labelledby="roles_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                <?=$this->FormItem->buildArray($formRoles)?>
            </div>
            <div class="tab-pane" id="resources_tab" role="tabpanel" aria-labelledby="resources_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                <form id="form-permisos" name="form-permisos">
                    <table class="table table-bordered">
                        <thead class="table-active">
                            <tr>
                                <th>Nombre</th>
                                <th>Activo</th>
                                <th>Permisos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?=$this->recursiveResources($resources)?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>           
        <?php return ob_get_clean();
    }

    function recursiveResources(array $elements, $level = 1) {   
        foreach($elements as $element): ?>
        <tr>
            <td>
            <?php if (isset($element['children'])): ?> 
                    <?php echo $this->space_generator($level, $element['children']); echo "<i class='".$element['icono']."'></i> " . $element['texto']; 
                else: echo $this->space_generator($level); 
                ?><i class="<?=$element['icono']?>"></i> <?=$element['texto']?><?php 
                endif; ?>
                <td style="width: 60px;">
                
                <?php if (!isset($element['children'])): ?>

                    <div class="custom-control custom-switch"  style="width:20px; height:20px;">
                        <input type="checkbox" class="custom-control-input recursosList" id="res_<?=$element['id']?>" name="<?=$element['id']?>" value="<?=$element['id']?>" data-parent="<?=$element['parent_id']?>" <?=$element['activo'] ? "checked" : "" ?>>
                        <label class="custom-control-label" for="res_<?=$element['id']?>"></label>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn btn-link btn-sm apply-all" value="<?=$element['id']?>"> <i class="fa fa-check-double"></i></button>
                <?php endif; ?>
                </td>
                <td style="width: 300px; padding: 5px;">
                    <?php if($element["permisos_obj"] && !isset($element['children'])): ?>
                    <select class="form-control selectpicker" id="select_permiso_<?=$element['id']?>" multiple>
                        <?php foreach($element["permisos_obj"] as $res): ?>
                            <option value="<?=$res->key?>" <?=in_array($res->key, ($element["permisos_user_obj"])?$element["permisos_user_obj"]:array()) ? "selected" : ""?>><?=$res->permiso?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </td>
            <?php if (isset($element['children'])): ?>
                <?=$this->recursiveResources($element['children'], $level + 1)?>
            <?php endif; ?>
            </td>         
        </tr>
        <?php endforeach;
    }

    private function space_generator($level, $child = null) {
        $spaces = [];
        if($level == 1) {
            ob_start(); ?>
          
            <?php return ob_get_clean();
        } else {
            for ($i = 1; $i < $level; $i++) { 
                ob_start(); ?>
                <i class="fas fa-ellipsis-h"></i>
                <?php $spaces[] = ob_get_clean();
            }
            return implode("  ", $spaces);
        }     
    }
}