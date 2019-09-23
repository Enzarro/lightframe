<?php

class sys_tree_view {

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

    function html() {
        ob_start(); ?>
    
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                    <!-- <h3 class="box-title"></h3> -->
                        <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
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
                    <h4 class="modal-title">CRUD</h4>
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
}