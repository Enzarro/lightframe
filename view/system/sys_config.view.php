<?php

class sys_config_view {

    function __construct() {
        global $config;
        $this->FormItem = new FormItem();
        $this->formuser = [
            'formid' => 'form-user',
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
                'value' => $config->superuser->username
            ], [
                'label' => 'Contraseña',
                'name' => 'password',
                'type' => 'text',
                'value' => $config->superuser->password
            ]]
        ];
        $this->formdb = [
            'formid' => 'form-db',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Perfiles',
                'name' => 'profiles',
                'type' => 'select',
                'type-params' => [
                    'table' => array_map(function($dbcfg) {
                        return [$dbcfg->profile, $dbcfg->profile];
                    }, $config->database_list)
                ],
                'value' => $config->database->profile
            ], [
                'label' => 'Nombre perfil',
                'name' => 'profile',
                'type' => 'text',
                'value' => $config->database->profile
            ], [
                'label' => 'Host',
                'name' => 'host',
                'type' => 'text',
                'value' => $config->database->host
            ], [
                'label' => 'Puerto',
                'name' => 'port',
                'type' => 'text',
                'value' => $config->database->port
            ], [
                'label' => 'Nombre',
                'name' => 'name',
                'type' => 'text',
                'value' => $config->database->name
            ], [
                'label' => 'Usuario',
                'name' => 'user',
                'type' => 'text',
                'value' => $config->database->user
            ], [
                'label' => 'Contraseña',
                'name' => 'pass',
                'type' => 'text',
                'value' => $config->database->pass
            ], [
                'label' => 'Tipo',
                'name' => 'type',
                'type' => 'select',
                'value' => $config->database->type,
                'type-params' => [
                    'table' => [
                        ['pgsql', 'PostgreSQL'],
                        ['mssql', 'SQL Server'],
                        ['mysql', 'MySQL']
                    ]
                ]
            ]]
        ];
        $this->formlogin = [
            'formid' => 'form-login',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Host',
                'name' => 'loginhost',
                'type' => 'text',
                'value' => $config->login->host
            ], [
                'label' => 'Usuario test',
                'name' => 'testuser',
                'type' => 'text'
            ], [
                'label' => 'Contraseña test',
                'name' => 'testpass',
                'type' => 'text'
            ]]
        ];
    }

    function html($data = null) {
        if (!$data) {
            $data = [
                'error' => false
            ];
        }
        extract($data);
        ob_start(); ?>
        <div class="card">			
            <div class="card-body">		
                <ul class="nav nav-tabs nav-underline nav-justified">
                    <li class="nav-item">
                        <a class="nav-link active" id="user_tab-tab1" data-toggle="tab" href="#tab1" aria-controls="tab1" aria-expanded="true">
                            <i class="fas fa-user-shield"></i> Administrador
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="resources_tab-tab2" data-toggle="tab" href="#tab2" aria-controls="tab2">
                            <i class="fas fa-database"></i> Base Datos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="resources_tab-tab3" data-toggle="tab" href="#tab3" aria-controls="tab3">
                        <i class="fas fa-key"></i> API Login - Token
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="user_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                        <?=$this->FormItem->buildArray($this->formuser)?>
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
                        <?=$this->FormItem->buildArray($this->formdb)?>
                        <div class="row">
                            <div class="form-group col-xs-12 col-md-12 form-group-sm" style="margin-bottom: 0px;">
                                <!-- <div class="col-sm-3"></div> -->
                                <button id="testdb" class="btn btn-default"><span class="fa fa-paper-plane"></span> Probar conexión</button>
                                <button id="savedb" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                                <button id="initdb" class="btn btn-warning"><span class="fa fa-database"></span> Inicializar</button>
                                <button id="deletedb" class="btn btn-danger"><span class="fa fa-times"></span> Eliminar</button>
                                <button id="wipedb" class="btn btn-danger"><span class="fa fa-bomb"></span> Vaciar</button>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="user_tab-tab3" aria-expanded="false" style="padding-top:20px;">
                        <?=$this->FormItem->buildArray($this->formlogin)?>
                        <div class="row">
                            <div class="form-group col-xs-12 col-md-6 form-group-sm" style="margin-bottom: 0px;">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button id="testlogin" class="btn btn-default"><span class="fa fa-paper-plane"></span> Probar login</button>
                                    <button id="savelogin" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>				
        </div>	

        <?php return ob_get_clean();
    }
}