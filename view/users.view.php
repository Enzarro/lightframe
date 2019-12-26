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

        //$this->sys_client = new sys_clients_model();
    }

    function html($data = null) {
        ob_start(); ?>
    
        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>				
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="usuarios"></table>					
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
        $usuario_id = "";
        if (isset($data["personal_data"][0])) {
            $personal_data = (object)$data["personal_data"][0];
        }
        if ($data) {
            //datos generales
            $form['fields'][0]["value"] = $personal_data->nombre;
            $form['fields'][1]["value"] = $personal_data->username;
            $form['fields'][2]["value"] = '';
            $form['fields'][3]["value"] = $personal_data->correo;
            $usuario_id = $personal_data->usuario_id;
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
        <!--
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#user_tab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-address-book-o" aria-hidden="true"></i> Datos del Usuario</a>
            </li>
            <li role="presentation">
                <a href="#resources_tab" aria-controls="recursos" role="tab" data-toggle="tab"><i class="fa fa-cog" aria-hidden="true"></i> Recursos</a>
            </li>
        </ul>
        -->


        <ul class="nav nav-tabs nav-underline nav-justified">
            <li class="nav-item">
                <a class="nav-link active" id="user_tab-tab1" data-toggle="tab" href="#user_tab" aria-controls="user_tab" aria-expanded="true">
                    <i class="fas fa-user"></i> Datos Generales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="resources_tab-tab1" data-toggle="tab" href="#resources_tab" aria-controls="resources_tab">
                    <i class="fas fa-cubes"></i> Recursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="client_tab-tab1" data-toggle="tab" href="#client_tab" aria-controls="client_tab">
                    <i class="far fa-address-card"></i> Clientes
                </a>
            </li>
        </ul>
        
        <div class="tab-content">

            <div class="tab-pane active" id="user_tab" role="tabpanel" aria-labelledby="user_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                <?=$this->FormItem->buildArray($form)?>
            </div>
            <div class="tab-pane" id="resources_tab" role="tabpanel" aria-labelledby="resources_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                <form id="data-form" name="data-form">
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
            <div class="tab-pane" id="client_tab" role="tabpanel" aria-labelledby="client_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                <form id="data-client" name="data-client">

                    <div class='input-group'>
                        <div class="input-group-prepend">
                            <span class='input-group-text'><i class='fa fa-search' aria-hidden='true'></i></span>
                        </div>
                        <input type='text' class='search form-control' placeholder='Buscar cliente (s)'>
                    </div>
                    <span class='counter pull-right'></span>
                    <table class="table table-bordered results">
                        <thead class="table-active">
                            <tr>
                                <th>Nombre Cliente</th>
                                <th>Activo</th>
                            </tr>
                            <tr class='table-warning no-result'>
                                <td colspan='2'><i class="fas fa-exclamation-triangle"></i> Sin resultados</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?=$this->clientList($this->model->clientGet($usuario_id))?>
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
                        <input type="checkbox" class="custom-control-input recursosList" id="<?=$element['id']?>" name="<?=$element['id']?>" <?=$element['activo'] ? "checked" : "" ?>>
                        <label class="custom-control-label" for="<?=$element['id']?>"></label>
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

    function clientList($elements) { 
        //<?=$element['activo'] ? "checked" : "" 
        if (!$elements) return;
        foreach($elements as $element): ?>
            <tr>
                <td><label style="cursor:pointer" for="cl_<?=$element['client_id']?>"><?=strtoupper($element["label"])?></label></td>
                <td style="width: 60px;">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="cl_<?=$element['client_id']?>" name="cl_<?=$element['client_id']?>" value="<?=$element['client_id']?>" <?=$element['activo']?>>
                        <label class="custom-control-label" for="cl_<?=$element['client_id']?>"></label>
                    </div>
                </td>
            </tr>
        <?php 
        endforeach;
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