<?php

class sys_config_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->FormItem = new FormItem();
        $this->formuser = [
            'formid' => 'user',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => [[
                'label' => 'Nombre de usuario',
                'name' => 'username',
                'type' => 'text'
            ], [
                'label' => 'Contraseña',
                'name' => 'password',
                'type' => 'password'
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

                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Parámetros de usuario</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <?=$this->FormItem->buildArray($this->formuser)?>

                        <div class="row">
                            <div class="form-group col-xs-12 col-md-6 form-group-sm" style="margin-bottom: 0px;">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button id="saveuser" class="btn btn-success"><span class="fa fa-save"></span> Guardar</button>
                                </div>
                            </div>
                        </div>

                        <table data_end="<?=$i?>" id="example1" class="table table-bordered table-striped"></table>
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