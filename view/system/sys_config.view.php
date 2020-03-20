<?php

class sys_config_view {

    function __construct() {
        global $config;
        utils::load([
            classes.'formitembuilder.php'
        ]);
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
        <div class="row">
            <div class="col-xs-12">
                <!-- Frame Admin -->
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Parámetros administrador frame</h3>
                    </div>

                    <div class="box-body">
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

                    <!-- Database -->
                    <div class="box-header">
                        <h3 class="box-title">Parámetros base de datos</h3>
                    </div>
                    <div class="box-body">
                        <?=$this->FormItem->buildArray($this->formdb)?>

                        <div class="row">
                            <div class="form-group col-xs-12 col-md-6 form-group-sm" style="margin-bottom: 0px;">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button id="savedb" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                                    <button id="testdb" class="btn btn-default"><span class="fa fa-paper-plane"></span> Probar conexión</button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Database -->
                    <div class="box-header">
                        <h3 class="box-title">Parámetros API Login - Token</h3>
                    </div>
                    <div class="box-body">
                        <?=$this->FormItem->buildArray($this->formlogin)?>

                        <div class="row">
                            <div class="form-group col-xs-12 col-md-6 form-group-sm" style="margin-bottom: 0px;">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button id="savelogin" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                                    <button id="testlogin" class="btn btn-default"><span class="fa fa-paper-plane"></span> Probar login</button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
        <?php return ob_get_clean();
    }
}