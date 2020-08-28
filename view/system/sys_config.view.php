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
                    }, (isset($config->database_list)?$config->database_list:[]))
                ],
                'value' => (isset($config->database->profile)?$config->database->profile:null)
            ], [
                'label' => 'Nombre perfil',
                'name' => 'profile',
                'type' => 'text',
                'value' => (isset($config->database->profile)?$config->database->profile:null)
            ], [
                'label' => 'Host',
                'name' => 'host',
                'type' => 'text',
                'value' => (isset($config->database->host)?$config->database->host:null)
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

    function wipe_resume($data) {
        extract($data);
        //clients, system_existing_tables, client_existing_tables: db_name
        ob_start(); ?>

        <h4>Tablas globales vaciadas (public/dbo)</h4>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th>DBO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($system_clean_tables as $table): ?>
                <tr>
                    <td><?=$table?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Tablas globales eliminadas (public/dbo)</h4>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th>DBO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($system_existing_tables as $table): ?>
                <tr>
                    <td><?=$table?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Tablas de clientes</h4>
        <?php foreach (array_keys($client_existing_tables) as $client): ?>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th><?=$client?></th>
                </tr>
                <?php if(is_array($client_existing_tables[$client]) && count($client_existing_tables[$client])): //No creado ?>
                <tr>
                    <td>Se dropearon <b><?=count($client_existing_tables[$client])?></b> tablas. <a href="javascript:;" class="wipe-toggle">Mostrar listado</a></td>
                </tr>
                <?php endif; ?>
            </thead>

            <?php if(is_array($client_existing_tables[$client]) && count($client_existing_tables[$client])): //No creado ?>
            <tbody style="display: none;">
            <?php else: ?>
            <tbody>
            <?php endif; ?>
                <?php if($client_existing_tables[$client] === []): //Sin tablas ?>
                <tr>
                    <td>No habían tablas en el esquema</td>
                </tr>
                <?php else: ?>

                <?php foreach ($client_existing_tables[$client] as $table): ?>
                <tr>
                    <td><?=$table?></td>
                </tr>
                <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
        <?php endforeach; ?>
        
        <?php echo ob_get_clean();
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