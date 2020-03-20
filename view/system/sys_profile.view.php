<?php

class sys_profile_view {

    function __construct($data = null) {
        $this->FormItem = new FormItem();
    }

    function html($data = null) {
        ob_start(); ?>
        <div class="card">			
            <div class="card-body">		
                <ul class="nav nav-tabs nav-underline nav-justified">
                    <li class="nav-item">
                        <a class="nav-link active" id="user_tab-tab1" data-toggle="tab" href="#tab1" aria-controls="tab1" aria-expanded="true">
                            <i class="fa fa-user"></i> Datos Personales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="resources_tab-tab2" data-toggle="tab" href="#tab2" aria-controls="tab2">
                            <i class="fa fa-user-plus"></i> Datos Inicio Sesión
                        </a>
                    </li>
                </ul>
                <?= $this->Form($data) ?>
            </div>				
        </div>	

        <?php return ob_get_clean();
    }

    function Form($data = null) {
        extract($data);
        $this->formUser = [
            'formid' => 'form-user',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Rut',
                'name' => 'rut',
                'type' => 'static',
                'value' => $userData->rut,
                'prop' => [ 'readonly' => true ]
            ],[
                'label' => '',
                'name' => '',
                'type' => 'static'
            ],[
                'label' => 'Nombre',
                'name' => 'nombre',
                'type' => 'text',
                'value' => $userData->nombre,
                'prop' => [ 'required' => true ]
            ],[
                'label' => 'Apellido',
                'name' => 'apellido',
                'type' => 'text',
                'value' => $userData->apellido,
                'prop' => [ 'required' => true ]
            ],[
                'label' => 'Telefono',
                'name' => 'telefono',
                'type' => 'text',
                'value' => $userData->telefono,
                'prop' => [ 'required' => true ]
            ],[
                'label' => 'Correo',
                'name' => 'correo',
                'type' => 'text',
                'value' => $userData->correo,
                'prop' => [ 'required' => true ]
            ]]
        ];
        $this->formLogin = [
            'formid' => 'form-login',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Nombre de usuario',
                'name' => 'username',
                'type' => 'text',
                'value' => $loginData->username,
                'prop' => [ 'required' => true ]
            ], [
                'label' => 'Contraseña',
                'name' => 'password',
                'type' => 'password',
                'prop' => [ 'required' => true ]
            ],[
                'label' => 'Repetir Contraseña',
                'name' => 'rep_password',
                'type' => 'password',
                'prop' => [ 'required' => true ]
            ]]
        ];
        ob_start(); ?>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="user_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                    <?=$this->FormItem->buildArray($this->formUser)?>
                    <div class="row">
                        <div class="form-group col-xs-12 col-md-6 form-group-sm" style="margin-bottom: 0px;">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button id="saveuser" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="user_tab-tab2" aria-expanded="false" style="padding-top:20px;">
                    <?=$this->FormItem->buildArray($this->formLogin)?>
                    <div class="row">
                        <div class="form-group col-xs-12 col-md-12 form-group-sm" style="margin-bottom: 0px;">
                            <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button id="savelogin" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php return ob_get_clean();
    }

}
?>