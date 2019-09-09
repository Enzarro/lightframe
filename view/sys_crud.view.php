<?php

class sys_crud_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->FormItem = new FormItem();
        $this->form = [
            'formid' => 'crud',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Nombre',
                'name' => 'name',
                'type' => 'text'
            ], [
                'label' => 'Tabla',
                'name' => 'table',
                'type' => 'password'
            ],
            [
                'label' => 'Campos',
                'name' => 'fields',
                'type' => 'table'
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
                        <button id="new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                    </div>
                    <div class="box-body">
                        <table id="mantenedores" class="table table-bordered table-striped"></table>
                    </div>
                </div>
            </div>
        </div>
    
        <?php return ob_get_clean();
    }

    function form() {
        ob_start(); ?>

        <?php return ob_get_clean();
    }
}