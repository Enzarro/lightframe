<?php

class users_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->model = new users_model();
        $this->FormItem = new FormItem();
        $this->frame_model = new frame_model();
        $this->frame_view = new frame_view();
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
                        <table id="usuarios" class="table table-bordered table-striped"></table>
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
                    <h4 class="modal-title">Usuarios</h4>
                </div>
                <div class="modal-body"></div>
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

    function form($data = null, $resources = null) {
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
                'type' => 'text'
                ], 
                [
                'label' => 'Usuario',
                'name' => 'user',
                'type' => 'text',
                'prop' => [
                    'required' => true
                ]
                ], 
                [
                'label' => 'Contraseña',
                'name' => 'pass',
                'type' => 'password'
                ], 
                [
                'label' => 'Correo',
                'name' => 'email',
                'type' => 'text'
                ]
            ]
        ];

        $personal_data = (object)$data["personal_data"][0];
        if ($data) {
            //datos generales
            $form['fields'][0]["value"] = $personal_data->nombre;
            $form['fields'][1]["value"] = $personal_data->username;
            $form['fields'][2]["value"] = $personal_data->password;
            $form['fields'][3]["value"] = $personal_data->correo;
            //check recursos
            $resources = array_map(function($res) use ($data) {
                $acceso = array_filter($data["permission"], function($row) use ($res) {
                    return $row["recurso_id"] == $res["id"];
                });
                foreach ($acceso as $acess) {
                    if(!empty($acceso)) {
                        $res["activo"] = 1;
                        $res["permisos_user_obj"] = json_decode($acess["permisos_obj"]);
                    }
                }
                return $res;
            }, $resources);   
        }
        $resources = $this->frame_model->buildTree($resources);
        ob_start(); ?>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#user_tab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-address-book-o" aria-hidden="true"></i> Datos del Usuario</a>
            </li>
            <li role="presentation">
                <a href="#resources_tab" aria-controls="recursos" role="tab" data-toggle="tab"><i class="fa fa-cog" aria-hidden="true"></i> Recursos</a>
            </li>
        </ul>
        
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="user_tab" style="padding-top:10px;">
                <?=$this->FormItem->buildArray($form)?>
            </div>
            <div role="tabpanel" class="tab-pane" id="resources_tab" style="padding-top:10px;">
                <form id="data-form" name="data-form">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="active">
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
                    <?php echo $this->space_generator($level, $element['children']); echo $element['texto']; 
                else: echo $this->space_generator($level); 
                ?><b><?=$element['texto']?></b><?php 
                endif; ?>
                <td style="width: 60px;">
                <?php if (!isset($element['children'])): ?>
                    <div class="icheck-primary d-inline">
                        <input type="checkbox" id="<?=$element['id']?>" name="<?=$element['id']?>" class="minimal" <?=$element['activo'] ? "checked" : "" ?>>
                    </div>
                <?php endif; ?>
                </td>
                <td style="width: 300px;">
                    <?php if($element["permisos_obj"]): ?>
                    <select class="form-control selectpicker" id="select_permiso_<?=$element['id']?>" multiple>
                        <?php foreach($element["permisos_obj"] as $res): ?>
                            <option value="<?=$res->key?>" <?=in_array($res->key, $element["permisos_user_obj"]) ? "selected" : ""?>><?=$res->permiso?></option>
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
                <i class="material-icons">subdirectory_arrow_right</i>
                <?php $spaces[] = ob_get_clean();
            }
            return implode("  ", $spaces);
        }     
    }
}