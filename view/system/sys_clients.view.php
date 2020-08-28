<?php

class sys_clients_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->model = new sys_clients_model();
        $this->FormItem = new FormItem();
    }

    function html($data = null) {
        ob_start(); ?>

        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
                <button id="main-consolidate" class="btn btn-warning"><span class="fa fa-database"></span> Consolidar</button>
                <button id="main-spdbo" class="btn btn-warning"><span class="fa fa-file-code"></span> SP DBO</button>
                <button id="main-spcli" class="btn btn-warning"><span class="fa fa-file-code"></span> SP CLI</button>
                <button id="main-cleansing" class="btn btn-outline-flickr"><span class="fa fa-broom"></span> Limpiar</button>
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="mantenedores"></table>					
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

    function resume($data) {
        extract($data);
        //clients, client_grids, system_grids
        ob_start(); ?>
        <h4>Clientes</h4>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Esquema</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?=$client["label"]?></td>
                    <td><?=$client["db_name"]?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- <pre style="text-align: left;"><?=json_encode($clients, JSON_PRETTY_PRINT)?></pre> -->

        <h4>Tablas de clientes</h4>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tabla</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($client_grids as $grid): ?>
                <tr>
                    <td><?=$grid["name"]?></td>
                    <td><?=$grid["table_name"]?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- <pre style="text-align: left;"><?=json_encode($client_grids, JSON_PRETTY_PRINT)?></pre> -->

        <h4>Tablas comunes (public/dbo)</h4>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tabla</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($system_grids as $grid): ?>
                <tr>
                    <td><?=$grid["name"]?></td>
                    <td><?=$grid["table_name"]?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- <pre style="text-align: left;"><?=json_encode($system_grids, JSON_PRETTY_PRINT)?></pre> -->
        <?php return ob_get_clean();
    }

    function cleansing_resume($data) {
        extract($data);
        //clients, system_existing_tables, client_existing_tables: db_name
        ob_start(); ?>

        <h4>Tablas comunes (public/dbo)</h4>
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
        <?php foreach ($clients as $client): ?>
        <table class="table table-xs table-bordered" style="font-size: x-small;">
            <thead>
                <tr>
                    <th><?=$client['label']?> (<?=$client['db_name']?>)</th>
                </tr>
                <?php if(is_array($client_existing_tables[$client['db_name']]) && count($client_existing_tables[$client['db_name']])): //No creado ?>
                <tr>
                    <td>Existen <b><?=count($client_existing_tables[$client['db_name']])?></b> tablas no definidas. <a href="javascript:;" class="cleansing-toggle">Mostrar listado</a></td>
                </tr>
                <?php endif; ?>
            </thead>

            <?php if(is_array($client_existing_tables[$client['db_name']]) && count($client_existing_tables[$client['db_name']])): //No creado ?>
            <tbody style="display: none;">
            <?php else: ?>
            <tbody>
            <?php endif; ?>
                <?php if($client_existing_tables[$client['db_name']] === 0): //No creado ?>
                <tr>
                    <td>El esquema no ha sido creado</td>
                </tr>
                <?php elseif($client_existing_tables[$client['db_name']] === []): //Sin diferencias ?>
                <tr>
                    <td>No hay tablas sin definición</td>
                </tr>
                <?php else: ?>

                <?php foreach ($client_existing_tables[$client['db_name']] as $table): ?>
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

    function form($data = null) {
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
                    'name' => 'label',
                    'type' => 'text'
                ],
                [
                    'label' => 'Esquema',
                    'name' => 'db_name',
                    'type' => 'text'
                ],
                [
                    'label' => 'Logo',
                    'name' => 'image',
                    'type' => 'text'
                ]
            ]
        ];
        if ($data) {
            // $data->fields = utils::dtBuildDataFromConfig($this->model->getCamposDTConfig(), $data->fields)['data'];
        } else {
            $data = (object)['db_name' => 'client_'];
        }
        ob_start(); ?>
        <?=$this->FormItem->buildArray($form, $data)?>
        <?php return ob_get_clean();
    }
}